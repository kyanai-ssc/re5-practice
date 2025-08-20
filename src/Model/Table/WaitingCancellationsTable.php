<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Event;
use App\Model\Entity\Reservation;
use App\Model\Entity\ReservationStatus;
use App\Model\Entity\WaitingCancellation;
use App\Model\EventCalendar\EventTimetable;
use App\Model\EventCalendar\EventUnit;
use App\Utility\DateTimeUtility;
use App\Validation\MailValidation;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenTime;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Query;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;
use Throwable;

/**
 * WaitingCancellations Model
 *
 * @method \App\Model\Entity\WaitingCancellation newEmptyEntity()
 * @method \App\Model\Entity\WaitingCancellation newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\WaitingCancellation[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WaitingCancellation get($primaryKey, $options = [])
 * @method \App\Model\Entity\WaitingCancellation findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\WaitingCancellation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\WaitingCancellation[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\WaitingCancellation|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WaitingCancellation saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WaitingCancellation[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\WaitingCancellation[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\WaitingCancellation[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\WaitingCancellation[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class WaitingCancellationsTable extends AppTable
{
    use MailerAwareTrait;

    public const NOTIFY_LOCK_CODE = 0;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('AutoReplyMailHistories', [
            'foreignKey' => 'waiting_cancellation_id',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        if (!$this->commonData()->existsUserLoginData()) {
            $validator
                ->requirePresence('mail', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('mail', __(Message::ERROR_NOT_EMPTY), false)
                ->add('mail', MailValidation::getMailValidator());
        }

        return $validator;
    }

    /**
     * パラメータのバリデーション
     *
     * @param \Cake\Validation\Validator $validator validator
     * @return \Cake\Validation\Validator
     */
    public function validationParameter(Validator $validator)
    {
        $validator
            ->requirePresence('event_id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('event_id', __(Message::ERROR_NOT_EMPTY), false)
            ->add('event_id', [
                'primaryKey' => [
                    'rule' => function ($value) {
                        /** @var \App\Model\Table\EventsTable $eventsTable */
                        $eventsTable = $this->getTableLocator()->get('Events');

                        if (!$eventsTable->validatePrimaryKey($value)) {
                            return false;
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_EXISTS),
                ],
            ]);

        $validator
            ->requirePresence('usage_timestamp', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_timestamp', __(Message::ERROR_NOT_EMPTY), false)
            ->add('usage_timestamp', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => ['dateTime', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
            ]);

        return $validator;
    }

    /**
     * キャンセル待ちデータ公開側一覧
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options)
    {
        $query->select([
            'id',
            'event_id',
            'usage_timestamp',
        ])->contain(['Events' => [
            'fields' => ['id', 'name', 'sort_no'],
        ]]);

        if (Hash::get($options, 'userId') !== null) {
            $query->where(['user_id' => Hash::get($options, 'userId')]);
        } else {
            $query->where(['mail' => Hash::get($options, 'mail', '')]);
        }

        $query
            ->order([
                'WaitingCancellations.usage_timestamp' => 'ASC',
                'Events.sort_no' => 'ASC',
                'Events.id' => 'ASC',
            ], true);

        return $query;
    }

    /**
     * キャンセル待ちデータ解除用
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findRelease(Query $query, array $options)
    {
        $query->select([
            'id',
            'event_id',
            'usage_timestamp',
        ])->where([
            'id' => Hash::get($options, 'id'),
        ]);

        if (Hash::get($options, 'userId') !== null) {
            $query->where(['user_id' => Hash::get($options, 'userId')]);
        } else {
            $query->where(['mail' => Hash::get($options, 'mail', '')]);
        }

        return $query;
    }

    /**
     * キャンセル通知時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findNotify(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_id',
            'event_id',
            'usage_timestamp',
            'mail',
            'notify_flg',
        ]);
        $query->contain([
            'Events' => [
                'fields' => [
                    'id',
                    'label_id',
                    'name',
                    'type',
                    'date_from',
                    'date_to',
                    'time_from',
                    'time_to',
                    'event_unit_time',
                    'interval_time',
                    'interval_day',
                    'stock',
                    'stock_range_from',
                    'registration_deadline_type',
                    'registration_deadline_number',
                    'registration_deadline_time',
                    'registration_deadline_criterion',
                ],
            ],
            'Events.EventStockSettings' => [
                'fields' => [
                    'id',
                    'event_id',
                    'date_from',
                    'date_to',
                    'time_from',
                    'time_to',
                    'stock',
                ],
            ],
            'Events.EventStockSettings.EventStockSettingWeeks' => [
                'fields' => [
                    'id',
                    'event_stock_setting_id',
                    'week',
                ],
            ],
            'Events.EventStockSettings.EventStockSettingExcludeDates' => [
                'fields' => [
                    'id',
                    'event_stock_setting_id',
                    'date',
                ],
            ],
            'Events.EventHolidays' => [
                'fields' => [
                    'id',
                    'event_id',
                    'date_from',
                    'date_to',
                    'time_from',
                    'time_to',
                ],
            ],
            'Events.EventHolidays.EventHolidayWeeks' => [
                'fields' => [
                    'id',
                    'event_holiday_id',
                    'week',
                ],
            ],
            'Events.EventHolidays.EventHolidayExcludeDates' => [
                'fields' => [
                    'id',
                    'event_holiday_id',
                    'date',
                ],
            ],
            'Users' => [
                'fields' => [
                    'id',
                    'user_authority_id',
                    'mail',
                ],
            ],
        ]);

        $query->where([
            'WaitingCancellations.notify_flg' => WaitingCancellation::NOTIFY_FLG_ON,
        ]);

        return $query;
    }

    /**
     * パラメータのデータ取得
     *
     * @param array $parameters パラメータ
     * @return array
     */
    public function getParametersData(array $parameters)
    {
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');

        $returnErrors = function ($errors) {
            return ['errors' => $errors];
        };

        $validator = $this->validationParameter(new KuchenValidator());
        $errors = $validator->validate($parameters);
        if (!empty($errors)) {
            return call_user_func($returnErrors, $errors);
        }

        $event = null;
        try {
            /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
            $event = $eventsTable->get($parameters['event_id'], [
                'finder' => 'waitingCancellation',
            ]);
        } catch (RecordNotFoundException $e) {
            return call_user_func($returnErrors, [
                'event_id' => __(Message::ERROR_INVALID_VALUE),
            ]);
        }
        if (!$event->isPublic()) {
            return call_user_func($returnErrors, [
                'event_id' => __(Message::ERROR_INVALID_VALUE),
            ]);
        }

        $usageTimestamp = DateTimeUtility::convertToDateTimeObject($parameters['usage_timestamp']);
        if (!isset($usageTimestamp)) {
            throw new CakeException();
        }
        if (!$event->existsUsageTimestampFrom($usageTimestamp)) {
            return call_user_func($returnErrors, [
                'usage_timestamp' => __(Message::ERROR_INVALID_VALUE),
            ]);
        }

        $eventTimetable = new EventTimetable($event, $usageTimestamp, $event->getUsageTimestampTo($usageTimestamp));
        $eventTimetable->createReservedTimetable(null, null, true);
        $eventTimetable->applyIntervalStock();
        if (!$eventTimetable->isOutOfStock()) {
            return call_user_func($returnErrors, [
                'usage_timestamp' => __(Message::ERROR_WAITING_CANCELLATION_IN_STOCK),
            ]);
        }

        return [
            'parameters' => [
                'event_id' => $event->get('id'),
                'usage_timestamp' => $usageTimestamp->format('Y/m/d H:i'),
            ],
            'event' => $event,
        ];
    }

    /**
     * 通知フラグを更新
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return void
     */
    public function updateNotifyFlg(Reservation $reservation)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        // 編集時のみ
        if ($reservation->isNew()) {
            return;
        }

        // 元のデータが在庫確保する場合のみ
        $originalKeepStockFlg = $reservationStatusesTable->getKeepStockFlg(
            $reservation->getOriginal('reservation_status_id')
        );
        if (((string)$originalKeepStockFlg) !== ((string)ReservationStatus::KEEP_STOCK_FLG_ON)) {
            return;
        }

        // キャンセル待ち利用の場合のみ
        $originalEventTimetable = $reservation->getOriginalTimetable();
        $event = $originalEventTimetable->getEvent();
        if ((string)$event->get('waiting_cancellation_flg') !== ((string)Event::COMMON_FLG_ON)) {
            return;
        }

        // 未来の場合のみ
        $dateTimeFrom = $event->subIntervalTime($originalEventTimetable->getDateTimeFrom());
        $dateTimeTo = $event->addIntervalTime($originalEventTimetable->getDateTimeTo());
        if ($dateTimeTo < $this->commonData()->getNowDateTime()) {
            return;
        }

        // 元の在庫を計算
        $eventTimetable = new EventTimetable($event, $dateTimeFrom, $dateTimeTo);
        $eventTimetable->getTimetable();
        $eventTimetable->createReservedTimetable($reservation->get('id'), null, true);
        $eventTimetable->applyReservation(
            $reservation->getOriginal('usage_timestamp_from'),
            $reservation->getOriginal('usage_timestamp_to'),
            (int)$reservation->getOriginal('number')
        );

        // 元の在庫で予約不可の枠に限定する
        $targetUnit = [];
        foreach ($eventTimetable->getTimetable() as $eventUnit) {
            if ($eventUnit->getDateTimeFrom() >= $this->commonData()->getNowDateTime()) {
                $unitTimestamp = $event->getUnitTimeUsageTimestamp($eventUnit->getDateTimeFrom());
                if (
                    isset($unitTimestamp)
                    && !$eventTimetable->canReserve($eventUnit->getDateTimeFrom(), $unitTimestamp)
                ) {
                    $targetUnit[] = $eventUnit;
                }
            }
        }

        // 変更後の在庫を計算
        $eventTimetable->applyReservation(
            $reservation->getOriginal('usage_timestamp_from'),
            $reservation->getOriginal('usage_timestamp_to'),
            (int)(-1 * $reservation->getOriginal('number'))
        );
        $keepStockFlg = $reservationStatusesTable->getKeepStockFlg($reservation->get('reservation_status_id'));
        if (((string)$keepStockFlg) === ((string)ReservationStatus::KEEP_STOCK_FLG_ON)) {
            $eventTimetable->applyReservation(
                $reservation->get('usage_timestamp_from'),
                $reservation->get('usage_timestamp_to'),
                (int)$reservation->get('number')
            );
        }

        // 変更後の在庫で予約可能な枠が対象
        foreach ($targetUnit as $index => $eventUnit) {
            $unitTimestamp = $event->getUnitTimeUsageTimestamp($eventUnit->getDateTimeFrom());
            if (isset($unitTimestamp) && !$eventTimetable->canReserve($eventUnit->getDateTimeFrom(), $unitTimestamp)) {
                unset($targetUnit[$index]);
            }
        }

        foreach ($targetUnit as $eventUnit) {
            $this->updateAll(
                [
                    'notify_flg' => WaitingCancellation::NOTIFY_FLG_ON,
                    'modified' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
                ],
                [
                    'WaitingCancellations.event_id' => $eventUnit->getEvent()->get('id'),
                    'WaitingCancellations.usage_timestamp' => $eventUnit->getDateTimeFrom()->format('Y-m-d H:i:s'),
                ]
            );
        }
    }

    /**
     * キャンセルを通知
     *
     * @return void
     */
    public function notifyCancellation()
    {
        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');

        $this->getLockForNotify();

        $list = $this->find('notify');

        $allCount = 0;
        $errorCount = 0;
        foreach ($list as $waitingCancellation) {
            try {
                $autoReplyMailHistory = null;

                $eventUnit = $waitingCancellation->get('event')->createEventUnit(
                    $waitingCancellation->get('usage_timestamp')
                );

                if (
                    $eventUnit->isWithinRegistrationDeadline()
                    && $this->isExistStock($waitingCancellation, $eventUnit)
                ) {
                    $autoReplyMailHistory = $autoReplyMailHistoriesTable->generateDataForWaitingCancellation(
                        $waitingCancellation
                    );
                }

                if (isset($autoReplyMailHistory)) {
                    $result = $this->getConnection()->transactional(function () use (
                        $waitingCancellation,
                        $autoReplyMailHistory
                    ) {
                        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
                        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');

                        $waitingCancellation->set('notify_flg', WaitingCancellation::NOTIFY_FLG_OFF);
                        if (!$this->save($waitingCancellation)) {
                            return false;
                        }

                        $autoReplyMailHistoriesTable->sendAutoReplyMail($autoReplyMailHistory, false, [
                            'event_name' => $waitingCancellation->get('event')->get('name'),
                            'usage_timestamp' => $waitingCancellation->get('usage_timestamp'),
                        ]);

                        return true;
                    });

                    if (!$result) {
                        throw new CakeException();
                    }
                }
            } catch (Throwable $e) {
                try {
                    $this->getErrorLogger()->log($e, Router::getRequest());
                } catch (Throwable $e2) {
                    // DO NOTHING
                }
                $errorCount += 1;
            }
            $allCount += 1;
        }

        $this->releaseLockForNotify();

        // エラーメール送信
        if ($errorCount > 0) {
            /** @var \App\Mailer\ErrorMailer $errorMailer */
            $errorMailer = $this->getMailer('Error');

            $errorMailer->sendNotifyCancellationErrorMail($errorCount, $allCount);
        }
    }

    /**
     * 過去のデータを削除
     *
     * @return void
     */
    public function deleteOldData()
    {
        $dateTime = new FrozenTime($this->commonData()->getNowDateTime()->format('Y-m-d'));
        $this->deleteAll([
            'usage_timestamp <' => $dateTime->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * キャンセル通知のロックを取得
     *
     * @return void
     */
    protected function getLockForNotify()
    {
        $this->getLock(static::LOCK_TYPE_WAITING_CANCELLATION, static::NOTIFY_LOCK_CODE);
    }

    /**
     * キャンセル通知のロックを解放
     *
     * @return void
     */
    protected function releaseLockForNotify()
    {
        $this->releaseLock(static::LOCK_TYPE_WAITING_CANCELLATION, static::NOTIFY_LOCK_CODE);
    }

    /**
     * 在庫が存在するか判定
     *
     * @param \App\Model\Entity\WaitingCancellation $waitingCancellation キャンセル待ち
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @return bool
     */
    public function isExistStock(WaitingCancellation $waitingCancellation, EventUnit $eventUnit)
    {
        $event = $eventUnit->getEvent();

        // 休日の判定
        if (!$eventUnit->isHoliday()) {
            $waitingCancellationData = [
                'usageTimestampFrom' => $waitingCancellation->get('usage_timestamp'),
                'usageTimestampTo' => $event->getUsageTimestampTo($waitingCancellation->get('usage_timestamp')),
                'number' => $event->get('stock_range_from'),
            ];

            // インターバル時間を含めて在庫チェック
            return $event->checkRemainStock(
                $event->subIntervalTime($waitingCancellation->get('usage_timestamp')),
                $event->addIntervalTime($event->getUsageTimestampTo($waitingCancellation->get('usage_timestamp'))),
                null,
                null,
                null,
                $waitingCancellationData
            );
        }

        return false;
    }
}
