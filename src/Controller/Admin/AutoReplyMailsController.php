<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\AutoReplyMails\SearchForm;
use App\Locale\Message;
use App\Mailer\DefaultMailer;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

/**
 * AutoReplyMails Controller
 */
class AutoReplyMailsController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'delete',
        ]);

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'AutoReplyMails',
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
            $this->getRequest()->getSession()->delete('autoReplyMails.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('autoReplyMails.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('autoReplyMails.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $autoReplyMails = $this->Pagination->paginate($this->fetchTable('AutoReplyMails'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('autoReplyMails.list.search', $searchData);

        // ビュー変数
        $this->set([
            'autoReplyMails' => $autoReplyMails,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * Copy method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function copy($id = null)
    {
        $this->add($id);

        $this->render('add');
    }

    /**
     * Add method
     *
     * @param int $id ID コピー用
     * @return \Cake\Http\Response|null|void
     */
    public function add($id = null)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->fetchTable('AutoReplyMails');

        if ($this->getRequest()->is('post')) {
            $autoReplyMail = $autoReplyMailsTable->newEntity($this->getRequest()->getData());

            if (
                $autoReplyMailsTable->save($autoReplyMail, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'autoReplyMailsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'AutoReplyMails',
                    'action' => 'list',
                    '?' => Configure::readOrFail('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'autoReplyMailsErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            if (!is_null($id)) {
                // エンティティー生成
                $autoReplyMail = $autoReplyMailsTable->get($id, [
                    'finder' => 'edit',
                ]);
                // コピー可否チェック
                if (!$labelsTable->isAdminUsableLabel($autoReplyMail->label_id)) {
                    throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
                }
                $autoReplyMail->setNew(true);
                $autoReplyMail->unset('id');
            } else {
                // エンティティ生成
                $autoReplyMail = $autoReplyMailsTable->newEntity([], [
                    'validate' => false,
                    'associated' => [
                        'AutoReplyMailStatuses' => [],
                    ],
                ]);
            }
        }
        // ビュー変数
        $this->set([
            'autoReplyMail' => $autoReplyMail,
            'valueOptions' => $autoReplyMailsTable->getFieldValueOptions(),
        ]);
    }

    /**
     * Edit method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->fetchTable('AutoReplyMails');

        // エンティティー生成
        $autoReplyMail = $autoReplyMailsTable->get($id, [
            'finder' => 'edit',
        ]);

        // 編集可否チェック
        if (!$labelsTable->isAdminUsableLabel($autoReplyMail->label_id)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            $autoReplyMail = $autoReplyMailsTable->patchEntity($autoReplyMail, (array)$this->getRequest()->getData(), [
                'associated' => [
                    'AutoReplyMailStatuses' => [],
                ],
            ]);

            if (
                $autoReplyMailsTable->save($autoReplyMail, [
                'associated' => ['AutoReplyMailStatuses'],
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'autoReplyMailsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'AutoReplyMails',
                    'action' => 'list',
                    '?' => Configure::readOrFail('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'autoReplyMailsErrors',
                    'element' => 'error',
                ]);
            }
        }

        // ビュー変数
        $this->set([
            'autoReplyMail' => $autoReplyMail,
            'valueOptions' => $autoReplyMailsTable->getFieldValueOptions(),
        ]);
    }

    /**
     * Delete method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->fetchTable('AutoReplyMails');

        // エンティティー生成
        $autoReplyMail = $autoReplyMailsTable->get($id, [
            'finder' => 'edit',
            'contain' => ['AutoReplyMailStatuses'],
        ]);

        if (!$labelsTable->isAdminUsableLabel($autoReplyMail->get('label_id')) || !$autoReplyMail->canDelete()) {
            throw new NotFoundException();
        }

        $autoReplyMailsTable->deleteOrFail($autoReplyMail, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ]);

        // 完了メッセージ
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'autoReplyMailsFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'AutoReplyMails',
            'action' => 'list',
            '?' => Configure::readOrFail('Setting.searchInput.searchQuery'),
        ]);
    }

    /**
     * ReplaceVars method
     *
     * @param int $type 自動返信メールタイプ
     * @return \Cake\Http\Response|null|void
     */
    public function replaceVars($type = null)
    {
        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->fetchTable('AutoReplyMails');

        if (is_null($type)) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $mail = new DefaultMailer();

        // ビュー変数
        $this->set([
            'replaceVars' => $mail->getAutoMailReplaceTokens((int)$type),
            'valueOptions' => $autoReplyMailsTable->getFieldValueOptions(),
        ]);
    }
}
