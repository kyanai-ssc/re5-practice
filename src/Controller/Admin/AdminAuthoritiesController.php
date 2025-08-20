<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\AdminAuthorities\SearchForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * AdminAuthorities Controller
 */
class AdminAuthoritiesController extends AdminAppController
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
            'controller' => 'AdminAuthorities',
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
            $this->getRequest()->getSession()->delete('adminAuthorities.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('adminAuthorities.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('adminAuthorities.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $adminAuthorities = $this->Pagination->paginate($this->fetchTable('AdminAuthorities'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                    'usersCount' => true,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('adminAuthorities.list.search', $searchData);

        // ビュー変数
        $this->set([
            'adminAuthorities' => $adminAuthorities,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * Add method
     *
     * @param int $id ID コピー用
     * @return \Cake\Http\Response|null|void
     */
    public function add($id = null)
    {
        /** @var \App\Model\Table\AdminAuthoritiesTable $adminAuthoritiesTable */
        $adminAuthoritiesTable = $this->fetchTable('AdminAuthorities');

        if ($this->getRequest()->is('post')) {
            // 入力チェック
            $adminAuthority = $adminAuthoritiesTable->newEntity((array)$this->getRequest()->getData());

            if (
                $adminAuthoritiesTable->save(
                    $adminAuthority,
                    ['saveOperation' => $this->getRequest()->getAttribute('params')]
                )
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'adminAuthoritiesFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'AdminAuthorities',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'adminAuthoritiesErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            if (!is_null($id)) {
                // エンティティー生成
                $adminAuthority = $adminAuthoritiesTable->get($id, [
                    'finder' => 'edit',
                ]);
                $adminAuthority->setNew(true);
                $adminAuthority->unset('id');
            } else {
                // 入力チェック
                $adminAuthority = $adminAuthoritiesTable->newEntity(
                    $adminAuthoritiesTable->getDefaultFieldValues(),
                    ['validate' => false]
                );
            }
        }

        // ビュー変数
        $this->set([
            'adminAuthority' => $adminAuthority,
            'valueOptions' => $adminAuthoritiesTable->getFieldValueOptions(),
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
        /** @var \App\Model\Table\AdminAuthoritiesTable $adminAuthoritiesTable */
        $adminAuthoritiesTable = $this->fetchTable('AdminAuthorities');

        // エンティティー生成
        $adminAuthority = $adminAuthoritiesTable->get($id, [
            'finder' => 'edit',
        ]);

        if ($this->getRequest()->is('post')) {
            // 入力チェック
            $adminAuthority = $adminAuthoritiesTable->patchEntity(
                $adminAuthority,
                (array)$this->getRequest()->getData()
            );

            if (
                $adminAuthoritiesTable->save(
                    $adminAuthority,
                    ['saveOperation' => $this->getRequest()->getAttribute('params')]
                )
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'adminAuthoritiesFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'AdminAuthorities',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'adminAuthoritiesErrors',
                    'element' => 'error',
                ]);
            }
        }

        // ビュー変数
        $this->set([
            'adminAuthority' => $adminAuthority,
            'valueOptions' => $adminAuthoritiesTable->getFieldValueOptions(),
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

        /** @var \App\Model\Table\AdminAuthoritiesTable $adminAuthoritiesTable */
        $adminAuthoritiesTable = $this->fetchTable('AdminAuthorities');

        // エンティティー生成
        $adminAuthority = $adminAuthoritiesTable->get($id, [
            'finder' => 'delete',
            'fields' => ['id'],
        ]);

        if (!$adminAuthority->cnaDelete()) {
            throw new NotFoundException();
        }

        $adminAuthoritiesTable->deleteOrFail(
            $adminAuthority,
            ['saveOperation' => $this->getRequest()->getAttribute('params')]
        );

        // 完了メッセージ
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'adminAuthoritiesFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'AdminAuthorities',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
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
}
