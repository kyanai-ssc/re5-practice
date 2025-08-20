<?php
declare(strict_types=1);

namespace App\Form\Admin\Users;

use App\Form\AppForm;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\InputType\Item\UserAuthority;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * 権限更新フォーム
 */
class UpdateAuthorityForm extends AppForm
{
    /**
     * @var \App\Model\Entity\FormItem|null
     */
    protected $formItem = null;

    /**
     * @var \Cake\Datasource\EntityInterface|null
     */
    protected $entity = null;

    /**
     * @inheritDoc
     */
    protected function initialize()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        foreach ($formItemsTable->getFormItems(FormGroup::FORM_TYPE_USER) as $formItem) {
            if ((string)$formItem->get('input_type') === ((string)FormItem::INPUT_TYPE_USER_AUTHORITY)) {
                $this->formItem = $formItem;
                break;
            }
        }
        if (!isset($this->formItem)) {
            throw new CakeException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('user_authority_id', 'integer');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        if (!isset($this->formItem)) {
            throw new CakeException();
        }
        $inputTypeItem = $this->formItem->getInputTypeItem();
        if (!($inputTypeItem instanceof UserAuthority)) {
            throw new CakeException();
        }
        $inputTypeItem->buildFieldsetValidator($validator);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        if (!isset($this->formItem)) {
            throw new CakeException();
        }
        $inputTypeItem = $this->formItem->getInputTypeItem();
        if (!($inputTypeItem instanceof UserAuthority)) {
            throw new CakeException();
        }
        $fieldValueOptions = [
            'user_authority_id' => $inputTypeItem->getValueOptions(false),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [];

        $entity = $this->getEntity();
        if (isset($entity)) {
            $defaultFieldValues += [
                'user_authority_id' => $entity->get('user_authority_id'),
            ];
        }

        return $defaultFieldValues;
    }

    /**
     * エンティティーを取得
     *
     * @return \Cake\Datasource\EntityInterface|null エンティティー
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * エンティティーを設定
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティー
     * @return void
     */
    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;
    }
}
