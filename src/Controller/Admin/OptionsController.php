<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\Options\SearchForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * Options Controller
 */
class OptionsController extends AdminAppController
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
            'controller' => 'Options',
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
            $this->getRequest()->getSession()->delete('options.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('options.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('options.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $options = $this->Pagination->paginate($this->fetchTable('Options'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('options.list.search', $searchData);

        // ビュー変数
        $this->set([
            'options' => $options,
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
        /** @var \App\Model\Table\OptionsTable $optionsTable */
        $optionsTable = $this->fetchTable('Options');

        if ($this->getRequest()->is('post')) {
            // エンティティ生成
            $this->RequestFilter->setRebalanceInputs('Options', 'option_stock_settings');
            $option = $optionsTable->newEntity($this->getRequest()->getData(), [
                'associated' => [
                    'OptionStockSettings' => [],
                ],
            ]);
            if ($optionsTable->save($option, ['saveOperation' => $this->getRequest()->getAttribute('params')])) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'optionsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Options',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'optionsErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            // エンティティ生成
            $option = $optionsTable->newEntity($optionsTable->getDefaultFieldValues(), [
                'validate' => false,
                'associated' => [
                    'OptionStockSettings' => [
                        'validate' => false,
                    ],
                ],
            ]);
        }

        // ビュー変数
        $this->set([
            'option' => $option,
            'valueOptions' => $optionsTable->getFieldValueOptions(),
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
        /** @var \App\Model\Table\OptionsTable $optionsTable */
        $optionsTable = $this->fetchTable('Options');

        $option = $optionsTable->get($id, [
            'finder' => 'edit',
        ]);

        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('Options', 'option_stock_settings');
            // エンティティ生成
            $option = $optionsTable->patchEntity($option, (array)$this->getRequest()->getData(), [
                'associated' => [
                    'OptionStockSettings' => [],
                ],
            ]);

            if ($optionsTable->save($option, ['saveOperation' => $this->getRequest()->getAttribute('params')])) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'optionsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Options',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'optionsErrors',
                    'element' => 'error',
                ]);
            }
        }
        // ビュー変数
        $this->set([
            'option' => $option,
            'valueOptions' => $optionsTable->getFieldValueOptions(),
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
        /** @var \App\Model\Table\OptionsTable $optionsTable */
        $optionsTable = $this->fetchTable('Options');

        // エンティティー生成
        $option = $optionsTable->get($id, [
            'finder' => 'delete',
        ]);

        if (!$option->canDelete()) {
            throw new NotFoundException();
        }

        $optionsTable->deleteOrFail(
            $option,
            ['saveOperation' => $this->getRequest()->getAttribute('params')]
        );

        // 完了メッセージ
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'optionsFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Options',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }
}
