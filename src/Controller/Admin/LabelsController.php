<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\Labels\SearchForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

/**
 * Labels Controller
 */
class LabelsController extends AdminAppController
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
            'controller' => 'Labels',
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
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->fetchTable('UserAuthorities');

        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('labels.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('labels.list.search')
        );

        // 会員権限情報
        $userAuthorityLists = $userAuthoritiesTable->getSelectList(false);
        $searchForm->addFieldValueOptions(['userAuthorityId' => $userAuthorityLists]);

        $searchInputs = $labelsTable->setSearchLabelID($searchInputs);
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('labels.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $labels = $this->Pagination->paginate($this->fetchTable('Labels'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('labels.list.search', $searchData);

        // ビュー変数
        $this->set([
            'labels' => $labels,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'lowerLabelData' => $labelsTable->isLowerLabelData(),
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');

        // 新規登録可能チェック
        if (!empty($labelsTable->isLowerLabelData())) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }
        // HTTPメソッドチェック
        if ($this->getRequest()->is('post')) {
            // エンティティ生成
            $label = $labelsTable->newEntity($this->getRequest()->getData());

            if (
                $labelsTable->save(
                    $label,
                    ['saveOperation' => $this->getRequest()->getAttribute('params')]
                )
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'labelsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Labels',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set(
                    (string)__(Message::INVALID_INPUT),
                    [
                        'key' => 'labelsErrors',
                        'element' => 'error',
                    ]
                );
            }
        } else {
            // 入力値取得
            $label = $labelsTable->newEntity($labelsTable->getDefaultFieldValues(), [
                'validate' => false,
            ]);
        }

        // ビュー変数
        $this->set([
            'label' => $label,
            'valueOptions' => $labelsTable->getFieldValueOptions(),
            'excludeId' => null,
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

        $label = $labelsTable->get($id, [
            'finder' => 'edit',
        ]);

        // 編集可否チェック
        if (!$labelsTable->isAdminUsableLabel($label['id'], true)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // HTTPメソッドチェック
        if ($this->getRequest()->is('post')) {
            // 入力チェック
            $label = $labelsTable->patchEntity($label, (array)$this->getRequest()->getData());

            if (
                $labelsTable->save(
                    $label,
                    ['saveOperation' => $this->getRequest()->getAttribute('params')]
                )
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'labelsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Labels',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set(
                    (string)__(Message::INVALID_INPUT),
                    [
                        'key' => 'labelsErrors',
                        'element' => 'error',
                    ]
                );
            }
        } else {
            $labelsTable->formatDefault($label);
        }
        // ビュー変数
        $this->set([
            'label' => $label,
            'valueOptions' => $labelsTable->getFieldValueOptions(),
            'excludeId' => $label->id,
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

        $label = $labelsTable->get($id, [
            'finder' => 'delete',
            'countId' => $id,
        ]);

        if (!$labelsTable->isAdminUsableLabel($label['id'], true) || !$label->canDelete()) {
            throw new NotFoundException();
        }

        if ($labelsTable->delete($label, ['saveOperation' => $this->getRequest()->getAttribute('params')])) {
            // 完了メッセージ
            $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
                'key' => 'labelsFinish',
                'element' => 'success',
            ]);
        } else {
            $this->Flash->set((string)__(Message::ERROR_ILLEGAL_TRANSITION), [
                'key' => 'labelsFinish',
                'element' => 'error',
            ]);
        }

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Labels',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }
}
