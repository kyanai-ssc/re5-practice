<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\Event;
use App\Model\Entity\ReservationStatus;
use Cake\Database\Schema\TableSchema;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * ReservationOptions Model
 *
 * @method \App\Model\Entity\ReservationOption newEmptyEntity()
 * @method \App\Model\Entity\ReservationOption newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationOption[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationOption get($primaryKey, $options = [])
 * @method \App\Model\Entity\ReservationOption findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ReservationOption patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationOption[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationOption|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationOption saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationOption[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationOption[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationOption[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationOption[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ReservationOptionsTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Reservations', [
            'foreignKey' => 'reservation_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Options', [
            'foreignKey' => 'option_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('FormItems', [
            'foreignKey' => 'form_item_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * 予約存在チェックのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCheckReservation(Query $query, array $options)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $query = $this->selectQuery();

        $query->join([
            'Reservations' => [
                'table' => 'reservations',
                'type' => 'INNER',
                'conditions' => [
                    'ReservationOptions.reservation_id = Reservations.id',
                ],
            ],
            'Events' => [
                'table' => 'events',
                'type' => 'INNER',
                'conditions' => [
                    'Reservations.event_id = Events.id',
                ],
            ],
        ]);

        $optionId = Hash::get($options, 'inputs.option_id');
        if (!is_null($optionId)) {
            $query->where([
                'ReservationOptions.option_id' => $optionId,
            ]);
        }

        $formItemId = Hash::get($options, 'inputs.form_item_id');
        if (!is_null($formItemId)) {
            $query->where([
                'ReservationOptions.form_item_id' => $formItemId,
            ]);
        }

        $status = $reservationStatusesTable->getGroupingStatusType([
            ReservationStatus::STATUS_TYPE_FIXED,
            ReservationStatus::STATUS_TYPE_TENTATIVE,
            ReservationStatus::STATUS_TYPE_VISIT,
        ], true, false);
        $query->where([
            'Reservations.reservation_status_id IN' => array_keys($status),
        ]);

        $dateTimeFrom = Hash::get($options, 'inputs.date_time_from');
        $dateTimeTo = Hash::get($options, 'inputs.date_time_to');
        if (!is_null($dateTimeFrom)) {
            $sqlFrom = $this->driverExpression()->cast('Reservations.usage_timestamp_from', TableSchema::TYPE_DATE);
            $sqlTo = $this->driverExpression()->cast('Reservations.usage_timestamp_to', TableSchema::TYPE_DATE);
            $query->where([
                'OR' => [
                    [
                        'Events.type' => Event::TYPE_TIME,
                        [
                            'OR' => [
                                function ($queryExpression) use ($sqlFrom, $dateTimeFrom) {
                                    return $queryExpression->lt($sqlFrom, $dateTimeFrom);
                                },
                                function ($queryExpression) use ($sqlFrom, $dateTimeTo) {
                                    return $queryExpression->gt($sqlFrom, $dateTimeTo);
                                },
                            ],
                        ],
                    ],
                    [
                        'Events.type' => Event::TYPE_DAY,
                        [
                            'OR' => [
                                function ($queryExpression) use ($sqlTo, $dateTimeFrom) {
                                    return $queryExpression->lt($sqlTo, $dateTimeFrom);
                                },
                                function ($queryExpression) use ($sqlTo, $dateTimeTo) {
                                    return $queryExpression->gt($sqlTo, $dateTimeTo);
                                },
                            ],
                        ],
                    ],
                ],
            ]);
        }

        return $query;
    }

    /**
     * 予約日時の最小値と最大値を取得するファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findMinMaxDateTime(Query $query, array $options)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $query->select([
            'option_id' => 'ReservationOptions.option_id',
            'usage_timestamp_from' => $query->func()->min('Reservations.usage_timestamp_from', ['datetime']),
            'usage_timestamp_to' => $query->func()->max('Reservations.usage_timestamp_to', ['datetime']),
        ]);

        $query->contain([
            'Reservations' => [
                'fields' => [],
            ],
        ]);

        // オプションID
        $optionId = Hash::get($options, 'inputs.option_id');
        $query->where([
            'ReservationOptions.option_id IN' => (array)$optionId,
        ]);

        // 利用日時
        $usageTimestampFrom = Hash::get($options, 'inputs.usage_timestamp_from');
        if (isset($usageTimestampFrom) && $usageTimestampFrom !== '') {
            $query->where([
                'Reservations.usage_timestamp_to >' => $usageTimestampFrom,
            ]);
        }
        $usageTimestampTo = Hash::get($options, 'inputs.usage_timestamp_to');
        if (isset($usageTimestampTo) && $usageTimestampTo !== '') {
            $query->where([
                'Reservations.usage_timestamp_from <' => $usageTimestampTo,
            ]);
        }

        // 在庫を確保するステータス
        $reservationStatusId = [];
        foreach ($reservationStatusesTable->getKeepStockData() as $data) {
            $reservationStatusId[] = $data['id'];
        }
        $query->where([
            'Reservations.reservation_status_id IN' => $reservationStatusId,
        ]);

        $query->group([
            'ReservationOptions.option_id',
        ]);

        $query->enableHydration(false);
        $query->disableBufferedResults();

        $query->formatResults(function ($results) {
            $result = $results->map(function ($data) {
                return [
                    'min' => new FrozenTime($data['usage_timestamp_from']),
                    'max' => new FrozenTime($data['usage_timestamp_to']),
                ];
            });

            return $result;
        });

        return $query;
    }

    /**
     * 在庫計算時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCalculateStock(Query $query, array $options)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $query->select([
            'option_id' => 'ReservationOptions.option_id',
            'usage_timestamp_from' => 'Reservations.usage_timestamp_from',
            'usage_timestamp_to' => 'Reservations.usage_timestamp_to',
            'number' => $query->func()->sum('ReservationOptions.number'),
        ]);

        $query->contain([
            'Reservations' => [
                'fields' => [],
            ],
        ]);

        $optionId = Hash::get($options, 'inputs.option_id');
        $usageTimestampFrom = Hash::get($options, 'inputs.usage_timestamp_from');
        $usageTimestampTo = Hash::get($options, 'inputs.usage_timestamp_to');
        $reservationStatusId = [];
        foreach ($reservationStatusesTable->getKeepStockData() as $data) {
            $reservationStatusId[] = $data['id'];
        }

        $query->where([
            'ReservationOptions.option_id IN' => (array)$optionId,
            'Reservations.usage_timestamp_from <' => $usageTimestampTo,
            'Reservations.usage_timestamp_to >' => $usageTimestampFrom,
            'Reservations.reservation_status_id IN' => $reservationStatusId,
        ]);

        $excludeId = Hash::get($options, 'inputs.exclude_reservation_id');
        if (is_scalar($excludeId) && ((string)$excludeId !== '') || is_array($excludeId) && !empty($excludeId)) {
            $query->where([
                'Reservations.id NOT IN' => (array)$excludeId,
            ]);
        }

        $query->group([
            'ReservationOptions.option_id',
            'Reservations.usage_timestamp_from',
            'Reservations.usage_timestamp_to',
        ]);

        $query->order([
            'ReservationOptions.option_id',
            'Reservations.usage_timestamp_from',
            'Reservations.usage_timestamp_to',
        ]);

        $query->enableHydration(false);
        $query->disableBufferedResults();

        return $query;
    }

    /**
     * 予約済みデータ取得用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReserved(Query $query, array $options)
    {
        $query->select([
            'id',
            'reservation_id',
            'option_id',
            'form_item_id',
            'number',
        ]);
        $query->where([
            'ReservationOptions.reservation_id' => Hash::get($options, 'inputs.reservation_id'),
        ]);
        $query->order([
            'ReservationOptions.option_id',
            'ReservationOptions.id',
        ]);

        return $query;
    }

    /**
     * フォーム項目オプション.表示順で取得するファインダー
     *
     * デフォルトは昇順
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findWithSortNo(Query $query, array $options = [])
    {
        return $query
            ->select([
                'id',
                'reservation_id',
                'option_id',
                'form_item_id',
                'number',
            ])
            ->join([
                'FormItems' => [
                    'table' => 'form_items',
                    'type' => 'INNER',
                    'conditions' => [
                        'ReservationOptions.form_item_id = FormItems.id',
                    ],
                ],
                'FormItemOptionGroups' => [
                    'table' => 'form_item_option_groups',
                    'type' => 'INNER',
                    'conditions' => [
                        'FormItemOptionGroups.form_item_id = FormItems.id',
                    ],
                ],
                'FormItemOptions' => [
                    'table' => 'form_item_options',
                    'type' => 'INNER',
                    'conditions' => [
                        'ReservationOptions.option_id = FormItemOptions.option_id',
                        'FormItemOptionGroups.id = FormItemOptions.form_item_option_group_id',
                    ],
                ],
            ])
            ->order([
                'FormItemOptions.sort_no' => Hash::get($options, 'sort_order', 'ASC'),
                'ReservationOptions.id' => 'ASC',
            ], true);
    }

    /**
     * 期間内にオプション予約が存在するかどうか
     *
     * @param int $optionId オプションID
     * @param string|\DateTimeInterface $from From
     * @param string|\DateTimeInterface $to To
     * @return bool
     */
    public function checkReserveOptionsInDate($optionId, $from, $to)
    {
        $query = $this->find('checkReservation', [
            'inputs' => [
                'option_id' => $optionId,
                'date_time_from' => $from,
                'date_time_to' => $to,
            ],
        ]);
        if ($query->count() < 1) {
            return false;
        }

        return true;
    }

    /**
     * 該当formItemIdにオプション予約が存在するかどうか
     *
     * @param int $formItemId 項目ID
     * @return bool
     */
    public function checkReserveFormItemId(int $formItemId)
    {
        $query = $this->find('checkReservation', [
            'inputs' => [
                'form_item_id' => $formItemId,
            ],
        ]);

        if ($query->count() < 1) {
            return false;
        }

        return true;
    }

    /**
     * フォーム種別を指定して削除を行う
     *
     * @param int $formType フォーム種別
     * @param array $excludeFormItemId 除外するフォーム項目ID
     * @return void
     */
    public function deleteByFormType(int $formType, ?array $excludeFormItemId = null)
    {
        $formItemsQuery = $this->getAssociation('FormItems')->find();
        $formItemsQuery->select(['FormItems.id']);
        $formItemsQuery->matching('FormGroups', function ($formGroupsQuery) use ($formType) {
            $formGroupsQuery->where(['FormGroups.form_type' => $formType]);

            return $formGroupsQuery;
        });

        $where = [
            'ReservationOptions.form_item_id IN' => $formItemsQuery,
        ];
        if (!empty($excludeFormItemId)) {
            $where['ReservationOptions.form_item_id NOT IN'] = (array)$excludeFormItemId;
        }
        $this->deleteAll($where);
    }
}
