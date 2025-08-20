<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\BounceMails\SearchForm;
use App\Locale\Message;
use App\Model\Entity\BounceMail;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;

/**
 * BounceMails Controller
 */
class BounceMailsController extends AdminAppController
{
    public const BOUNCE_MAIL_HISTORIES_PAGE_LIMIT = 10;

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'delete',
            'sendOff',
            'sendOn',
            'togetherDelete',
            'download',
        ]);

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'BounceMails',
            'action' => 'list',
        ], 301);
    }

    /**
     * List method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function list()
    {
        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('bounceMails.list.search');
            $this->getRequest()->getSession()->delete('bounceMails.list.checked');
        }

        if ($this->SearchInput->checkSearchButtonClick()) {
            $this->getRequest()->getSession()->delete('bounceMails.list.checked');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('bounceMails.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('bounceMails.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $bounceMails = $this->Pagination->paginate($this->fetchTable('BounceMails'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('bounceMails.list.search', $searchData);

        // ビュー変数
        $this->set([
            'bounceMails' => $bounceMails,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'check' => $this->SearchInput->getListCheckInfo(
                $searchForm->getFieldValueOptions(),
                $this->getRequest()->getSession()->read('bounceMails.list.checked')
            ),
        ]);
    }

    /**
     * View method
     *
     * @param int $id 不達メールID
     * @return \Cake\Http\Response|null|void
     */
    public function view($id = null)
    {
        $bounceMail = $this->fetchTable('BounceMails')->get($id, [
            'finder' => 'detail',
        ]);

        $searchInputs = $this->SearchInput->getCondition(
            ['bounce_mail_id' => $bounceMail->get('id')],
            null
        );

        // データ取得
        $bounceMailHistories = $this->Pagination->paginate($this->fetchTable('BounceMailHistories'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchInputs,
                ],
            ],
            'maxLimit' => static::BOUNCE_MAIL_HISTORIES_PAGE_LIMIT,
        ]);

        $this->set([
            'bounceMail' => $bounceMail,
            'bounceMailHistories' => $bounceMailHistories,
        ]);
    }

    /**
     * Download method
     *
     * @param null $id 不達履歴ID
     * @return \Cake\Http\Response
     */
    public function download($id = null)
    {
        $this->getRequest()->allowMethod('post');

        $bounceMailHistory = $this->fetchTable('BounceMailHistories')->get($id, [
            'finder' => 'download',
        ]);

        $fileName = Configure::readOrFail('Setting.download.bounceMailHistory.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);

        $response = $this->FileDownload->setDownloadHeader($this->getResponse(), $fileName);

        $response = $response->withStringBody($bounceMailHistory->get('contents'));
        $this->setResponse($response);

        return $this->getResponse();
    }

    /**
     * CheckDelete method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function togetherDelete()
    {
        /** @var \App\Model\Table\BounceMailsTable $bounceMailsTable */
        $bounceMailsTable = $this->fetchTable('BounceMails');

        $this->getRequest()->allowMethod('post');

        $checked = $this->getRequest()->getSession()->read('bounceMails.list.checked');
        if (empty($checked) || !is_array($checked)) {
            throw new BadRequestException(Message::ERROR_ONE_OR_MORE_SELECT);
        }

        $checkList = $bounceMailsTable->getTogetherBounceMails(
            $checked,
            $this->getRequest()->getSession()->read('bounceMails.list.search')
        );

        if ($checkList->count() < 1) {
            throw new BadRequestException(Message::ERROR_ONE_OR_MORE_SELECT);
        }

        if (
            $bounceMailsTable->deleteData($checkList, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
            ])
        ) {
            // 完了メッセージ
            $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
                'key' => 'bounceMailsFinish',
                'element' => 'success',
            ]);

            $this->getRequest()->getSession()->delete('bounceMails.list.checked');
        } else {
            $this->Flash->set((string)__(Message::INVALID_INPUT), [
                'key' => 'bounceMailsErrors',
                'element' => 'error',
            ]);
        }

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'BounceMails',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }

    /**
     * sendOn method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function sendOn()
    {
        $this->getRequest()->allowMethod('post');

        $this->updateSendExclude(BounceMail::SEND_EXCLUDE_FLG_OFF);
    }

    /**
     * sendOff method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function sendOff()
    {
        $this->getRequest()->allowMethod('post');

        $this->updateSendExclude(BounceMail::SEND_EXCLUDE_FLG_ON);
    }

    /**
     * @param int $sendExcludeFlg 送信フラグ
     * @return \Cake\Http\Response|null
     */
    private function updateSendExclude(int $sendExcludeFlg)
    {
        /** @var \App\Model\Table\BounceMailsTable $bounceMailsTable */
        $bounceMailsTable = $this->fetchTable('BounceMails');

        $checked = $this->getRequest()->getSession()->read('bounceMails.list.checked');
        if (empty($checked) || !is_array($checked)) {
            throw new BadRequestException(Message::ERROR_ONE_OR_MORE_SELECT);
        }

        $checkList = $bounceMailsTable->getTogetherBounceMails(
            $checked,
            $this->getRequest()->getSession()->read('bounceMails.list.search')
        );

        if ($checkList->count() < 1) {
            throw new BadRequestException(Message::ERROR_ONE_OR_MORE_SELECT);
        }

        if (
            $bounceMailsTable->updateSendExcludeMany($checkList, $sendExcludeFlg, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
            ])
        ) {
            // 完了メッセージ
            $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                'key' => 'bounceMailsFinish',
                'element' => 'success',
            ]);

            $this->getRequest()->getSession()->delete('bounceMails.list.checked');
        } else {
            $this->Flash->set((string)__(Message::INVALID_INPUT), [
                'key' => 'bounceMailsErrors',
                'element' => 'error',
            ]);
        }

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'BounceMails',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }

    /**
     * Delete method
     *
     * @param int $id 管理者ID
     * @return \Cake\Http\Response|null|void
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod('post');

        // エンティティー生成
        $bounceMail = $this->fetchTable('BounceMails')->get($id, [
            'finder' => 'delete',
        ]);

        $this->fetchTable('BounceMails')->deleteOrFail($bounceMail, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ]);

        // 完了メッセージ
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'bounceMailsFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'BounceMails',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }
}
