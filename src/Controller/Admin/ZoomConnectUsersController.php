<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;

/**
 * ZoomConnectUsers Controller
 */
class ZoomConnectUsersController extends AdminAppController
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
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        /** @var \App\Model\Table\ZoomConnectUsersTable $zoomConnectUsersTable */
        $zoomConnectUsersTable = $this->fetchTable('ZoomConnectUsers');

        $zoomConnectUser = $zoomConnectUsersTable->getData();
        if (isset($zoomConnectUser)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            $zoomConnectUser = $zoomConnectUsersTable->newEntity($this->getRequest()->getData());
            if (
                $zoomConnectUsersTable->save($zoomConnectUser, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'zoomConnectUsersFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'ZoomConnectUsers',
                    'action' => 'view',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'zoomConnectUsersErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            $zoomConnectUser = $zoomConnectUsersTable->newEntity($zoomConnectUsersTable->getDefaultFieldValues(), [
                'validate' => false,
            ]);
        }

        $this->set([
            'zoomConnectUser' => $zoomConnectUser,
        ]);
    }

    /**
     * View method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function view()
    {
        /** @var \App\Model\Table\ZoomConnectUsersTable $zoomConnectUsersTable */
        $zoomConnectUsersTable = $this->fetchTable('ZoomConnectUsers');

        $this->set([
            'zoomConnectUser' => $zoomConnectUsersTable->getData(),
        ]);
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        /** @var \App\Model\Table\ZoomConnectUsersTable $zoomConnectUsersTable */
        $zoomConnectUsersTable = $this->fetchTable('ZoomConnectUsers');

        $zoomConnectUser = $zoomConnectUsersTable->getData();
        if (!isset($zoomConnectUser)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            $zoomConnectUsersTable->patchEntity($zoomConnectUser, (array)$this->getRequest()->getData());
            if (
                $zoomConnectUsersTable->save($zoomConnectUser, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'zoomConnectUsersFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'ZoomConnectUsers',
                    'action' => 'view',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'zoomConnectUsersErrors',
                    'element' => 'error',
                ]);
            }
        }

        $this->set([
            'zoomConnectUser' => $zoomConnectUser,
        ]);
    }

    /**
     * Delete method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function delete()
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\ZoomConnectUsersTable $zoomConnectUsersTable */
        $zoomConnectUsersTable = $this->fetchTable('ZoomConnectUsers');

        $zoomConnectUser = $zoomConnectUsersTable->getData();
        if (!isset($zoomConnectUser)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $zoomConnectUsersTable->deleteOrFail($zoomConnectUser, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ]);
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'zoomConnectUsersFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'ZoomConnectUsers',
            'action' => 'view',
        ]);
    }
}
