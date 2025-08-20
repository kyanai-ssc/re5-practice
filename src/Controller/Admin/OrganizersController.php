<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\Organizers\SearchForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * Organizers Controller
 */
class OrganizersController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->fetchTable('Organizers');

        $organizersTable->checkPlan();
    }

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
     * List method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function list()
    {
        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('organizers.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('organizers.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('organizers.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $organizers = $this->Pagination->paginate($this->fetchTable('Organizers'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('organizers.list.search', $searchData);

        // ビュー変数
        $this->set([
            'organizers' => $organizers,
            'searchForm' => $searchForm,
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->fetchTable('Organizers');

        if ($this->getRequest()->is('post')) {
            $organizer = $organizersTable->newEntity($this->getRequest()->getData());
            if (
                $organizersTable->save($organizer, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'organizersFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Organizers',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'organizersErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            $organizer = $organizersTable->newEntity($organizersTable->getDefaultFieldValues(), [
                'validate' => false,
            ]);
        }

        $this->set([
            'organizer' => $organizer,
            'valueOptions' => $organizersTable->getFieldValueOptions(),
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
        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->fetchTable('Organizers');

        $organizer = $organizersTable->get($id, [
            'finder' => 'edit',
        ]);

        if ($this->getRequest()->is('post')) {
            $organizersTable->patchEntity($organizer, (array)$this->getRequest()->getData());
            if (
                $organizersTable->save($organizer, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'organizersFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Organizers',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'organizersErrors',
                    'element' => 'error',
                ]);
            }
        }

        $this->set([
            'organizer' => $organizer,
            'valueOptions' => $organizersTable->getFieldValueOptions(),
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

        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->fetchTable('Organizers');

        $organizer = $organizersTable->get($id, [
            'finder' => 'edit',
        ]);
        if (!$organizer->canDelete()) {
            throw new NotFoundException();
        }

        $organizersTable->deleteOrFail($organizer, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ]);
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'organizersFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Organizers',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }
}
