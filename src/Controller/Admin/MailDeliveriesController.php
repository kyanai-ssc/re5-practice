<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Command\Traits\CommandTrait;
use App\Controller\AdminAppController;
use App\Form\Admin\MailDeliveries\SearchForm;
use App\Form\Admin\MailDeliveries\UserSearchForm;
use App\Locale\Message;
use App\Mailer\DefaultMailer;
use App\Model\Entity\MailDelivery;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;

/**
 * MailDeliveries Controller
 */
class MailDeliveriesController extends AdminAppController
{
    use CommandTrait;

    public const TOKEN_MAIL_DELIVERY_ADD = 'token_mail_delivery_add';

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'sendUserDownload',
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
            'controller' => 'MailDeliveries',
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
            $this->getRequest()->getSession()->delete('mailDeliveries.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('mailDeliveries.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('mailDeliveries.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $mailDeliveries = $this->Pagination->paginate($this->fetchTable('MailDeliveries'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                    'mailDeliverySchema' => $searchForm->getMailDeliverySchema(),
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('mailDeliveries.list.search', $searchData);

        // ビュー変数
        $this->set([
            'mailDeliveries' => $mailDeliveries,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * ReplaceVars method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function replaceVars()
    {
        $mail = new DefaultMailer();

        // ビュー変数
        $this->set([
            'replaceVars' => $mail->getMailDeliveryReplaceTokens(),
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        /** @var \App\Model\Table\MailDeliveriesTable $mailDeliveriesTable */
        $mailDeliveriesTable = $this->fetchTable('MailDeliveries');

        if ($this->getRequest()->getQuery('input') === 'back') {
            if (!$this->getRequest()->getSession()->check('mailDeliveries.add')) {
                throw new BadRequestException();
            }
        }

        if (!empty($this->getRequest()->getSession()->read('users.list.search'))) {
            $checked = $this->getRequest()->getSession()->read('users.list.search');
        } else {
            $checked = $this->getRequest()->getSession()->read('mailDeliveries.add');
        }

        $users = $mailDeliveriesTable->getUserList($checked);
        if ($users === false) {
            throw new BadRequestException(Message::ERROR_ONE_OR_MORE);
        }

        if (!$mailDeliveriesTable->canSendLimit($users)) {
            throw new BadRequestException(Message::PLAN_RESTRICTION_OVER);
        }

        if ($this->getRequest()->is('post')) {
            $mailDeliveryInputs = (array)$this->getRequest()->getData();
            $mailDelivery = $mailDeliveriesTable->newEntity(
                $mailDeliveryInputs,
                ['checkRules' => true]
            );

            if (
                !$mailDelivery->getErrors()
                && $this->TokenValidation->validate(static::TOKEN_MAIL_DELIVERY_ADD)
            ) {
                $this->getRequest()->getSession()->write(
                    'mailDeliveries.add',
                    json_decode($mailDeliveryInputs['checked'], true)
                );
                $this->getRequest()->getSession()->write('mailDeliveries.add.inputs', $mailDeliveryInputs);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'MailDeliveries',
                    'action' => 'addConf',
                ]);
            }
        } elseif ($this->getRequest()->getQuery('input') === 'back') {
            // エンティティ生成
            $mailDelivery = $mailDeliveriesTable->newEntity(
                $this->getRequest()->getSession()->read('mailDeliveries.add.inputs'),
                ['validate' => false]
            );
        } else {
            $this->getRequest()->getSession()->delete('mailDeliveries.add');

            // エンティティ生成
            $mailDelivery = $mailDeliveriesTable->newEntity($mailDeliveriesTable->getDefaultFieldValues(), [
                'validate' => false,
            ]);
        }

        $this->TokenValidation->generate(static::TOKEN_MAIL_DELIVERY_ADD);

        $this->set([
            'mailDelivery' => $mailDelivery,
            'valueOptions' => $mailDeliveriesTable->getFieldValueOptions(),
            'sendUserCount' => $users,
            'checked' => json_encode($checked),
        ]);
    }

    /**
     * Add method
     *
     * @param int|null $id id
     * @return \Cake\Http\Response|null
     */
    public function sendUserDownload($id = null)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');
        /** @var \App\Model\Table\MailDeliveriesTable $mailDeliveriesTable */
        $mailDeliveriesTable = $this->fetchTable('MailDeliveries');

        $options = [
            'mailDelivery' => true,
        ];
        $finder = 'searchList';

        if ($id !== null) {
            $options['mailDeliveryId'] = $id;
            $finder = 'deliveryUserList';
            $searchInputs = [];
        } else {
            if (!empty($this->getRequest()->getSession()->read('users.list.search'))) {
                $searchInputs = $this->getRequest()->getSession()->read('users.list.search');
            } else {
                $searchInputs = $this->getRequest()->getSession()->read('mailDeliveries.add');
            }
            $users = $mailDeliveriesTable->getUserList($searchInputs);
            if ($users === false) {
                throw new BadRequestException();
            }
        }

        $searchInputs = $mailDeliveriesTable->createDeliverySearchInputs($searchInputs);

        // CSVファイル出力
        $fileName = Configure::readOrFail('Setting.csv.download.user.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $fileType = Configure::readOrFail('Setting.csv.download.user.type');
        $callback = $usersTable->createCsv(Hash::get((array)$searchInputs, 'inputs', []), $finder, $options);

        return $this->FileDownload->setStreamDownloadResponse($fileName, $fileType, $callback);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function addConf()
    {
        /** @var \App\Model\Table\MailDeliveriesTable $mailDeliveriesTable */
        $mailDeliveriesTable = $this->fetchTable('MailDeliveries');

        $users = $mailDeliveriesTable->getUserList($this->getRequest()->getSession()->read('mailDeliveries.add'));
        if ($users === false) {
            throw new BadRequestException();
        }

        $mailDeliveryInputs = $this->getRequest()->getSession()->read('mailDeliveries.add.inputs');
        if (empty($mailDeliveryInputs)) {
            throw new BadRequestException();
        }

        if (!$mailDeliveriesTable->canSendLimit($users)) {
            throw new BadRequestException(Message::PLAN_RESTRICTION_OVER);
        }

        if ($this->getRequest()->is('post')) {
            // ワンタイムトークンチェック

            $mailDelivery = $mailDeliveriesTable->newEntity(
                $mailDeliveryInputs
            );

            if (
                $this->TokenValidation->validate(static::TOKEN_MAIL_DELIVERY_ADD)
                && $mailDeliveriesTable->save($mailDelivery, [
                    'saveOperation' => $this->getRequest()->getAttribute('params'),
                    'deliveryTarget' => $this->getRequest()->getSession()->read('mailDeliveries.add'),
                    'saveNew' => true,
                ])
            ) {
                // 即時配信の場合はバッチを実行する
                if ($mailDelivery->get('send_type') === MailDelivery::SEND_TYPE_IMMEDIATELY) {
                    $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
                        'mail',
                        $mailDelivery->get('id'),
                        Configure::readOrFail('Setting.batch.client'),
                        '--quiet',
                    ]);
                }

                $this->getRequest()->getSession()->delete('mailDeliveries.add');
                $this->getRequest()->getSession()->delete('users.list.search.checked');

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'MailDeliveries',
                    'action' => 'addFinish',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'mailDeliveriesErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            // エンティティ生成
            $mailDelivery = $mailDeliveriesTable->newEntity($mailDeliveryInputs, ['validation' => true]);
        }

        $this->TokenValidation->generate(static::TOKEN_MAIL_DELIVERY_ADD);

        // ビュー変数
        $this->set([
            'mailDelivery' => $mailDelivery,
            'valueOptions' => $mailDeliveriesTable->getFieldValueOptions(),
            'sendUserCount' => $users,
        ]);
    }

    /**
     * Send contents preview method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null
     */
    public function preview($id = null)
    {
        $this->autoRender = false;

        if ($id != null) {
            $mailDelivery = $this->fetchTable('MailDeliveries')->get($id);
            $mailDeliveryInputs['content_type'] = $mailDelivery->get('content_type');
            $mailDeliveryInputs['contents'] = $mailDelivery->get('contents');
        } else {
            $mailDeliveryInputs = (array)$this->getRequest()->getSession()->read('mailDeliveries.add.inputs');
            if (empty($mailDeliveryInputs)) {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }
        }

        if ((int)$mailDeliveryInputs['content_type'] === DefaultMailer::MAIL_FORMAT_CONTENTS_HTML) {
            $body = $mailDeliveryInputs['contents'];
        } else {
            $body = '<pre>' . h($mailDeliveryInputs['contents']) . '</pre>';
        }

        return $this->getResponse()->withStringBody($body);
    }

    /**
     * Add exec method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function addFinish()
    {
        $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
            'key' => 'mailDeliveriesFinish',
            'element' => 'success',
        ]);
    }

    /**
     * View method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function view($id = null)
    {
        $mailDelivery = $this->fetchTable('MailDeliveries')->get($id, [
            'finder' => 'detail',
        ]);

        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('mailDeliveries.viewUserList.search');
        }

        $searchForm = new UserSearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('mailDeliveries.viewUserList.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('mailDeliveries.viewUserList.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $users = $this->Pagination->paginate($this->fetchTable('Users'), [
            'finder' => [
                'deliveryUserList' => [
                    'inputs' => $searchData,
                    'mailDeliveryId' => $id,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('mailDeliveries.viewUserList.search', $searchData);

        // ビュー変数
        $this->set([
            'mailDelivery' => $mailDelivery,
            'searchForm' => $searchForm,
            'users' => $users,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * Cancel method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null
     */
    public function cancel($id)
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\MailDeliveriesTable $mailDeliveriesTable */
        $mailDeliveriesTable = $this->fetchTable('MailDeliveries');

        $mailDelivery = $mailDeliveriesTable->get($id, [
            'fields' => ['id', 'send_status'],
        ]);

        if (!$mailDelivery->canCancel()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if (
            $mailDeliveriesTable->save($mailDelivery, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                'validate' => false,
                'checkRules' => false,
                'updateStatus' => MailDelivery::SEND_STATUS_CANCEL,
            ])
        ) {
            $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                'key' => 'mailDeliveriesFinish',
                'element' => 'success',
            ]);
        } else {
            $this->Flash->set((string)__(Message::INVALID_INPUT), [
                'key' => 'mailDeliveriesErrors',
                'element' => 'error',
            ]);
        }

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'MailDeliveries',
            'action' => 'view',
            'id' => $mailDelivery->get('id'),
        ]);
    }
}
