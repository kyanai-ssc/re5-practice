<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Option;
use App\Utility\DateTimeUtility;
use App\Validation\CustomValidation;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Expression\QueryExpression;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * Options Model
 *
 * @method \App\Model\Entity\Option newEmptyEntity()
 * @method \App\Model\Entity\Option newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Option[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Option get($primaryKey, $options = [])
 * @method \App\Model\Entity\Option findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Option patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Option[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Option|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Option saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Option[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Option[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Option[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Option[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class OptionsTable extends AppTable
{
    public const NAME_MAX = 100;
    public const STOCK_UNIT_MAX = 100;
    public const CHARGE_MAX = 10000000;
    public const DESCRIPTION_MAX = 10000;
    public const OPTION_UNIT_TIME_MAX = 3;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('FormItemOptions', [
            'foreignKey' => 'option_id',
        ]);

        $this->hasMany('OptionStockSettings', [
            'foreignKey' => 'option_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);

        $this->hasMany('ReservationOptions', [
            'foreignKey' => 'option_id',
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'publicFlg' => Configure::readOrFail('Master.option.publicFlg'),
        ];

        return $fieldValueOptions;
    }

    /**
     * Model.beforeMarshalイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \ArrayObject $data データ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        if (isset($data['name']) && is_string($data['name'])) {
            $data->offsetSet('name', $this->csvFormat()->replaceSeparetorForMultiple($data['name']));
        }
        if (!isset($data['option_stock_settings']) || !is_array($data['option_stock_settings'])) {
            $data->offsetSet('option_stock_settings', []);
        }
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('name', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('name', __(Message::ERROR_NOT_EMPTY), false)
            ->add('name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('charge', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('charge', __(Message::ERROR_NOT_EMPTY), false)
            ->add('charge', [
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
                'lessThan' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::CHARGE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::CHARGE_MAX),
                ],
            ]);

        $validator
            ->requirePresence('stock', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('stock')
            ->add('stock', [
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
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, CustomValidation::BIGINT_MAX),
                ],
            ]);

        $validator
            ->requirePresence('stock_unit', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('stock_unit')
            ->add('stock_unit', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::STOCK_UNIT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::STOCK_UNIT_MAX),
                ],
            ]);

        $validator
            ->requirePresence('public_flg', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('public_flg', __(Message::ERROR_NOT_EMPTY), false)
            ->add('public_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('publicFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator->requirePresence('usage_timestamp_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDateTime('usage_timestamp_from')
            ->add('usage_timestamp_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'datetime' => [
                    'rule' => [
                        'datetime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
            ]);

        $validator->requirePresence('usage_timestamp_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDateTime('usage_timestamp_to')
            ->add('usage_timestamp_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'datetime' => [
                    'rule' => [
                        'datetime',
                        'ymd',
                    ],
                    'message' => __(Message::ERROR_DATE_TIME),
                    'last' => true,
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'usage_timestamp_from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'last' => true,
                    'on' => function ($context) use ($validator) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        if (
                            $validator->isValid('usage_timestamp_from')
                            && Validation::notBlank(Hash::get($context['data'], 'usage_timestamp_from'))
                        ) {
                            return true;
                        }

                        return false;
                    },
                ],
            ]);

        $validator
            ->requirePresence('description', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('description')
            ->add('description', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::DESCRIPTION_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::DESCRIPTION_MAX),
                ],
            ]);

        $validator->requirePresence('option_stock_settings', false)
            ->allowEmptyArray('option_stock_settings')
            ->array('option_stock_settings', __(Message::ERROR_NOT_EMPTY));

        $validator
            ->requirePresence('option_unit_time', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('option_unit_time')
            ->add('option_unit_time', [
                'halfSizeNumber' => [
                    'rule' => ['custom', '/^[0-9]+$/'],
                    'last' => true,
                    'message' => __(Message::ERROR_HALF_SIZE_NUMBER),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::OPTION_UNIT_TIME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::OPTION_UNIT_TIME_MAX),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(function ($entity) {
            return $this->checkSpecificDate($entity);
        }, 'checkSpecificDate');

        $rules->addUpdate(function ($entity) {
            return $this->checkChangeStock($entity);
        }, 'checkStockChange');

        return $rules;
    }

    /**
     * オプションの在庫変更チェック
     *
     * @param \Cake\ORM\Entity $entity entity
     * @return bool
     */
    public function checkChangeStock(Entity $entity)
    {
        $success = true;
        /** @var \App\Model\Table\ReservationOptionsTable $reservationOptionsTable */
        $reservationOptionsTable = $this->getTableLocator()->get('ReservationOptions');

        // 設定期間内の在庫チェック
        $original = $entity->getOriginalValues();
        $originalStockSettings = Hash::combine($original['option_stock_settings'], '{*}.id', '{*}');

        if ($entity->isDirty('usage_timestamp_from')) {
            if (
                $reservationOptionsTable->checkReserveOptionsInDate(
                    $entity->get('id'),
                    $entity->get('usage_timestamp_from'),
                    $entity->get('usage_timestamp_to')
                )
            ) {
                $entity->setError('usage_timestamp_to', (string)__(Message::ERROR_CHANGE_OPTION_STOCK));
                $success = false;
            }
        }

        $checkStock = false;

        // 個別設定の在庫チェック
        $optionStockSettingIds = [];
        foreach ($entity->get('option_stock_settings') as $optionStockSetting) {
            if (
                $optionStockSetting->isDirty('usage_timestamp_from')
                || $optionStockSetting->isDirty('usage_timestamp_to')
                || $optionStockSetting->isDirty('stock')
            ) {
                if (
                    !$this->checkStockBetweenDate(
                        $entity,
                        $optionStockSetting->get('usage_timestamp_from'),
                        $optionStockSetting->get('usage_timestamp_to'),
                        false
                    )
                ) {
                    $optionStockSetting->setError('stock', (string)__(Message::ERROR_CHANGE_OPTION_STOCK));
                    $success = false;
                }
            }

            $optionStockSettingIds[] = $optionStockSetting->get('id');
            $checkStock = true;
        }

        if (!empty(array_diff(array_keys($originalStockSettings), $optionStockSettingIds))) {
            $checkStock = true;
        }

        if ($entity->get('stock') < $original['stock'] || $checkStock) {
            if (
                !$this->checkStockBetweenDate(
                    $entity,
                    $entity->get('usage_timestamp_from'),
                    $entity->get('usage_timestamp_to'),
                    true
                )
            ) {
                $entity->setError('stock', (string)__(Message::ERROR_CHANGE_OPTION_STOCK));
                $success = false;
            }
        }

        return $success;
    }

    /**
     * 個別設定の日付なが期間内もしくは重複していないかチェック
     *
     * @param \Cake\ORM\Entity $entity エンティティ
     * @return bool
     */
    protected function checkSpecificDate(Entity $entity)
    {
        $optionStockSettings = $entity->get('option_stock_settings');
        $checkDates = [];
        $success = true;
        foreach ($optionStockSettings as $index => $optionStockSetting) {
            $dateFrom = $optionStockSetting->get('usage_timestamp_from');
            $dateTo = $optionStockSetting->get('usage_timestamp_to');

            if (
                !DateTimeUtility::isWithinDate(
                    $dateFrom,
                    $dateTo,
                    $entity->get('usage_timestamp_from'),
                    $entity->get('usage_timestamp_to')
                )
            ) {
                $success = false;
                $optionStockSetting->setError('usage_timestamp_to', (string)__(Message::ERROR_OPTION_INCLUDE_DATE));
                continue;
            }
            foreach ($checkDates as $checkDate) {
                if (DateTimeUtility::isWithinDate($dateFrom, $dateTo, $checkDate['from'], $checkDate['to'])) {
                    $success = false;
                    $optionStockSetting->setError('usage_timestamp_to', (string)__(Message::ERROR_DUPLICATE_DATE));
                    break;
                }
            }
            $checkDates[$index] = ['from' => $dateFrom, 'to' => $dateTo];
        }

        return $success;
    }

    /**
     * 在庫チェック
     *
     * @param \Cake\ORM\Entity|\Cake\Datasource\EntityInterface $option オプション
     * @param string|\DateTimeInterface|null $usageTimestampFrom 開始日時
     * @param string|\DateTimeInterface|null $usageTimestampTo 終了日時
     * @param bool $excludeStockSettings 個別設定の除外
     * @return bool
     */
    public function checkStockBetweenDate(
        $option,
        $usageTimestampFrom,
        $usageTimestampTo,
        bool $excludeStockSettings = false
    ) {
        if (!$option instanceof Option) {
            throw new CakeException();
        }

        if ($excludeStockSettings && ((string)$option->get('stock')) === '') {
            return true;
        }

        $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($usageTimestampFrom);
        if (!isset($usageTimestampFrom)) {
            $usageTimestampFrom = new FrozenTime($this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'));
        }

        // 基本在庫のみチェックする場合
        $excludeDateTime = [];
        if ($excludeStockSettings) {
            foreach ((array)$option->get('option_stock_settings') as $optionStockSetting) {
                if (
                    $optionStockSetting->isDirty('usage_timestamp_from')
                    || $optionStockSetting->isDirty('usage_timestamp_to')
                    || $optionStockSetting->isDirty('stock')
                ) {
                    $excludeDateTime[] = [
                        'from' => DateTimeUtility::convertToDateTimeObject(
                            $optionStockSetting->get('usage_timestamp_from')
                        ),
                        'to' => DateTimeUtility::convertToDateTimeObject(
                            $optionStockSetting->get('usage_timestamp_to')
                        ),
                    ];
                }
            }
        }

        if (!$option->checkRemainStock($usageTimestampFrom, $usageTimestampTo, null, null, $excludeDateTime)) {
            return false;
        }

        return true;
    }

    /**
     * 編集時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $query->select([
            'id',
            'name',
            'usage_timestamp_from',
            'usage_timestamp_to',
            'stock',
            'stock_unit',
            'charge',
            'public_flg',
            'description',
            'option_unit_time',
        ])->contain([
            'OptionStockSettings' => [
                'fields' => [
                    'id',
                    'option_id',
                    'usage_timestamp_from',
                    'usage_timestamp_to',
                    'stock',
                ],
            ],
        ]);

        return $query;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->like('name', [
                'before' => true,
                'after' => true,
            ])
            ->callback('date', [
                'callback' => function (Query $query, $args) {
                    $from = $this->driverExpression()->dateFormat('usage_timestamp_from', 'ymd');
                    $to = $this->driverExpression()->dateFormat('usage_timestamp_to', 'ymd');

                    $query->where(['OR' => [function (QueryExpression $exp) use ($from, $args) {
                        $exp->lte($from, $args['date']);

                        return $exp;
                    }, 'usage_timestamp_from IS NULL']]);

                    $query->where(['OR' => [function (QueryExpression $exp) use ($to, $args) {
                        $exp->gte($to, $args['date']);

                        return $exp;
                    }, 'usage_timestamp_to IS NULL']]);
                }]);
    }

    /**
     * 一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options)
    {
        $query->select([
            'id',
            'name',
            'usage_timestamp_from',
            'usage_timestamp_to',
            'stock',
            'charge',
            'public_flg',
            'description',
        ])->contain([
            'OptionStockSettings' =>
                [
                    'fields' => ['id', 'option_id', 'usage_timestamp_from', 'usage_timestamp_to', 'stock'],
                ],
            'FormItemOptions' => function (Query $q) {
                $q->select([
                    'option_id',
                    'count' => $q->func()->count('option_id'),
                ])
                    ->group(['option_id'])
                    ->order('option_id', true);

                return $q;
            },
            'ReservationOptions' => function (Query $q) {
                $q->select([
                    'option_id',
                    'count' => $q->func()->count('option_id'),
                ])
                    ->group(['option_id'])
                    ->order('option_id', true);

                return $q;
            },
        ]);

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));

        $query
            ->order([
                    'Options.' . $sort => $direction,
                ] + [
                    'Options.id' => $direction,
                ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * 削除のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDelete(Query $query, array $options)
    {
        $query->select(['id'])->contain([
            'FormItemOptions' => function (Query $q) {
                $q->select([
                    'option_id',
                    'count' => $q->func()->count('option_id'),
                ])
                    ->group(['option_id'])
                    ->order('option_id', true);

                return $q;
            },
            'ReservationOptions' => function (Query $q) {
                $q->select([
                    'option_id',
                    'count' => $q->func()->count('option_id'),
                ])
                    ->group(['option_id'])
                    ->order('option_id', true);

                return $q;
            },
        ]);

        return $query;
    }

    /**
     * フォーム項目作成時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCreateFormItem(Query $query, array $options)
    {
        $query->order([
            'Options.id' => 'ASC',
        ], true);

        return $this->callFinder('list', $query, [
            'valueField' => 'name',
        ]);
    }

    /**
     * 料金計算のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCharge(Query $query, array $options)
    {
        $query->order([
            'Options.id' => 'ASC',
        ], true);

        $id = Hash::get($options, 'inputs.id');
        if (is_scalar($id) && ((string)$id !== '') || is_array($id) && !empty($id)) {
            $query->where(['Options.id IN' => $id]);
        }

        return $query;
    }

    /**
     * 在庫チェック時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCalculateStock(Query $query, array $options)
    {
        $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject(
            Hash::get($options, 'inputs.usage_timestamp_from')
        );
        $usageTimestampTo = DateTimeUtility::convertToDateTimeObject(
            Hash::get($options, 'inputs.usage_timestamp_to')
        );

        $query->select([
            'id',
            'usage_timestamp_from',
            'usage_timestamp_to',
            'stock',
        ]);

        $query->contain([
            'OptionStockSettings' => [
                'fields' => [
                    'id',
                    'option_id',
                    'usage_timestamp_from',
                    'usage_timestamp_to',
                    'stock',
                ],
                'queryBuilder' => function ($stockSettingsQuery) use ($usageTimestampFrom, $usageTimestampTo) {
                    if (isset($usageTimestampFrom)) {
                        $stockSettingsQuery->where([
                            'OptionStockSettings.usage_timestamp_to >' => $usageTimestampFrom,
                        ]);
                    }
                    if (isset($usageTimestampTo)) {
                        $stockSettingsQuery->where([
                            'OptionStockSettings.usage_timestamp_from <' => $usageTimestampTo,
                        ]);
                    }

                    return $stockSettingsQuery;
                },
            ],
        ]);

        $id = Hash::get($options, 'inputs.id');
        if (is_scalar($id) && ((string)$id !== '') || is_array($id) && !empty($id)) {
            $query->where(['Options.id IN' => $id]);
        }

        $query->order([
            'Options.id' => 'ASC',
        ]);

        return $query;
    }

    /**
     * ロック取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReservationLock(Query $query, array $options)
    {
        $query->select(['id']);

        $optionIds = [];
        foreach (Hash::get($options, 'reservationOptions') as $reservationOption) {
            $optionIds[$reservationOption->get('option_id')] = $reservationOption->get('option_id');
        }
        $query->where([
            'Options.id IN' => $optionIds,
        ]);

        $query->epilog('FOR UPDATE');

        $query->enableHydration(false);
        $query->disableBufferedResults();

        return $query;
    }
}
