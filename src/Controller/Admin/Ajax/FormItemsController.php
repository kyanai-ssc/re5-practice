<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxTrait;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;

/**
 * FormItems Controller
 */
class FormItemsController extends AdminAppController
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
            'add',
            'edit',
            'delete',
            'deleteMany',
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
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->fetchTable('FormItems');

        $formType = $this->getRequest()->getQuery('form_type');
        if (!is_scalar($formType) || !Configure::check('Master.form.formType.' . $formType)) {
            throw new BadRequestException();
        }

        $allFormItems = (array)$this->getRequest()->getSession()->read('formGroups.edit.' . $formType . '.formItems');

        $finish = false;
        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('FormItems', [
                'form_item_choices',
                'form_item_details',
                'form_item_option_groups',
            ]);
            $formItemInputs = (array)$this->getRequest()->getData();
            $formItemInputs['all_form_items'] = $allFormItems;

            $entityOptions = [
                'associated' => [
                    'FormItemChoices' => [],
                    'FormItemDetails' => [
                        'inputType' => Hash::get($formItemInputs, 'input_type'),
                    ],
                    'FormItemOptionGroups' => [],
                    'FormItemOptionGroups.FormItemOptions' => [],
                ],
            ];
            $formItem = $formItemsTable->newEntity($formItemInputs, $entityOptions);
            $formItemsTable->afterEntity($formItem);
            if (!$formItem->getErrors()) {
                $finish = true;
                $sessionKey = -1;
                if (!empty($allFormItems)) {
                    $sessionKey = (int)min(min(array_keys($allFormItems)), 0) - 1;
                }
                $formItem->set('session_key', $sessionKey);
                $this->getRequest()->getSession()->write(
                    'formGroups.edit.' . $formType . '.formItems.' . $formItem->get('session_key'),
                    $formItem->toArray()
                );
            }
            $formItem->setSmartLockInstance(false);
        } else {
            $formItem = $formItemsTable->newEntity($formItemsTable->getDefaultFieldValues(), [
                'validate' => false,
                'associated' => [
                    'FormItemChoices' => [
                        'validate' => false,
                    ],
                    'FormItemDetails' => [
                        'validate' => false,
                    ],
                    'FormItemOptionGroups' => [
                        'validate' => false,
                    ],
                    'FormItemOptionGroups.FormItemOptions' => [
                        'validate' => false,
                    ],
                ],
            ]);
        }

        $valueOptions = [];
        if (!$finish) {
            $valueOptions = $formItemsTable->getFieldValueOptions();
        }

        $this->set([
            'finish' => $finish,
            'formType' => $formType,
            'formItem' => $formItem,
            'valueOptions' => $valueOptions,
        ]);
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->fetchTable('FormItems');

        $formType = $this->getRequest()->getQuery('form_type');
        if (!is_scalar($formType) || !Configure::check('Master.form.formType.' . $formType)) {
            throw new BadRequestException();
        }

        $sessionKey = $this->getRequest()->getQuery('session_key');
        $allFormItems = (array)$this->getRequest()->getSession()->read('formGroups.edit.' . $formType . '.formItems');
        if (!is_scalar($sessionKey) || !isset($allFormItems[$sessionKey])) {
            throw new BadRequestException();
        }

        $formItem = null;
        if (isset($allFormItems[$sessionKey]['id'])) {
            $formItem = $formItemsTable->get($allFormItems[$sessionKey]['id'], [
                'finder' => 'edit',
            ]);
        }

        $finish = false;
        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('FormItems', [
                'form_item_choices',
                'form_item_details',
                'form_item_option_groups',
            ]);
            $formItemInputs = (array)$this->getRequest()->getData();
            $formItemInputs['all_form_items'] = $allFormItems;

            $entityOptions = [
                'associated' => [
                    'FormItemChoices' => [],
                    'FormItemDetails' => [
                        'inputType' => Hash::get($formItemInputs, 'input_type'),
                    ],
                    'FormItemOptionGroups' => [],
                    'FormItemOptionGroups.FormItemOptions' => [],
                ],
            ];
            if (!isset($formItem)) {
                $formItem = $formItemsTable->newEntity($formItemInputs, $entityOptions);
            } else {
                $formItemsTable->patchEntity($formItem, $formItemInputs, $entityOptions);
            }

            $formItemsTable->afterEntity($formItem);

            if (!$formItem->getErrors()) {
                $finish = true;
                $formItem->set('session_key', $sessionKey);
                $this->getRequest()->getSession()->write(
                    'formGroups.edit.' . $formType . '.formItems.' . $formItem->get('session_key'),
                    $formItem->toArray()
                );
            }
        } else {
            $entityOptions = [
                'validate' => false,
                'associated' => [
                    'FormItemChoices' => [
                        'validate' => false,
                    ],
                    'FormItemDetails' => [
                        'validate' => false,
                    ],
                    'FormItemOptionGroups' => [
                        'validate' => false,
                    ],
                    'FormItemOptionGroups.FormItemOptions' => [
                        'validate' => false,
                    ],
                ],
            ];
            if (!isset($formItem)) {
                $formItem = $formItemsTable->newEntity($allFormItems[$sessionKey], $entityOptions);
            } else {
                $formItemsTable->patchEntity($formItem, $allFormItems[$sessionKey], $entityOptions);
            }
        }

        $this->set([
            'finish' => $finish,
            'formType' => $formType,
            'sessionKey' => $sessionKey,
            'formItem' => $formItem,
            'valueOptions' => $formItemsTable->getFieldValueOptions(),
        ]);
    }

    /**
     * Delete method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function delete()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->fetchTable('FormItems');

        $formType = $this->getRequest()->getQuery('form_type');
        if (!is_scalar($formType) || !Configure::check('Master.form.formType.' . $formType)) {
            throw new BadRequestException();
        }

        $sessionKey = $this->getRequest()->getQuery('session_key');
        if (
            !is_scalar($sessionKey)
            || !$this->getRequest()->getSession()->check('formGroups.edit.' . $formType . '.formItems.' . $sessionKey)
        ) {
            throw new BadRequestException();
        }

        $formItems = $this->getRequest()->getSession()->read(
            'formGroups.edit.' . $formType . '.formItems.' . $sessionKey
        );
        if (is_array($formItems) && Hash::get($formItems, 'id', false) !== false) {
            $formItem = $formItemsTable->get(Hash::get($formItems, 'id'), [
                'finder' => 'edit',
            ]);

            if (!$formItem->canDelete()) {
                throw new BadRequestException();
            }
        }

        $this->getRequest()->getSession()->delete('formGroups.edit.' . $formType . '.formItems.' . $sessionKey);
    }

    /**
     * Delete all method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function deleteMany()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->fetchTable('FormItems');

        $formType = $this->getRequest()->getQuery('form_type');
        if (!is_scalar($formType) || !Configure::check('Master.form.formType.' . $formType)) {
            throw new BadRequestException();
        }

        $sessionKey = $this->getRequest()->getData('session_key');
        if (!isset($sessionKey)) {
            return;
        }
        if (!is_array($sessionKey)) {
            throw new BadRequestException();
        }

        foreach ($sessionKey as $key) {
            if (!is_scalar($key)) {
                throw new BadRequestException();
            }
            $data = $this->getRequest()->getSession()->read('formGroups.edit.' . $formType . '.formItems.' . $key);
            if (!is_array($data)) {
                throw new BadRequestException();
            }

            if (isset($data['id'])) {
                $formItem = $formItemsTable->get($data['id'], [
                    'finder' => 'edit',
                ]);

                if (!$formItem->canDelete()) {
                    throw new BadRequestException(Message::ERROR_DELETE_FORM_GROUP);
                }
            }
        }

        foreach ($sessionKey as $key) {
            $this->getRequest()->getSession()->delete('formGroups.edit.' . $formType . '.formItems.' . $key);
        }
    }
}
