<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Validation\Validator;

/**
 * FormItemOptionGroups Model
 *
 * @method \App\Model\Entity\FormItemOptionGroup newEmptyEntity()
 * @method \App\Model\Entity\FormItemOptionGroup newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup get($primaryKey, $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemOptionGroup[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FormItemOptionGroupsTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('FormItems', [
            'foreignKey' => 'form_item_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('FormItemOptions', [
            'foreignKey' => 'form_item_option_group_id',
            'sort' => [
                'FormItemOptions.sort_no' => 'ASC',
                'FormItemOptions.id' => 'ASC',
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('select_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('select_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('select_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('selectType'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('form_item_options', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyArray('form_item_options', __(Message::ERROR_NOT_EMPTY), false)
            ->add('form_item_options', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\FormItemOptionsTable $formItemOptionsTable */
        $formItemOptionsTable = $this->getAssociation('FormItemOptions')->getTarget();

        $fieldValueOptions = [
            'selectType' => Configure::readOrFail('Master.optionGroup.selectType'),
            'formItemOptions' => $formItemOptionsTable->getFieldValueOptions(),
        ];

        return $fieldValueOptions;
    }

    /**
     * 表示順を割り当てる
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティー
     * @return void
     */
    public function assignSortNo(EntityInterface $entity)
    {
        /** @var \App\Model\Table\FormItemOptionsTable $formItemOptionsTable */
        $formItemOptionsTable = $this->getAssociation('FormItemOptions')->getTarget();

        if ($entity->has('form_item_options')) {
            $formItemOptionsTable->assignSortNo($entity->get('form_item_options'));
        }
    }

    /**
     * フォーム種別を指定して削除を行う
     *
     * @param int $formType フォーム種別
     * @param array|null $excludeEntities エンティティー配列
     * @return void
     */
    public function deleteByFormType(int $formType, ?array $excludeEntities = null)
    {
        /** @var \App\Model\Table\FormItemOptionsTable $formItemOptionsTable */
        $formItemOptionsTable = $this->getAssociation('FormItemOptions')->getTarget();

        $formItemOptions = [];
        if (isset($excludeEntities)) {
            foreach ($excludeEntities as $entity) {
                if ($entity->has('form_item_options')) {
                    foreach ($entity->get('form_item_options') as $formItemOption) {
                        $formItemOptions[] = $formItemOption;
                    }
                }
            }
        }

        $formItemOptionsTable->deleteByFormType($formType, $formItemOptions);

        $formItemsQuery = $this->getAssociation('FormItems')->find();
        $formItemsQuery->select(['FormItems.id']);
        $formItemsQuery->matching('FormGroups', function ($formGroupsQuery) use ($formType) {
            $formGroupsQuery->where(['FormGroups.form_type' => $formType]);

            return $formGroupsQuery;
        });

        $this->deleteAll([
            ['FormItemOptionGroups.form_item_id IN' => $formItemsQuery],
            $this->excludeQueryByEntities($excludeEntities),
        ]);
    }
}
