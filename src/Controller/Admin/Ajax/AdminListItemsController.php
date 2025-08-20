<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxTrait;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;

/**
 * AdminListItems Controller
 */
class AdminListItemsController extends AdminAppController
{
    use AjaxTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Ajax');
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'edit',
        ]);

        return $response;
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        /** @var \App\Model\Table\AdminListItemsTable $adminListItemsTable */
        $adminListItemsTable = $this->fetchTable('AdminListItems');

        $type = $this->getRequest()->getQuery('type');
        if (!is_scalar($type) || !Configure::check('Master.adminListItems.type.' . $type)) {
            throw new BadRequestException();
        }

        $adminListItem = $adminListItemsTable->find('edit', [
            'finder' => 'edit',
            'inputs' => [
                'type' => $type,
            ],
        ])->first();

        if (!$adminListItem instanceof EntityInterface) {
            throw new BadRequestException();
        }

        $finish = false;
        if ($this->getRequest()->is('post')) {
            $adminListItemInputs = (array)$this->getRequest()->getData();
            $adminListItemInputs['type'] = $type;
            $adminListItemsTable->patchEntity($adminListItem, $adminListItemInputs);
            $saveOptions = [
                'accessibleDirty' => true,
                'saveOperation' => $this->getRequest()->getAttribute('params'),
            ];
            if ($adminListItemsTable->save($adminListItem, $saveOptions)) {
                $finish = true;
            }
        }

        $valueOptions = [];
        if (!$finish) {
            $valueOptions = $adminListItemsTable->getFieldValueOptions();
        }

        $this->set([
            'finish' => $finish,
            'type' => $type,
            'adminListItem' => $adminListItem,
            'valueOptions' => $valueOptions,
        ]);
    }
}
