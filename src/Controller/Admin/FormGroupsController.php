<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

/**
 * Forms Controller
 */
class FormGroupsController extends AdminAppController
{
    public const TOKEN_VALIDATION_EDIT = 'form_groups_edit';

    /**
     * Edit method
     *
     * @param int $formType フォームタイプ
     * @return \Cake\Http\Response|null|void
     */
    public function edit($formType = null)
    {
        /** @var \App\Model\Table\FormGroupsTable $formGroupsTable */
        $formGroupsTable = $this->fetchTable('FormGroups');

        if (!$formGroupsTable->validateFormType($formType)) {
            throw new NotFoundException();
        }

        $formGroups = $formGroupsTable->find('edit', [
            'inputs' => [
                'form_type' => $formType,
            ],
        ])->toArray();

        $smartLock = new SmartLockLinkage();

        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('FormGroups', 'form_groups');
            $formGroupsInputs = $this->getRequest()->getData('form_groups');

            $formGroupsForPatchEntities = $formGroups;

            $akerunGroups = [];
            // Akerun が有効ではない場合、項目が削除されるのを防ぐため Akerun用の入力項目を追加する
            if (!$smartLock->useAkerun()) {
                $akerunGroups = $formGroupsTable->getAkerunFormGroup();
                foreach ($akerunGroups as $akerunGroup) {
                    $formGroupsForPatchEntities[] = $akerunGroup;
                }
            }

            $formGroups = $formGroupsTable->patchEntitiesPreserveId($formGroupsForPatchEntities, $formGroupsInputs, [
                'formType' => $formType,
                'formItems' => $this->getRequest()->getSession()->read('formGroups.edit.' . $formType . '.formItems'),
            ]);
            if ($this->TokenValidation->validate(static::TOKEN_VALIDATION_EDIT)) {
                $saveOptions = [
                    'formType' => $formType,
                    'accessibleDirty' => true,
                    'saveOperation' => [
                        'controller' => Configure::read('Master.form.operationUrl.' . $formType),
                        'action' => $this->getRequest()->getParam('action'),
                    ],
                    'saveKey' => 'form_type',
                    'afterSave' => false,
                ];
                if ($formGroupsTable->saveMany($formGroups, $saveOptions)) {
                    $this->getRequest()->getSession()->delete('formGroups.edit');
                    $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                        'key' => 'formGroupsFinish',
                        'element' => 'success',
                    ]);

                    return $this->redirect([
                        'prefix' => 'Admin',
                        'controller' => 'FormGroups',
                        'action' => 'edit',
                        'id' => $formType,
                    ]);
                } else {
                    $this->Flash->set((string)__(Message::INVALID_INPUT), [
                        'key' => 'formGroupsErrors',
                        'element' => 'error',
                    ]);
                }
            }
            // Akerunが有効でない場合、入力エラー時に表示されないようにエンティティから削除する
            if (!$smartLock->useAkerun()) {
                foreach ($akerunGroups as $akerunGroup) {
                    foreach ($formGroups as $key => $formGroup) {
                        if ($formGroup->get('id') === $akerunGroup->get('id')) {
                            unset($formGroups[$key]);
                        }
                    }
                }
            }
        } else {
            $formItems = [];
            foreach ($formGroups as $formGroup) {
                $formItems += $formGroup->get('form_items_data');
            }
            $this->getRequest()->getSession()->delete('formGroups.edit.' . $formType);
            $this->getRequest()->getSession()->write('formGroups.edit.' . $formType . '.formItems', $formItems);
        }

        $this->TokenValidation->generate(static::TOKEN_VALIDATION_EDIT);

        $this->set([
            'formGroups' => $formGroups,
            'valueOptions' => $formGroupsTable->getFieldValueOptions(),
        ]);
    }
}
