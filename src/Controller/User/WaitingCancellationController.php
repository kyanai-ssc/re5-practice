<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Traits\WaitingCancellationsTrait;
use App\Controller\UserAppController;
use App\Form\User\WaitingCancellations\SearchForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;

/**
 * WaitingCancellation Controller
 */
class WaitingCancellationController extends UserAppController
{
    use WaitingCancellationsTrait;

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->isLoginRequire([
            'add',
            'addFinish',
            'token',
            'tokenFinish',
            'listToken',
            'list',
            'release',
        ]);

        $this->FormProtection->setConfig('unlockedActions', [
            'release',
        ]);

        return $response;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $waitingCancellationForm = $this->addAction();
        $waitingCancellation = $waitingCancellationForm->getWaitingCancellationEntity();
        if (isset($waitingCancellation) && $waitingCancellation->has('id')) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'WaitingCancellation',
                'action' => 'add-finish',
                'id' => $waitingCancellation->get('id'),
            ]);
        }
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function addFinish()
    {
    }

    /**
     * token method キャンセル待ちトークン
     *
     * @return \Cake\Http\Response|null|void
     */
    public function token()
    {
        if ($this->commonData()->existsUserLoginData()) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'WaitingCancellation',
                'action' => 'list',
            ]);
        }

        if ($this->getRequest()->is('post')) {
            $waiting = $this->fetchTable('WaitingCancellationConfTokens')->newEntity($this->getRequest()->getData());

            if ($this->fetchTable('WaitingCancellationConfTokens')->save($waiting)) {
                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'WaitingCancellation',
                    'action' => 'tokenFinish',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'waitingCancellationErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            $waiting = $this->fetchTable('WaitingCancellationConfTokens')->newEntity([], ['validate' => false]);
            $this->getRequest()->getSession()->delete('waitingCancellations.list');
        }

        $this->set([
            'waiting' => $waiting,
        ]);
    }

    /**
     * token method キャンセル待ちトークン 発行完了
     *
     * @return \Cake\Http\Response|null|void
     */
    public function tokenFinish()
    {
        if ($this->commonData()->existsUserLoginData()) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'WaitingCancellation',
                'action' => 'list',
            ]);
        }
    }

    /**
     * List token method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function listToken()
    {
        if (!$this->commonData()->existsUserLoginData()) {
            $waitingCancellationsToken = null;
            $query = (array)$this->getRequest()->getQuery();
            if (isset($query['token']) && is_scalar($query['token'])) {
                $waitingCancellationsToken = $this->fetchTable('WaitingCancellationConfTokens')->find('token', [
                    'token' => $query['token'],
                ])->first();
            }

            if (!$waitingCancellationsToken instanceof EntityInterface) {
                throw new BadRequestException(Message::ERROR_INVALID_URL);
            }
            $this->getRequest()->getSession()->write(
                'waitingCancellations.list.token',
                $waitingCancellationsToken->get('token')
            );
        }

        return $this->redirect([
            'prefix' => 'User',
            'controller' => 'WaitingCancellation',
            'action' => 'list',
        ]);
    }

    /**
     * Detailmethod 会員詳細
     *
     * @return \Cake\Http\Response|null|void
     */
    public function list()
    {
        $mailAddress = '';
        if (!$this->commonData()->existsUserLoginData()) {
            $token = '';
            if ($this->getRequest()->getSession()->read('waitingCancellations.list.token') !== null) {
                $token = $this->getRequest()->getSession()->read('waitingCancellations.list.token');
            }

            $waitingCancellationsToken = $this->fetchTable('WaitingCancellationConfTokens')->find('token', [
                'token' => $token,
            ])->first();
            if (!$waitingCancellationsToken instanceof EntityInterface) {
                throw new BadRequestException(Message::ERROR_INVALID_URL);
            }
            $mailAddress = $waitingCancellationsToken->get('mail');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('waitingCancellations.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('waitingCancellations.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $identity = $this->Authentication->getIdentity();
        $userId = null;
        if (isset($identity)) {
            /** @var \Authentication\Identity $identity */
            $userId = $identity->offsetGet('id');
        }
        $waitingCancellations = $this->Pagination->paginate($this->fetchTable('WaitingCancellations'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                    'userId' => $userId,
                    'mail' => $mailAddress,
                ],
            ],
            'limit' => $searchForm::PAGE_LIMIT,
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('waitingCancellations.list.search', $searchData);

        // ビュー変数
        $this->enableLoginRedirectBack();
        $this->set([
            'waitingCancellations' => $waitingCancellations,
            'searchForm' => $searchForm,
        ]);
    }

    /**
     * @param int $id id
     * @return \Cake\Http\Response|null|void
     */
    public function release($id)
    {
        $mailAddress = '';
        if (!$this->commonData()->existsUserLoginData()) {
            $token = '';
            if ($this->getRequest()->getSession()->read('waitingCancellations.list.token') !== null) {
                $token = $this->getRequest()->getSession()->read('waitingCancellations.list.token');
            }

            $waitingCancellationsToken = $this->fetchTable('WaitingCancellationConfTokens')->find('token', [
                'token' => $token,
            ])->first();
            if (!$waitingCancellationsToken instanceof EntityInterface) {
                throw new BadRequestException(Message::ERROR_INVALID_URL);
            }
            $mailAddress = $waitingCancellationsToken->get('mail');
        }

        $identity = $this->Authentication->getIdentity();
        $userId = null;
        if (isset($identity)) {
            /** @var \Authentication\Identity $identity */
            $userId = $identity->offsetGet('id');
        }

        $release = $this->fetchTable('WaitingCancellations')->get($id, [
            'finder' => 'release',
            'id' => $id,
            'userId' => $userId,
            'mail' => $mailAddress,
        ]);

        $this->fetchTable('WaitingCancellations')->deleteOrFail($release);

        $this->Flash->set((string)__(Message::WAITING_CANCELLATION_RELEASE_SUCCESS), [
            'key' => 'waitingCancellationFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'User',
            'controller' => 'WaitingCancellation',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }
}
