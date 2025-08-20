<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\Admins\SearchForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * Admins Controller
 */
class AdminsController extends AdminAppController
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
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Admins',
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
        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->fetchTable('Admins');

        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('admins.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('admins.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('admins.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $admins = $this->Pagination->paginate($this->fetchTable('Admins'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('admins.list.search', $searchData);

        // ビュー変数
        $this->set([
            'admins' => $admins,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'canAddAdmin' => $adminsTable->canAddAdmin(),
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->fetchTable('Admins');

        if (!$adminsTable->canAddAdmin()) {
            throw new NotFoundException();
        }

        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('Admins', 'admin_mails');
            $admin = $adminsTable->newEntity($this->getRequest()->getData(), [
                'associated' => [
                    'AdminMails' => [],
                ],
            ]);
            $saveOptions = [
                'accessibleDirty' => true,
                'saveOperation' => $this->getRequest()->getAttribute('params'),
            ];
            if ($adminsTable->save($admin, $saveOptions)) {
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'adminsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Admins',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'adminsErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            $admin = $adminsTable->newEntity($adminsTable->getDefaultFieldValues(), [
                'validate' => false,
                'associated' => [
                    'AdminMails' => [
                        'validate' => false,
                    ],
                ],
            ]);
        }

        $this->set([
            'admin' => $admin,
            'valueOptions' => $adminsTable->getFieldValueOptions(),
        ]);
    }

    /**
     * Edit method
     *
     * @param int $id 管理者ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->fetchTable('Admins');

        $admin = $adminsTable->get($id, [
            'finder' => 'edit',
        ]);
        if (!$admin->canEdit()) {
            throw new NotFoundException();
        }

        if ($this->getRequest()->is('post')) {
            // 入力値取得
            $this->RequestFilter->setRebalanceInputs('Admins', 'admin_mails');
            $adminsTable->patchEntity($admin, (array)$this->getRequest()->getData(), [
                'associated' => [
                    'AdminMails' => [],
                ],
            ]);
            $saveOptions = [
                'accessibleDirty' => true,
                'saveOperation' => $this->getRequest()->getAttribute('params'),
            ];
            if ($adminsTable->save($admin, $saveOptions)) {
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'adminsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Admins',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'adminsErrors',
                    'element' => 'error',
                ]);
            }
        }

        $this->set([
            'admin' => $admin,
            'valueOptions' => $adminsTable->getFieldValueOptions(),
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

        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->fetchTable('Admins');

        $admin = $adminsTable->get($id, [
            'finder' => 'delete',
        ]);
        if (!$admin->canDelete()) {
            throw new NotFoundException();
        }

        $adminsTable->deleteOrFail($admin, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ]);
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'adminsFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Admins',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }
}
