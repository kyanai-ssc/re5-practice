<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Option;
use App\Validation\CustomValidation;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * FormPatternOptions Model
 *
 * @method \App\Model\Entity\FormPatternOption newEmptyEntity()
 * @method \App\Model\Entity\FormPatternOption newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FormPatternOption[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FormPatternOption get($primaryKey, $options = [])
 * @method \App\Model\Entity\FormPatternOption findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FormPatternOption patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FormPatternOption[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FormPatternOption|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormPatternOption saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormPatternOption[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormPatternOption[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormPatternOption[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormPatternOption[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FormPatternOptionsTable extends AppTable
{
    /**
     * @var array
     */
    protected $checkedOptions = [];

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('FormPatterns', [
            'foreignKey' => 'form_pattern_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('FormItemOptions', [
            'foreignKey' => 'form_item_option_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->requirePresence('form_item_id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('form_item_id', __(Message::ERROR_NOT_EMPTY), false)
            ->add('form_item_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('formItems')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator->requirePresence('form_item_option_id', false)
            ->allowEmptyString('form_item_option_id')
            ->add('form_item_option_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('options')),
                    ],
                    'on' => function ($context) {
                        if (!empty(Hash::get($context['data'], 'form_item_option_id'))) {
                            return true;
                        }

                        return false;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * オプション一覧取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEventOptionsList(Query $query, array $options)
    {
        $query->select([
            'id',
            'form_pattern_id',
            'form_item_option_id',
        ]);

        $query->contain([
            'FormPatterns' => [
                'fields' => [
                    'id',
                ],
            ],
        ], true);

        $eventId = (array)Hash::get($options, 'inputs.event_id');
        $eventsQuery = $this->getAssociation('FormPatterns.Events')->find();
        $eventsQuery->select([
            'Events.form_pattern_id',
        ]);
        $eventsQuery->where([
            'Events.id IN' => $eventId,
        ]);
        $query->where([
            'FormPatterns.id IN' => $eventsQuery,
        ]);

        $formItemOptionsQuery = $this->getAssociation('FormItemOptions')->find();
        $formItemOptionsQuery->select(['FormItemOptions.id']);
        $formItemOptionsQuery->matching('Options', function ($optionsQuery) use ($options) {
            $usageTimestampFrom = Hash::get($options, 'inputs.usage_timestamp_from');
            if (isset($usageTimestampFrom) && $usageTimestampFrom !== '') {
                $optionsQuery->where([
                    [
                        'OR' => [
                            'Options.usage_timestamp_from IS NULL',
                            'Options.usage_timestamp_from <=' => $usageTimestampFrom,
                        ],
                    ],
                    [
                        'OR' => [
                            'Options.usage_timestamp_to IS NULL',
                            'Options.usage_timestamp_to >' => $usageTimestampFrom,
                        ],
                    ],
                ]);
            }

            $publicFlg = Hash::get($options, 'inputs.public_flg', true);
            $includeValues = Hash::get($options, 'inputs.include_values');
            if ($publicFlg) {
                $publicWhere = [
                    'Options.public_flg' => Option::PUBLIC_FLG_ON,
                ];
                if (!empty($includeValues)) {
                    $publicWhere['Options.id IN'] = array_map('intval', (array)$includeValues);
                }
                $optionsQuery->where([
                    'OR' => $publicWhere,
                ]);
            }

            return $optionsQuery;
        });
        $query->where([
            'FormPatternOptions.form_item_option_id IN' => $formItemOptionsQuery,
        ]);

        return $query;
    }

    /**
     * フォーム種別を指定して削除を行う
     *
     * @param int $formType フォーム種別
     * @param array|null $excludeFormItemOptionId 除外するフォーム項目オプションID
     * @return void
     */
    public function deleteByFormType(int $formType, ?array $excludeFormItemOptionId = null)
    {
        $formItemOptionsQuery = $this->getAssociation('FormItemOptions')->find();
        $formItemOptionsQuery->select(['FormItemOptions.id']);
        $formItemOptionsQuery->matching('FormItemOptionGroups', function ($formItemOptionGroupsQuery) use ($formType) {
            $formItemOptionGroupsQuery->matching('FormItems', function ($formItemsQuery) use ($formType) {
                $formItemsQuery->matching('FormGroups', function ($formGroupsQuery) use ($formType) {
                    $formGroupsQuery->where(['FormGroups.form_type' => $formType]);

                    return $formGroupsQuery;
                });

                return $formItemsQuery;
            });

            return $formItemOptionGroupsQuery;
        });

        $where = [
            'FormPatternOptions.form_item_option_id IN' => $formItemOptionsQuery,
        ];
        if (!empty($excludeFormItemOptionId)) {
            $where['FormPatternOptions.form_item_option_id NOT IN'] = (array)$excludeFormItemOptionId;
        }
        $this->deleteAll($where);
    }

    /**
     * 表示パターン設定でチェックがつけられているオプションか判定
     * (予約枠を指定)
     *
     * @param int $eventId 予約枠ID
     * @param int $formItemId フォーム項目ID
     * @param int $optionId オプションID
     * @return bool
     */
    public function isCheckedOptionForEvent($eventId, $formItemId, $optionId)
    {
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');

        if (!isset($this->checkedOptions[$eventId]) || !isset($this->checkedOptions[$eventId][$formItemId])) {
            $event = $eventsTable->get($eventId);

            $this->checkedOptions[$eventId][$formItemId] = $this->getCheckedOptions(
                $event->get('form_pattern_id'),
                $formItemId
            );
        }

        return isset($this->checkedOptions[$eventId][$formItemId][$optionId]);
    }

    /**
     * 表示パターン設定でチェックがつけられているオプションのIDを取得
     *
     * @param int $formPatternId フォームパターンID
     * @param int $formItemId フォーム項目ID
     * @return array
     */
    public function getCheckedOptions($formPatternId, $formItemId)
    {
        $formPatternOptions = $this->find('checkedOptions', [
            'inputs' => [
                'form_pattern_id' => $formPatternId,
                'form_item_id' => $formItemId,
            ],
        ])
        ->toArray();

        $result = [];
        foreach ((array)$formPatternOptions as $formPatternOption) {
            $optionId = $formPatternOption['form_item_option']['option_id'];
            $result[$optionId] = $optionId;
        }

        return $result;
    }

    /**
     * 表示パターン設定でチェックがつけられているオプションを取得するためのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCheckedOptions(Query $query, array $options)
    {
        $query->select([
            'id',
        ])
        ->contain([
            'FormItemOptions' => [
                'fields' => [
                    'id',
                    'option_id',
                ],
            ],
        ])
        ->contain([
            'FormItemOptions.FormItemOptionGroups' => [
                'fields' => [
                    'id',
                ],
            ],
        ]);

        $formPatternId = Hash::get($options, 'inputs.form_pattern_id');
        if (isset($formPatternId)) {
            $query->where(['FormPatternOptions.form_pattern_id' => $formPatternId]);
        }

        $formItemId = Hash::get($options, 'inputs.form_item_id');
        if (isset($formItemId)) {
            $query->where(['FormItemOptionGroups.form_item_id' => $formItemId]);
        }

        $excludeFormItemIds = Hash::get($options, 'inputs.exclude_form_item_ids');
        if (isset($excludeFormItemIds)) {
            $query->where(['FormItemOptionGroups.form_item_id NOT IN' => (array)$excludeFormItemIds]);
        }

        $query->enableHydration(false);
        $query->disableBufferedResults();

        return $query;
    }
}
