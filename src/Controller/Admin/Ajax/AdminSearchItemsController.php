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
 * AdminSearchItems Controller
 */
class AdminSearchItemsController extends AdminAppController
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
        /** @var \App\Model\Table\AdminSearchItemsTable $adminSearchItemsTable */
        $adminSearchItemsTable = $this->fetchTable('AdminSearchItems');

        $type = $this->getRequest()->getQuery('type');
        if (!is_scalar($type) || !Configure::check('Master.adminSearchItems.type.' . $type)) {
            throw new BadRequestException();
        }

        $adminSearchItem = $adminSearchItemsTable->find('edit', [
            'finder' => 'edit',
            'inputs' => [
                'type' => $type,
            ],
        ])->first();

        if (!$adminSearchItem instanceof EntityInterface) {
            $identity = $this->Authentication->getIdentity();
            $adminId = null;
            if (isset($identity)) {
                /** @var \Authentication\Identity $identity */
                $adminId = $identity->offsetGet('id');
            }
            $adminSearchItem = $adminSearchItemsTable->createNewEntity($adminId, $type);
        }

        $finish = false;
        if ($this->getRequest()->is('post')) {
            $adminSearchItemInputs = (array)$this->getRequest()->getData();
            $adminSearchItemInputs['type'] = $type;
            $adminSearchItemsTable->patchEntity($adminSearchItem, (array)$adminSearchItemInputs);

            $saveOptions = [
                'accessibleDirty' => true,
                'saveOperation' => $this->getRequest()->getAttribute('params'),
            ];
            if ($adminSearchItemsTable->save($adminSearchItem, $saveOptions)) {
                $finish = true;
            }
        }

        $valueOptions = [];
        if (!$finish) {
            $valueOptions = $adminSearchItemsTable->getFieldValueOptions();
        }

        $this->set([
            'finish' => $finish,
            'type' => $type,
            'adminSearchItem' => $adminSearchItem,
            'valueOptions' => $valueOptions,
        ]);
    }
}
