<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use Cake\Core\Exception\CakeException;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * FormItemOptions Model
 *
 * @method \App\Model\Entity\FormItemOption newEmptyEntity()
 * @method \App\Model\Entity\FormItemOption newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FormItemOption[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FormItemOption get($primaryKey, $options = [])
 * @method \App\Model\Entity\FormItemOption findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FormItemOption patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FormItemOption[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FormItemOption|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormItemOption saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormItemOption[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemOption[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemOption[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemOption[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FormItemOptionsTable extends AppTable
{
    public const STOCK_RANGE_MAX = 10000000;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('FormItemOptionGroups', [
            'foreignKey' => 'form_item_option_group_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Options', [
            'foreignKey' => 'option_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('FormPatternOptions', [
            'foreignKey' => 'form_item_option_id',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        if (!($validator instanceof KuchenValidator)) {
            throw new CakeException();
        }

        $validator
            ->requirePresence('option_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('option_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('option_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('optionId'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('stock_range_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('stock_range_from', __(Message::ERROR_NOT_EMPTY), false)
            ->add('stock_range_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber', true],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'compareLessOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::STOCK_RANGE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::STOCK_RANGE_MAX),
                ],
            ]);

        $validator
            ->requirePresence('stock_range_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('stock_range_to', __(Message::ERROR_NOT_EMPTY), false)
            ->add('stock_range_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber', true],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'compareLessOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::STOCK_RANGE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::STOCK_RANGE_MAX),
                ],
                'compareFields' => [
                    'rule' => ['compareFields', 'stock_range_from', '>='],
                    'last' => true,
                    'message' => __(Message::ERROR_LESS_THAN_FROM),
                    'on' => function () use ($validator) {
                        // 比較対象にエラーがある場合は比較処理を行わない
                        return $validator->isValid('stock_range_from');
                    },
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'optionId' => $this->getAssociation('Options')->find('createFormItem')->toArray(),
        ];

        return $fieldValueOptions;
    }

    /**
     * オプション一覧取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findOptionsList(Query $query, array $options)
    {
        $query->select([
            'id',
            'form_item_option_group_id',
            'option_id',
            'stock_range_from',
            'stock_range_to',
        ]);

        $query->contain([
            'Options' => [
                'fields' => [
                    'id',
                    'name',
                    'stock',
                    'stock_unit',
                    'charge',
                    'description',
                ],
            ],
        ], true);

        $formItemOptionGroupId = (array)Hash::get($options, 'inputs.form_item_option_group_id');
        $query->where([
            'FormItemOptions.form_item_option_group_id IN' => $formItemOptionGroupId,
        ]);

        $query->order([
            'FormItemOptions.sort_no' => 'ASC',
            'FormItemOptions.id' => 'ASC',
        ], true);

        return $query;
    }

    /**
     * 表示順を割り当てる
     *
     * @param array $entities エンティティー配列
     * @return void
     */
    public function assignSortNo(array $entities)
    {
        $sortNo = 0;
        foreach ($entities as $entity) {
            $sortNo += 1;
            $entity->set('sort_no', $sortNo);
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
        /** @var \App\Model\Table\FormPatternOptionsTable $formPatternOptionsTable */
        $formPatternOptionsTable = $this->getAssociation('FormPatternOptions')->getTarget();

        // フォームパターンオプション削除
        $excludeFormItemOptionId = [];
        if (isset($excludeEntities)) {
            foreach ($excludeEntities as $entity) {
                if ($entity->has('id')) {
                    $excludeFormItemOptionId[] = $entity->get('id');
                }
            }
        }
        $formPatternOptionsTable->deleteByFormType($formType, $excludeFormItemOptionId);

        $formItemOptionGroupsQuery = $this->getAssociation('FormItemOptionGroups')->find();
        $formItemOptionGroupsQuery->select(['FormItemOptionGroups.id']);
        $formItemOptionGroupsQuery->matching('FormItems', function ($formItemsQuery) use ($formType) {
            $formItemsQuery->matching('FormGroups', function ($formGroupsQuery) use ($formType) {
                $formGroupsQuery->where(['FormGroups.form_type' => $formType]);

                return $formGroupsQuery;
            });

            return $formItemsQuery;
        });

        $this->deleteAll([
            ['FormItemOptions.form_item_option_group_id IN' => $formItemOptionGroupsQuery],
            $this->excludeQueryByEntities($excludeEntities),
        ]);
    }
}
