<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\Tags\SearchForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * Tags Controller
 */
class TagsController extends AdminAppController
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
            'controller' => 'Tags',
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
            $this->getRequest()->getSession()->delete('tag.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('tag.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('tag.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $tags = $this->Pagination->paginate($this->fetchTable('TagGroups'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('tag.list.search', $searchData);

        // ビュー変数
        $this->set([
            'tags' => $tags,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        /** @var \App\Model\Table\TagGroupsTable $tagGroupsTable */
        $tagGroupsTable = $this->fetchTable('TagGroups');

        // HTTPメソッドチェック
        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('TagGroups', 'tags');
            // エンティティ生成
            $tag = $tagGroupsTable->newEntity($this->getRequest()->getData(), [
                'associated' => [
                    'Tags' => [],
                ],
            ]);

            if ($tagGroupsTable->save($tag, ['saveOperation' => $this->getRequest()->getAttribute('params')])) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'tagsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Tags',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set(
                    (string)__(Message::INVALID_INPUT),
                    [
                        'key' => 'tagsErrors',
                        'element' => 'error',
                    ]
                );
            }
        } else {
            // エンティティ生成
            $tag = $tagGroupsTable->newEntity($tagGroupsTable->getDefaultFieldValues(), [
                'validate' => false,
                'associated' => [
                    'Tags' => ['validate' => false],
                ],
            ]);
        }

        // ビュー変数
        $this->set([
            'tag' => $tag,
            'valueOptions' => $tagGroupsTable->getFieldValueOptions(),
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
        /** @var \App\Model\Table\TagGroupsTable $tagGroupsTable */
        $tagGroupsTable = $this->fetchTable('TagGroups');

        // エンティティー生成
        $tag = $tagGroupsTable->get($id, [
            'finder' => 'edit',
        ]);

        // HTTPメソッドチェック
        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('TagGroups', 'tags');
            $tag = $tagGroupsTable->patchEntity($tag, (array)$this->getRequest()->getData(), [
                'associated' => [
                    'Tags' => [],
                ],
            ]);

            if ($tagGroupsTable->save($tag, ['saveOperation' => $this->getRequest()->getAttribute('params')])) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'tagsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Tags',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set(
                    (string)__(Message::INVALID_INPUT),
                    [
                        'key' => 'tagsErrors',
                        'element' => 'error',
                    ]
                );
            }
        }

        // ビュー変数
        $this->set([
            'tag' => $tag,
            'valueOptions' => $tagGroupsTable->getFieldValueOptions(),
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
        // エンティティー生成
        $tag = $this->fetchTable('TagGroups')->get($id, [
            'finder' => 'delete',
        ]);

        // データ削除
        $this->fetchTable('TagGroups')->deleteOrFail($tag, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ]);
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'tagsFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Tags',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }
}
