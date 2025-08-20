<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\ColorChip;
use App\Model\Entity\Event;
use App\Model\Entity\EventRemark;
use App\Model\Entity\FormGroup;
use App\Model\Entity\Label;
use App\Model\Entity\ReservationStatus;
use App\Model\Entity\SiteSetting;
use App\Model\Entity\SystemSetting;
use App\Model\ImportableTableInterface;
use App\Utility\ArrayUtility;
use App\Utility\DateTimeUtility;
use App\Utility\SmartLock\SmartLockLinkage;
use App\Validation\CustomValidation;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * Events Model
 *
 * @method \App\Model\Entity\Event newEmptyEntity()
 * @method \App\Model\Entity\Event newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Event[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Event get($primaryKey, $options = [])
 * @method \App\Model\Entity\Event findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Event patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Event[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Event|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Event saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventsTable extends AppTable implements ImportableTableInterface
{
    public const NAME_MAX = 320;
    public const SORT_NO_MAX = 10000000000;
    public const UNIT_TIME_MIN = 5;
    public const UNIT_TIME_MAX = 1440;
    public const UNIT_DAY_MAX = 1000;
    public const UNIT_DAY_MIN = 1;
    public const USAGE_TIME_MAX = 100000;
    public const INTERVAL_TIME_MAX = 100000;
    public const INTERVAL_DAY_MAX = 100;
    public const CHARGE_MAX = 10000000;
    public const STOCK_MAX = 10000000;
    public const STOCK_UNIT_MAX = 100;
    public const RECEPTION_PERIOD_MAX = 10000;

    public const DEAD_LINE_MAX = 10000;
    public const DEAD_LINE_TIME_MAX_USE_PAYMENT = 1440;
    public const DEAD_LINE_DAY_MAX_USE_PAYMENT = 360;
    public const RESERVATION_LIMIT_MAX = 1000000;
    public const DESCRIPTION_MAX = 10000;

    public const USAGE_DAY_FOR_TIME = 1;
    public const INTERVAL_DAY_FOR_TIME = null;
    public const INTERVAL_TIME_FOR_DAY = null;

    /**
     * エクスポートカラム
     */
    public const CSV_COLUMN_ID = 'id';
    public const CSV_COLUMN_NAME = 'name';
    public const CSV_COLUMN_SORT_NO = 'sort_no';
    public const CSV_COLUMN_TYPE = 'type';
    public const CSV_COLUMN_EVENT_TAGS = 'event_tags';
    public const CSV_COLUMN_LABEL_ID = 'label_id';
    public const CSV_COLUMN_DATE_FROM = 'date_from';
    public const CSV_COLUMN_DATE_TO = 'date_to';
    public const CSV_COLUMN_TIME_FROM = 'time_from';
    public const CSV_COLUMN_TIME_TO = 'time_to';
    public const CSV_COLUMN_EVENT_WEEKS = 'event_weeks';
    public const CSV_COLUMN_EVENT_UNIT_TIME = 'event_unit_time';
    public const CSV_COLUMN_TIME_PLAN = 'time_plan';
    public const CSV_COLUMN_MULTIPLE_TIME_PLAN_TYPE = 'multiple_time_plan_type';
    public const CSV_COLUMN_EVENT_PLANS = 'event_plans';
    public const CSV_COLUMN_USAGE_UNIT_TIME = 'usage_unit_time';
    public const CSV_COLUMN_USAGE_TIME_FROM = 'usage_time_from';
    public const CSV_COLUMN_USAGE_TIME_TO = 'usage_time_to';
    public const CSV_COLUMN_USAGE_UNIT_DAY = 'usage_unit_day';
    public const CSV_COLUMN_USAGE_DAY_FROM = 'usage_day_from';
    public const CSV_COLUMN_USAGE_DAY_TO = 'usage_day_to';
    public const CSV_COLUMN_USAGE_TIME_NOTATION = 'usage_time_notation';
    public const CSV_COLUMN_INTERVAL_TIME = 'interval_time';
    public const CSV_COLUMN_INTERVAL_DAY = 'interval_day';
    public const CSV_COLUMN_CHARGE = 'charge';
    public const CSV_COLUMN_STOCK = 'stock';
    public const CSV_COLUMN_STOCK_RANGE_FROM = 'stock_range_from';
    public const CSV_COLUMN_STOCK_RANGE_TO = 'stock_range_to';
    public const CSV_COLUMN_STOCK_UNIT = 'stock_unit';
    public const CSV_COLUMN_STOCK_DISPLAY_TYPE = 'stock_display_type';
    public const CSV_COLUMN_PUBLIC_FLG = 'public_flg';
    public const CSV_COLUMN_PUBLIC_FROM = 'public_from';
    public const CSV_COLUMN_PUBLIC_TO = 'public_to';
    public const CSV_COLUMN_RECEPTION_PERIOD_NUMBER = 'reception_period_number';
    public const CSV_COLUMN_RECEPTION_PERIOD_TIME = 'reception_period_time';
    public const CSV_COLUMN_REGISTRATION_DEADLINE_TYPE = 'registration_deadline_type';
    public const CSV_COLUMN_REGISTRATION_DEADLINE_NUMBER = 'registration_deadline_number';
    public const CSV_COLUMN_REGISTRATION_DEADLINE_TIME = 'registration_deadline_time';
    public const CSV_COLUMN_EDITING_DEADLINE_TYPE = 'editing_deadline_type';
    public const CSV_COLUMN_EDITING_DEADLINE_NUMBER = 'editing_deadline_number';
    public const CSV_COLUMN_EDITING_DEADLINE_TIME = 'editing_deadline_time';
    public const CSV_COLUMN_CANCELLATION_DEADLINE_TYPE = 'cancellation_deadline_type';
    public const CSV_COLUMN_CANCELLATION_DEADLINE_NUMBER = 'cancellation_deadline_number';
    public const CSV_COLUMN_CANCELLATION_DEADLINE_TIME = 'cancellation_deadline_time';
    public const CSV_COLUMN_RESERVATION_STATUS_ID = 'reservation_status_id';
    public const CSV_COLUMN_WAITING_CANCELLATION_FLG = 'waiting_cancellation_flg';
    public const CSV_COLUMN_RESERVATION_LIMIT_ALL = 'reservation_limit_all';
    public const CSV_COLUMN_RESERVATION_LIMIT_FUTURE = 'reservation_limit_future';
    public const CSV_COLUMN_RESERVATION_LIMIT_MONTH = 'reservation_limit_month';
    public const CSV_COLUMN_RESERVATION_LIMIT_DAY = 'reservation_limit_day';
    public const CSV_COLUMN_DUPLICATION_CHECK_FLG = 'duplication_check_flg';
    public const CSV_COLUMN_BACKGROUND_COLOR_TYPE = 'background_color_type';
    public const CSV_COLUMN_COLOR_CHIP_ID = 'color_chip_id';
    public const CSV_COLUMN_BACKGROUND_COLOR_REPLACE_FRONT = 'background_color_replace_front';
    public const CSV_COLUMN_BACKGROUND_COLOR_REPLACE_ADMIN = 'background_color_replace_admin';
    public const CSV_COLUMN_FORMAT_TYPE_DISPLAY = 'format_type_display';
    public const CSV_COLUMN_FORM_PATTERN_ID = 'form_pattern_id';
    public const CSV_COLUMN_DESCRIPTION = 'description';
    public const CSV_COLUMN_EVENT_IMAGES = 'event_images';
    public const CSV_COLUMN_EVENT_REMARKS = 'event_remarks';
    public const CSV_COLUMN_EVENT_STOCK_MARKS = 'event_stock_marks';
    public const CSV_COLUMN_CREATED = 'created';
    public const CSV_COLUMN_MODIFIED = 'modified';
    public const CSV_COLUMN_ORGANIZER_ID = 'organizer_id';
    public const CSV_COLUMN_QR_CODE_FLG = 'qr_code_flg';
    public const CSV_COLUMN_REGISTRATION_DEADLINE_CRITERION = 'registration_deadline_criterion';
    public const CSV_COLUMN_EDITING_DEADLINE_CRITERION = 'editing_deadline_criterion';
    public const CSV_COLUMN_CANCELLATION_DEADLINE_CRITERION = 'cancellation_deadline_criterion';
    public const CSV_COLUMN_EVENT_SMART_LOCK = 'event_smart_lock';

    /**
     * @var array|null
     */
    protected $deadLineMax;

    /**
     * @var mixed
     */
    protected $editAbled;

    /**
     * @var int|null
     */
    protected $maxInterval;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Labels', [
            'foreignKey' => 'label_id',
        ]);
        $this->belongsTo('ReservationStatuses', [
            'foreignKey' => 'reservation_status_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ColorChips', [
            'foreignKey' => 'color_chip_id',
        ]);
        $this->belongsTo('FormPatterns', [
            'foreignKey' => 'form_pattern_id',
            'joinType' => 'INNER',
        ])->setConditions(['form_type' => FormGroup::FORM_TYPE_RESERVATION]);
        $this->belongsTo('Organizers', [
            'foreignKey' => 'organizer_id',
            'joinType' => 'LEFT',
        ]);

        $this->hasMany('EventHolidays', [
            'foreignKey' => 'event_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);
        $this->hasMany('EventImages', [
            'foreignKey' => 'event_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
            'sort' => [
                'EventImages.sort_no' => 'ASC',
                'EventImages.id' => 'ASC',
            ],
        ]);
        $this->hasMany('EventPlans', [
            'foreignKey' => 'event_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
            'sort' => [
                'EventPlans.sort_no' => 'ASC',
                'EventPlans.id' => 'ASC',
            ],
        ]);
        $this->hasMany('EventRemarks', [
            'foreignKey' => 'event_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);
        $this->hasMany('EventStockMarks', [
            'foreignKey' => 'event_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);
        $this->hasMany('EventStockSettings', [
            'foreignKey' => 'event_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);
        $this->hasMany('EventTags', [
            'foreignKey' => 'event_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);
        $this->hasMany('EventWeeks', [
            'foreignKey' => 'event_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
            'sort' => [
                'EventWeeks.week' => 'ASC',
            ],
        ]);
        $this->hasOne('EventSmartLocks', [
            'foreignKey' => 'event_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);
        $this->hasMany('Reservations', [
            'foreignKey' => 'event_id',
        ]);
        $this->hasMany('WaitingCancellations', [
            'foreignKey' => 'event_id',
        ]);

        $this->hasOne('EventSmartLocks', [
            'foreignKey' => 'event_id',
            'dependent' => true,
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema
            ->setColumnType('background_color_replace_front', 'json')
            ->setColumnType('background_color_replace_admin', 'json')
            ->setColumnType('format_type_display', 'json');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(function ($entity, $options) {
            /** @var \App\Model\Table\EventRemarksTable $eventRemarksTable */
            $eventRemarksTable = $this->getAssociation('EventRemarks')->getTarget();

            if (is_array($entity->get('event_remarks'))) {
                return $eventRemarksTable->checkDuplicationFormId($entity);
            }

            return true;
        }, 'eventRemarksDuplicated');

        $rules->addUpdate(function ($entity, $options) {
            /** @var \App\Model\Table\EventWeeksTable $eventWeeksTable */
            $eventWeeksTable = $this->getAssociation('EventWeeks')->getTarget();

            if (is_array($entity->get('event_weeks'))) {
                if ($eventWeeksTable->enableDeleteWeek($entity)) {
                    return true;
                } else {
                    $entity->setError('event_weeks', (string)__(Message::ERROR_IS_RESERVE));

                    return false;
                }
            }

            return true;
        }, 'eventWeeksDeleted');

        $rules->addUpdate(function (EntityInterface $entity, $options) {
            $success = true;

            /** @var \App\Model\Table\ReservationsTable $reservationTable */
            $reservationTable = $this->getTableLocator()->get('Reservations');
            if ($entity->isDirty('stock_range_from')) {
                $data = $reservationTable->find('reserveNum', [
                    'event_id' => $entity->get('id'),
                    'stock_range_from' => $entity->get('stock_range_from'),
                ]);

                if ($data->count() >= 1) {
                    $entity->setError('stock_range_from', (string)__(Message::ERROR_IS_RESERVE));
                    $success = false;
                }
            }

            if ($entity->isDirty('stock_range_to')) {
                $data = $reservationTable->find('reserveNum', [
                    'event_id' => $entity->get('id'),
                    'stock_range_to' => $entity->get('stock_range_to'),
                ]);

                if ($data->count() >= 1) {
                    $entity->setError('stock_range_to', (string)__(Message::ERROR_IS_RESERVE));
                    $success = false;
                }
            }

            return $success;
        }, 'stockRange');

        $rules->add(function ($entity, $options) {
            if ($entity->get('time_plan') === Event::PLAN_MULTIPLE) {
                $eventPlans = $entity->get('event_plans');
                $result = true;
                if ($entity->get('type') === Event::TYPE_TIME) {
                    foreach ($eventPlans as $eventPlan) {
                        if (
                            $eventPlan->isDirty()
                            && !CustomValidation::multipleNum(
                                $eventPlan->get('usage_time'),
                                'event_unit_time',
                                [
                                    'data' => ['event_unit_time' => $entity->get('event_unit_time')],
                                ]
                            )
                        ) {
                            $eventPlan->setError('usage_time', (string)__(Message::ERROR_MULTIPLE_NUM, '予約最小単位'));
                            $result = false;
                        }
                    }
                }

                return $result;
            } else {
                return true;
            }
        }, 'eventPlansMultipleNum');

        $rules->addUpdate(function ($entity, $options) {
            if ($entity->get('time_plan') === Event::PLAN_MULTIPLE) {
                $eventPlans = $entity->get('event_plans');
                $editAbled = $this->getEditAbled();
                if (isset($editAbled[$entity->get('id')]['plan']) && is_array($editAbled[$entity->get('id')]['plan'])) {
                    $originalEvent = $entity->getOriginalValues();
                    $originalEventPlans = array_column($originalEvent['event_plans'], 'id', 'id');
                    foreach ($editAbled[$entity->get('id')]['plan'] as $planId => $editPlans) {
                        if (
                            $editPlans['all'] >= 1
                            && ArrayUtility::arraySearch($planId, array_column($eventPlans, 'id')) === false
                            && ArrayUtility::arraySearch($planId, $originalEventPlans) !== false
                        ) {
                            $entity->setError('event_plans', (string)__(Message::ERROR_IS_RESERVE));
                        }
                    }
                }

                if ($entity->getError('event_plans')) {
                    return false;
                }

                return true;
            } else {
                return true;
            }
        }, 'eventPlansMultipleNum2');

        $rules->add(function ($entity, $options) {
            /** @var \App\Model\Table\EventStockMarksTable $eventStockMarksTable */
            $eventStockMarksTable = $this->getAssociation('EventStockMarks')->getTarget();

            if (is_array($entity->get('event_stock_marks'))) {
                return $eventStockMarksTable->checkDuplicationNumber($entity);
            }

            return true;
        }, 'eventStockmarksDuplicated');

        // 予約在庫チェック
        $rules->addUpdate(function ($entity, $options) {
            if (!Hash::get($options, 'together', false)) {
                if (!$entity->checkRemainStock($this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'))) {
                    $entity->setError('stock_error', (string)__(Message::ERROR_CHANGE_EVENT_STOCK));

                    return false;
                }
            }

            return true;
        }, 'checkStock');

        return $rules;
    }

    /**
     * Model.afterMarshalイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $data データ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterMarshal(
        EventInterface $event,
        EntityInterface $entity,
        ArrayObject $data,
        ArrayObject $options
    ) {
        if (!($entity instanceof Event)) {
            throw new CakeException();
        }

        if ($entity->hasErrors()) {
            return;
        }

        if (Hash::get($options, 'updatePublic', false) === false && $entity->get('type') === Event::TYPE_DAY) {
            $usageUnitTime = $entity->calculateUsageUnitTimeForDay();
            $entity->set('event_unit_time', $usageUnitTime);
        }
    }

    /**
     * beforeSave callback.
     *
     * @param \Cake\Event\EventInterface $event Event
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, $options)
    {
        if (!($entity instanceof Event)) {
            throw new CakeException();
        }
        $smartLock = new SmartLockLinkage();
        if ($smartLock->useRemoteLock() && !$entity->isNew() && $entity->has('event_smart_lock')) {
            $entity->get('event_smart_lock')->set('smart_lock_key_url_flg', null);
        }

        if (Hash::get($options, 'updatePublic', false) === false) {
            // 編集できない値はセット
            $originalEvent = null;
            $editAble = null;
            if (!$entity->isNew()) {
                $originalEvent = $entity->extractOriginal($entity->getVisible());
                $editAble = $this->getEditAbled();
                $entity->set('type', $originalEvent['type']);

                if (isset($editAble['all']) && $editAble['all']) {
                    $entity->set('time_plan', $originalEvent['time_plan']);
                    $entity->set('multiple_time_plan_type', $originalEvent['multiple_time_plan_type']);
                }

                if (isset($editAble['future']) && $editAble['future']) {
                    $entity->set('event_unit_time', $originalEvent['event_unit_time']);
                    $entity->set('usage_unit_time', $originalEvent['usage_unit_time']);
                }
            }

            $type = $entity->get('type');

            // アイコン閾値を0と1は固定
            if ($entity->get('stock_display_type') === Event::STOCK_DISPLAY_TYPE_ICON) {
                $eventStockMarks = $entity->get('event_stock_marks');
                $originalEventStockMarks = null;
                if (!$entity->isNew() && isset($originalEvent['event_stock_marks'])) {
                    $originalEventStockMarks = $originalEvent['event_stock_marks'];
                }

                if (empty($eventStockMarks)) {
                    throw new CakeException();
                }
                foreach ($eventStockMarks as $key => $eventStockMark) {
                    if ($eventStockMark->isDirty()) {
                        if ($key === 0) {
                            $eventStockMark->set('number', 0);
                        } elseif ($key === 1) {
                            $eventStockMark->set('number', 1);
                        }

                        if (!$entity->isNew() && !empty($originalEventStockMarks[$key])) {
                            $eventStockMark->set('id', $originalEventStockMarks[$key]['id']);
                            $eventStockMark->setNew(false);
                        }
                    }
                }
            } else {
                $entity->set('event_stock_marks', null);
            }

            // カラーコード設定
            if ($entity->get('background_color_type') !== Event::BACKGROUND_COLOR_TYPE_COLOR_CODE) {
                $entity->set('color_chip_id', null);
            }

            if (!$entity->isNew()) {
                // 未来に予約がある場合の日にちタイプは編集不可
                if (
                    isset($editAble['future'])
                    && $editAble['future']
                    && $entity->get('type') === Event::TYPE_DAY && is_array($originalEvent)
                ) {
                    $entity->set('time_from', $originalEvent['time_from']);
                    $entity->set('time_to', $originalEvent['time_to']);
                }
            }

            $usageUnitTime = null;
            if ($entity->get('type') === Event::TYPE_DAY) {
                $usageUnitTime = $entity->calculateUsageUnitTimeForDay();
                $entity->set('event_unit_time', $usageUnitTime);
            }

            if (!$entity->isNew() && isset($originalEvent['event_plans'])) {
                $originalEventPlans = array_column($originalEvent['event_plans'], 'id', 'id');
            }

            if (
                is_array($entity->get('event_plans'))
                && $entity->get('time_plan') === Event::PLAN_MULTIPLE
            ) {
                foreach ($entity->get('event_plans') as $key => $eventPlan) {
                    $eventPlan->set('sort_no', $key + 1);

                    if (
                        !$entity->isNew() && isset($originalEventPlans[$eventPlan->get('id')])
                        && (isset($editAble[$entity->get('id')]['plan'][$eventPlan['id']]['all'])
                            && $editAble[$entity->get('id')]['plan'][$eventPlan['id']]['all'])
                        && isset($originalEvent['event_plans'])
                    ) {
                        $originalPlan = null;
                        foreach ($originalEvent['event_plans'] as $plan) {
                            if ((string)$plan->get('id') === (string)$eventPlan['id']) {
                                $originalPlan = $plan;
                                break;
                            }
                        }
                        $eventPlan->set('usage_day', $originalPlan->getOriginal('usage_day'));
                        $eventPlan->set('usage_time', $originalPlan->getOriginal('usage_time'));
                    } else {
                        if ($type === Event::TYPE_TIME) {
                            $eventPlan->set('usage_day', static::USAGE_DAY_FOR_TIME);
                        } else {
                            $eventPlan->set('usage_time', $usageUnitTime);
                        }
                    }
                }
                $entity->set('charge', null);

                $entity->set('usage_day_from', null);
                $entity->set('usage_day_to', null);
                $entity->set('usage_unit_day', null);
                $entity->set('usage_unit_time', null);
                $entity->set('usage_time_from', null);
                $entity->set('usage_time_to', null);

                if ($entity->get('type') === Event::TYPE_TIME) {
                    $entity->set('interval_day', null);
                }

                if ($entity->get('type') === Event::TYPE_DAY) {
                    $entity->set('interval_time', null);
                }
            } else {
                $entity->set('event_plans', null);
                $entity->set('multiple_time_plan_type', null);

                // Timeの場合の日付登録固定値
                if ($entity->get('type') === Event::TYPE_TIME) {
                    $entity->set('usage_day_from', static::USAGE_DAY_FOR_TIME);
                    $entity->set('usage_day_to', static::USAGE_DAY_FOR_TIME);
                    $entity->set('usage_unit_day', static::USAGE_DAY_FOR_TIME);
                    $entity->set('interval_day', null);
                }

                // Dayの場合の時間登録（実施時間の差分値）
                if ($entity->get('type') === Event::TYPE_DAY) {
                    $entity->set('usage_unit_time', $usageUnitTime);
                    $entity->set('usage_time_from', $usageUnitTime);
                    $entity->set('usage_time_to', $usageUnitTime);
                    $entity->set('interval_time', null);
                }
            }

            if (!$entity->isNew() && isset($originalEvent['event_tags'])) {
                $originalEventTags = array_column($originalEvent['event_tags'], 'id', 'tag_id');
            }

            // タグのhiddenフィールドの0の値を削除
            $setEventTags = null;
            if (is_array($entity->get('event_tags'))) {
                foreach ($entity->get('event_tags') as $key => $eventTag) {
                    if ($eventTag->get('tag_id') != '0') {
                        if (isset($originalEventTags[$eventTag->get('tag_id')])) {
                            $eventTag->set('id', $originalEventTags[$eventTag->get('tag_id')]);
                            $eventTag->setNew(false);
                        }

                        $setEventTags[$key] = $eventTag;
                    }
                }
            }
            $entity->set('event_tags', $setEventTags);

            $registrationDeadlineType = $entity->get('registration_deadline_type');
            if ($registrationDeadlineType != Event::DEADLINE_TYPE_DAY) {
                $entity->set('registration_deadline_time', null);
            }

            $editingDeadlineType = $entity->get('editing_deadline_type');
            if ($editingDeadlineType != Event::DEADLINE_TYPE_DAY) {
                $entity->set('editing_deadline_time', null);
            }

            $cancellationDeadlineType = $entity->get('cancellation_deadline_type');
            if ($cancellationDeadlineType != Event::DEADLINE_TYPE_DAY) {
                $entity->set('cancellation_deadline_time', null);
            }

            $eventWeeks = $entity->get('event_weeks');
            $setEventWeeks = null;
            $originalEventWeeks = [];
            if (!$entity->isNew() && isset($originalEvent['event_weeks'])) {
                $originalEventWeeks = $originalEvent['event_weeks'];
            }

            if (!empty($eventWeeks)) {
                foreach ($eventWeeks as $key => $eventWeek) {
                    if (empty($eventWeek->get('week'))) {
                        continue;
                    }

                    if (!$entity->isNew()) {
                        $idKey = ArrayUtility::arraySearch(
                            $eventWeek['week'],
                            array_column($originalEventWeeks, 'week')
                        );

                        if ($idKey !== false) {
                            $eventWeek->set('id', $originalEventWeeks[$idKey]['id']);
                            $eventWeek->setNew(false);
                        }
                    }
                    $setEventWeeks[$key] = $eventWeek;
                }
            }

            if (!empty($setEventWeeks)) {
                $entity->set('event_weeks', $setEventWeeks);
            } else {
                $entity->set('event_weeks', null);
            }

            $eventImages = $entity->get('event_images');
            $setEventImages = null;
            $originalEventImages = [];
            if (!$entity->isNew() && isset($originalEvent['event_images'])) {
                $originalEventImages = $originalEvent['event_images'];
            }

            if (!empty($eventImages)) {
                $sort = 1;
                foreach ($eventImages as $key => $eventImage) {
                    if (!empty($originalEventImages[$key])) {
                        $eventImage->set('id', $originalEventImages[$key]['id']);
                        $eventImage->setNew(false);
                    }
                    if (Validation::notBlank($eventImage->get('url'))) {
                        $eventImage->set('sort_no', $sort);
                        $setEventImages[$key] = $eventImage;
                        $sort++;
                    }
                }
            }
            if (!empty($setEventImages)) {
                $entity->set('event_images', $setEventImages);
            } else {
                $entity->set('event_images', null);
            }

            $eventRemarks = $entity->get('event_remarks');
            if (!$entity->isNew() && !empty($eventRemarks)) {
                foreach ($eventRemarks as $key => $eventRemark) {
                    if ($eventRemark->isDirty('form_item_id')) {
                        unset($eventRemark['id']);
                        $eventRemark->setDirty('id', false);
                        $eventRemark->setNew(true);
                    }
                    foreach ($eventRemark->getVisible() as $prop) {
                        if ($eventRemark->isAccessible($prop)) {
                            $eventRemark->setDirty($prop, true);
                        }
                    }
                }
            }

            //削除される休日設定、イレギュラー設定のアソシエーション情報を削除
            if (!$entity->isNew() && !Hash::get($options, 'together', false)) {
                $eventHolidays = $entity->get('event_holidays');

                /** @var \App\Model\Table\EventHolidaysTable $eventHolidaysTable */
                $eventHolidaysTable = $this->getTableLocator()->get('EventHolidays');
                /** @var \App\Model\Table\EventHolidayWeeksTable $eventHolidayWeeksTable */
                $eventHolidayWeeksTable = $this->getTableLocator()->get('EventHolidayWeeks');
                /** @var \App\Model\Table\EventHolidayExcludeDatesTable $eventHolidayExDateTable */
                $eventHolidayExDateTable = $this->getTableLocator()->get('EventHolidayExcludeDates');

                if (!empty($eventHolidays)) {
                    $condition = [
                        [$this->excludeQueryByEntities($eventHolidays)],
                        [
                            'event_id' => $entity->get('id'),
                        ],
                    ];
                } else {
                    $condition = [
                        'event_id' => $entity->get('id'),
                    ];
                }

                $subQuery = $eventHolidaysTable->find('all', ['conditions' => $condition])->select('id');
                $eventHolidayWeeksTable->deleteAll(['event_holiday_id IN' => $subQuery]);
                $eventHolidayExDateTable->deleteAll(['event_holiday_id IN' => $subQuery]);

                $eventStockSettings = $entity->get('event_stock_settings');
                /** @var \App\Model\Table\EventStockSettingsTable $eventStockSettingsTable */
                $eventStockSettingsTable = $this->getTableLocator()->get('EventStockSettings');
                /** @var \App\Model\Table\EventStockSettingWeeksTable $eventStockSettingsTable */
                $eventStockSettingWeeksTable = $this->getTableLocator()->get('EventStockSettingWeeks');
                /** @var \App\Model\Table\EventStockSettingExcludeDatesTable $eventStockSettingExDatesTable */
                $eventStockSettingExDatesTable = $this->getTableLocator()->get('EventStockSettingExcludeDates');

                if (!empty($eventStockSettings)) {
                    $condition = [
                        [$this->excludeQueryByEntities($eventStockSettings)],
                        [
                            'event_id' => $entity->get('id'),
                        ],
                    ];
                } else {
                    $condition = [
                        'event_id' => $entity->get('id'),
                    ];
                }
                $subQuery = $eventStockSettingsTable->find('all', ['conditions' => $condition])->select('id');

                $eventStockSettingWeeksTable->deleteAll(['event_stock_setting_id IN' => $subQuery]);
                $eventStockSettingExDatesTable->deleteAll(['event_stock_setting_id IN' => $subQuery]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        /** @var \App\Model\Table\ColorChipsTable $colorChipsTable */
        $colorChipsTable = $this->getTableLocator()->get('ColorChips');
        /** @var \App\Model\Table\FormPatternsTable $formPatternsTable */
        $formPatternsTable = $this->getTableLocator()->get('FormPatterns');
        /** @var \App\Model\Table\EventRemarksTable $eventRemarksTable */
        $eventRemarksTable = $this->getAssociation('EventRemarks')->getTarget();
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');
        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->getTableLocator()->get('Organizers');

        // 予約登録時ステータス
        $statusLists = $reservationStatusesTable->getGroupingStatusType([
            ReservationStatus::STATUS_TYPE_FIXED,
            ReservationStatus::STATUS_TYPE_TENTATIVE,
        ], false);

        $statusLists = Hash::combine($statusLists, '{n}.id', '{n}.name');

        // 注釈項目を取得
        $remarkFormId = $formItemsTable->find('remarkList')->toArray();

        $fieldValueOptions = [
            'formatTypeDisplay' => Configure::readOrFail('Master.event.calendarType'),
            'type' => Configure::readOrFail('Master.event.type'),
            'publicFlg' => Configure::readOrFail('Master.event.publicFlg'),
            'plan' => Configure::readOrFail('Master.event.plan'),
            'deadlineType' => Configure::readOrFail('Master.event.deadlineType'),
            'waitingCancellationFlg' => Configure::readOrFail('Master.event.deadlineType'),
            'duplicationCheckFlg' => Configure::readOrFail('Master.event.duplicationCheckFlg'),
            'backgroundColorType' => Configure::readOrFail('Master.event.backgroundColorType'),
            'backgroundColorReplace' => $colorChipsTable->getColorChipList(true, false, [ColorChip::TYPE_EMPTY]),
            'reservationStatusId' => $statusLists,
            'common' => Configure::readOrFail('Master.event.common'),
            'reservationFormPatternId' => $formPatternsTable->getFormPatternList(
                FormGroup::FORM_TYPE_RESERVATION
            ),
            'formItemId' => $remarkFormId,
            'symbolic' => Configure::readOrFail('Master.event.symbolicDisp'),
            'stockDisplayType' => Configure::readOrFail('Master.event.stockDisplayType'),
            'multipleTimePlanType' => Configure::readOrFail('Master.event.multipleTimePlanType'),
            'deadlineTime' => $this->getDeadlineTimeList(),
            'receptionPeriodTime' => DateTimeUtility::createTimeList(0, 23, 1, '時'),
            'usageTimeNotation' => Configure::readOrFail('Master.event.usageTimeNotation'),
            'week' => Configure::readOrFail('Master.event.week'),
            'colorChip' => $colorChipsTable->getColorChipList(false, true),
            'remarkDetailDisplayFlg' => Configure::readOrFail('Master.eventRemarks.detailDisplayFlg'),
            'organizers' => $organizersTable->getOrganizerNames(),
            'qrCodeFlg' => Configure::readOrFail('Master.event.qrCodeFlg'),
            'deadlineCriterion' => Configure::readOrFail('Master.event.deadlineCriterion'),
        ];

        $eventRemarksTable->setFieldValueOptions($fieldValueOptions);

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
        $data->exchangeArray($this->clearUnnecessaryInputs($data->getArrayCopy()));

        if (!Hash::get($options, 'together', false)) {
            if (!isset($data['event_plans']) || !is_array($data['event_plans'])) {
                $data->offsetSet('event_plans', []);
            }

            if (!isset($data['event_stock_marks']) || !is_array($data['event_stock_marks'])) {
                $defaults = $this->getDefaultFieldValues();
                $data->offsetSet(
                    'event_stock_marks',
                    Hash::get($defaults, 'event_stock_marks', [])
                );
            }

            if (!isset($data['event_remarks']) || !is_array($data['event_remarks'])) {
                $data->offsetSet('event_remarks', []);
            }

            if (!isset($data['event_holidays']) || !is_array($data['event_holidays'])) {
                $data->offsetSet('event_holidays', []);
            }

            if (!isset($data['event_stock_settings']) || !is_array($data['event_stock_settings'])) {
                $data->offsetSet('event_stock_settings', []);
            }

            // バリデーションに値を渡す
            $type = Hash::get($data, 'type');
            $plans = Hash::get($data, 'event_plans');
            if (is_array($plans)) {
                foreach ($plans as $childIndex => $child) {
                    if (is_array($child) && !empty($child)) {
                        $plans[$childIndex]['event_type'] = $type;
                    }
                }
                $data->offsetSet('event_plans', $plans);
            }
        } else {
            if ((string)Hash::get($data['update'], 'event_remarks') === '1') {
                if (!isset($data['event_remarks']) || !is_array($data['event_remarks'])) {
                    $data->offsetSet('event_remarks', []);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $default = [];

        // 在庫表示設定の閾値0と1は固定
        foreach ([0, 1] as $number) {
            $default['event_stock_marks'][] = [
                'number' => $number,
                'symbolic' => Event::SYMBOLIC_DISP_HIDE,
            ];
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        if (!($validator instanceof KuchenValidator)) {
            throw new CakeException();
        }

        $addRule = $this->getCommonValidator($validator);

        $validator
            ->requirePresence('type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('type')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('time_plan', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('time_plan', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('time_plan', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('plan')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('name', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('name', __(Message::ERROR_NOT_EMPTY), false)
            ->add('name', $addRule['name']);

        $validator
            ->requirePresence('public_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('public_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('public_flg', $addRule['public_flg']);

        $validator
            ->requirePresence('sort_no', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('sort_no')
            ->add('sort_no', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::SORT_NO_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::SORT_NO_MAX),
                ],
            ]);

        $validator->requirePresence('date_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDate('date_from', __(Message::ERROR_NOT_EMPTY), false)
            ->add('date_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => [
                        'date',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'future' => [
                    'rule' => function ($value, $context) {
                        return $this->checkReserveDate($value, $context);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IS_RESERVE),
                ],

            ]);

        $validator->requirePresence('date_to', false, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDate('date_to')
            ->add('date_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => [
                        'date',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'date_from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                ],
                'future' => [
                    'rule' => function ($value, $context) {
                        return $this->checkReserveDate($value, $context);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IS_RESERVE),
                ],
            ]);

        $validator
            ->requirePresence('stock', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('stock', __(Message::ERROR_NOT_EMPTY), false)
            ->add('stock', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::STOCK_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::STOCK_MAX),
                ],
            ]);

        $validator
            ->requirePresence('event_unit_time', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('event_unit_time', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (
                    $validator->isValid('type')
                    && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_DAY
                ) {
                    return true;
                }

                return false;
            })
            ->add('event_unit_time', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::UNIT_TIME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::UNIT_TIME_MAX),
                    'on' => function ($context) use ($validator) {
                        return $validator->isValid('type')
                            && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_TIME;
                    },
                ],
                'multipleNum' => [
                    'rule' => ['multipleNumStatic', static::UNIT_TIME_MIN],
                    'last' => true,
                    'on' => function ($context) use ($validator) {
                        if (
                            $validator->isValid('type')
                            && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_DAY
                        ) {
                            return false;
                        }

                        return true;
                    },
                    'message' => __(Message::ERROR_MULTIPLE_NUM, static::UNIT_TIME_MIN),
                ],
            ]);

        $validator
            ->requirePresence('usage_unit_time', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_unit_time', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (
                    $validator->isValid('type')
                    && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_DAY
                ) {
                    return true;
                }
                if (
                    $validator->isValid('time_plan')
                    && (string)Hash::get($context['data'], 'time_plan') === (string)Event::PLAN_MULTIPLE
                ) {
                    return true;
                }

                return false;
            })
            ->add('usage_unit_time', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'greaterThanOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_GREATER_OR_EQUAL,
                        Event::USETIME_INTERVAL,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_UNDER_DIGIT, Event::USETIME_INTERVAL),
                    'on' => function ($context) use ($validator) {
                        return $validator->isValid('type')
                            && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_TIME;
                    },
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::USAGE_TIME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::USAGE_TIME_MAX),
                    'on' => function ($context) use ($validator) {
                        return $validator->isValid('type')
                            && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_TIME;
                    },
                ],
                'multipleNum' => [
                    'rule' => ['multipleNum', 'event_unit_time'],
                    'last' => true,
                    'on' => function ($context) use ($validator) {
                        if (
                            $validator->isValid('type')
                            && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_DAY
                        ) {
                            return false;
                        }

                        return true;
                    },
                    'message' => __(Message::ERROR_MULTIPLE_NUM, '予約最小単位'),
                ],
            ]);

        $validator
            ->requirePresence('usage_unit_day', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_unit_day', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (
                    $validator->isValid('type')
                    && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_TIME
                ) {
                    return true;
                }
                if (
                    $validator->isValid('time_plan')
                    && (string)Hash::get($context['data'], 'time_plan') === (string)Event::PLAN_MULTIPLE
                ) {
                    return true;
                }

                return false;
            })
            ->add('usage_unit_day', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                    'last' => true,
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::UNIT_DAY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::UNIT_DAY_MAX),
                    'on' => function ($context) use ($validator) {
                        return $validator->isValid('type')
                            && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_DAY;
                    },
                ],
            ]);

        $validator
            ->requirePresence('usage_time_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_time_from', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (
                    $validator->isValid('type')
                    && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_DAY
                ) {
                    return true;
                }

                if (
                    $validator->isValid('time_plan')
                    && (string)Hash::get($context['data'], 'time_plan') === (string)Event::PLAN_MULTIPLE
                ) {
                    return true;
                }

                return false;
            })
            ->add('usage_time_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'greaterThanOrEqualTime' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_GREATER_OR_EQUAL,
                        Event::USETIME_INTERVAL,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_UNDER_DIGIT, Event::USETIME_INTERVAL),
                    'on' => function ($context) use ($validator) {
                        return $validator->isValid('type')
                            && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_TIME;
                    },
                ],
                'lessThanOrEqualDay' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::USAGE_TIME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::USAGE_TIME_MAX),
                ],
                'multipleNum' => [
                    'rule' => ['multipleNum', 'usage_unit_time'],
                    'last' => true,
                    'message' => __(Message::ERROR_MULTIPLE_NUM, '利用時間最小単位'),
                ],
            ]);

        $validator
            ->requirePresence('usage_time_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_time_to', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (
                    $validator->isValid('type')
                    && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_DAY
                ) {
                    return true;
                }
                if (
                    $validator->isValid('time_plan')
                    && (string)Hash::get($context['data'], 'time_plan') === (string)Event::PLAN_MULTIPLE
                ) {
                    return true;
                }

                return false;
            })
            ->add('usage_time_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'greaterThanOrEqualDay' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_GREATER_OR_EQUAL,
                        Event::USETIME_INTERVAL,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_UNDER_DIGIT, Event::USETIME_INTERVAL),
                    'on' => function ($context) use ($validator) {
                        return $validator->isValid('type')
                            && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_TIME;
                    },
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::USAGE_TIME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::USAGE_TIME_MAX),
                ],
                'multipleNum' => [
                    'rule' => ['multipleNum', 'usage_unit_time'],
                    'last' => true,
                    'message' => __(Message::ERROR_MULTIPLE_NUM, '利用時間最小単位'),
                ],
                'edit' => [
                    'rule' => function ($value, $context) use ($validator) {
                        return $this->checkUsageTime($value, $context, $validator);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_CHANGE_USAGE_TIME),
                    'on' => function ($context) use ($validator) {
                        if (
                            $validator->isValid('type')
                            && $validator->isValid('usage_time_from')
                            && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_TIME
                        ) {
                            return true;
                        }
                    },
                ],
                'compareFields' => [
                    'rule' => ['compareFields', 'usage_time_from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'last' => true,
                    'on' => function () use ($validator) {
                        // 比較対象にエラーがある場合は比較処理を行わない
                        return $validator->isValid('usage_time_from');
                    },
                    'message' => __(Message::ERROR_LESS_THAN_FROM),
                ],
            ]);

        $validator
            ->requirePresence('usage_day_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_day_from', __(Message::ERROR_NOT_EMPTY), function ($context) {
                if (Hash::get($context['data'], 'type') != Event::TYPE_DAY) {
                    return true;
                }

                if ((string)Hash::get($context['data'], 'time_plan') === (string)Event::PLAN_MULTIPLE) {
                    return true;
                }

                return false;
            })
            ->add('usage_day_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqualDay' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::UNIT_DAY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::UNIT_DAY_MAX),
                ],
                'multipleNum' => [
                    'rule' => ['multipleNum', 'usage_unit_day'],
                    'last' => true,
                    'message' => __(Message::ERROR_MULTIPLE_NUM, '利用日最小単位'),
                ],
            ]);

        $validator
            ->requirePresence('usage_day_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_day_to', __(Message::ERROR_NOT_EMPTY), function ($context) {
                if ((string)Hash::get($context['data'], 'type') !== (string)Event::TYPE_DAY) {
                    return true;
                }

                if ((string)Hash::get($context['data'], 'time_plan') === (string)Event::PLAN_MULTIPLE) {
                    return true;
                }

                return false;
            })
            ->add('usage_day_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],

                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::UNIT_DAY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::UNIT_DAY_MAX),
                ],

                'multipleNum' => [
                    'rule' => ['multipleNum', 'usage_unit_day'],
                    'last' => true,
                    'message' => __(Message::ERROR_MULTIPLE_NUM, '利用日最小単位'),
                ],
                'compareFields' => [
                    'rule' => ['compareFields', 'usage_day_from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'last' => true,
                    'on' => function () use ($validator) {
                        // 比較対象にエラーがある場合は比較処理を行わない
                        return $validator->isValid('usage_day_from');
                    },
                    'message' => __(Message::ERROR_LESS_THAN_FROM),
                ],
            ]);

        $validator->requirePresence('time_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyTime('time_from', __(Message::ERROR_NOT_EMPTY), false)
            ->add('time_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'time' => [
                    'rule' => [
                        'time24h',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_TIME),
                ],
            ]);

        $validator->requirePresence('time_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyTime('time_to', __(Message::ERROR_NOT_EMPTY), false)
            ->add('time_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'time' => [
                    'rule' => [
                        'time24h',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_TIME),
                ],
                'checkMultipleNumTime' => [
                    'rule' => function ($value, $context) use ($validator) {
                        return $this->checkMultipleNumTime($value, $context, $validator);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_EVENT_TIME_MULTIPLE),
                ],
                'isReserve' => [
                    'rule' => function ($value, $context) {
                        return $this->checkReserveDate($value, $context);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IS_RESERVE),
                ],
            ]);

        $validator->requirePresence('event_plans', function ($context) use ($validator) {
            return $validator->isValid('time_plan')
                && (string)Hash::get($context['data'], 'time_plan') === (string)Event::PLAN_MULTIPLE;
        }, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyArray('event_plans', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                return !(($validator->isValid('time_plan')
                    && (string)Hash::get($context['data'], 'time_plan') === (string)Event::PLAN_MULTIPLE));
            })
            ->array('event_plans', __(Message::ERROR_NOT_EMPTY));

        $validator
            ->requirePresence('multiple_time_plan_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString(
                'multiple_time_plan_type',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) use ($validator) {
                    return !(($validator->isValid('time_plan')
                        && (string)Hash::get($context['data'], 'time_plan') === (string)Event::PLAN_MULTIPLE));
                }
            )
            ->add('multiple_time_plan_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('multipleTimePlanType')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
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
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::STOCK_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::STOCK_MAX),
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
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::STOCK_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::STOCK_MAX),
                ],
                'compareFields' => [
                    'rule' => ['compareFields', 'stock_range_to', Validation::COMPARE_LESS_OR_EQUAL],
                    'last' => true,
                    'on' => function () use ($validator) {
                        // 比較対象にエラーがある場合は比較処理を行わない
                        return $validator->isValid('stock_range_to');
                    },
                    'message' => __(Message::ERROR_GRATER_THAN_TO),
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
            ->requirePresence('interval_time', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('interval_time')
            ->add('interval_time', [
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
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::INTERVAL_TIME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::INTERVAL_TIME_MAX),
                ],
                'multipleNum' => [
                    'rule' => ['multipleNum', 'event_unit_time'],
                    'last' => true,
                    'message' => __(Message::ERROR_MULTIPLE_NUM, '分単位のコマ割り'),
                    'on' => function ($context) use ($validator) {
                        return $validator->isValid('type')
                            && (string)Hash::get($context['data'], 'type') === (string)Event::TYPE_TIME;
                    },
                ],
            ]);

        $validator
            ->requirePresence('interval_day', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('interval_day')
            ->add('interval_day', [
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
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::INTERVAL_DAY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::INTERVAL_DAY_MAX),
                ],
            ]);

        $validator->requirePresence('usage_time_notation', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('usage_time_notation', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('usage_time_notation', $addRule['usage_time_notation']);

        $validator->requirePresence('public_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDateTime('public_from')
            ->add('public_from', $addRule['public_from']);

        $validator->requirePresence('public_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDateTime('public_to')
            ->add('public_to', $addRule['public_to']);

        $validator
            ->requirePresence('label_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            // 担当カテゴリがある管理者の場合は入力必須にする(マスター管理者を除く)
            ->allowEmptyString('label_id', __(Message::ERROR_IN_LIST), function () {
                /** @var \App\Model\Entity\Admin $loginData */
                $loginData = $this->commonData()->getAdminLoginData();
                if ($loginData->isMasterAdmin()) {
                    return true;
                }

                return $this->commonData()->getAdminLoginLabel() === null;
            });
        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator);
        $validator = $labelTable->addValidateLabelIdAdminUsable($validator);

        $validator->requirePresence('event_tags', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->array('event_tags', __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('event_tags');

        $validator
            ->requirePresence('charge', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('charge', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (!($validator instanceof KuchenValidator)) {
                    throw new CakeException();
                }

                if (
                    $validator->isValid('time_plan')
                    && (string)Hash::get($context['data'], 'time_plan') === (string)Event::PLAN_SINGLE
                ) {
                    return false;
                }

                return true;
            })
            ->add('charge', $addRule['charge']);

        $validator
            ->requirePresence('reservation_limit_future', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reservation_limit_future')
            ->add('reservation_limit_future', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reservation_limit_month', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reservation_limit_month')
            ->add('reservation_limit_month', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reservation_limit_day', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reservation_limit_day')
            ->add('reservation_limit_day', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reservation_limit_all', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reservation_limit_all')
            ->add('reservation_limit_all', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reception_period_number', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reception_period_number', __(Message::ERROR_NOT_EMPTY), true)
            ->add('reception_period_number', $addRule['reception_period_number']);

        $validator
            ->requirePresence('reception_period_time', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyTime('reception_period_time', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                $receptionPeriodNumber = Hash::get($context['data'], 'reception_period_number');

                return empty($receptionPeriodNumber);
            })
            ->add('reception_period_time', $addRule['reception_period_time']);

        $validator
            ->requirePresence('registration_deadline_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('registration_deadline_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('registration_deadline_type', $addRule['deadline_type_valid']);

        $validator
            ->requirePresence('editing_deadline_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('editing_deadline_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('editing_deadline_type', $addRule['deadline_type_valid']);

        $validator
            ->requirePresence('cancellation_deadline_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('cancellation_deadline_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('cancellation_deadline_type', $addRule['deadline_type_valid']);

        $validator
            ->requirePresence('registration_deadline_number', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('registration_deadline_number', __(Message::ERROR_NOT_EMPTY), false)
            ->add('registration_deadline_number', $addRule['deadline_num_valid_regist']);

        $validator
            ->requirePresence('editing_deadline_number', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('editing_deadline_number', __(Message::ERROR_NOT_EMPTY), false)
            ->add('editing_deadline_number', $addRule['deadline_num_valid_edit']);

        $validator
            ->requirePresence('cancellation_deadline_number', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('cancellation_deadline_number', __(Message::ERROR_NOT_EMPTY), false)
            ->add('cancellation_deadline_number', $addRule['deadline_num_valid_cancel']);

        $validator
            ->requirePresence('registration_deadline_time', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyTime(
                'registration_deadline_time',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) use ($validator) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    $registrationDeadlineType = Hash::get($context['data'], 'registration_deadline_type');

                    return !($validator->isValid('registration_deadline_type')
                        && (string)$registrationDeadlineType === (string)Event::DEADLINE_TYPE_DAY);
                }
            )
            ->add('registration_deadline_time', $addRule['deadline_time_valid']);

        $validator
            ->requirePresence('editing_deadline_time', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyTime(
                'editing_deadline_time',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) use ($validator) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    $editingDeadlineType = Hash::get($context['data'], 'editing_deadline_type');

                    return !($validator->isValid('editing_deadline_type')
                        && (string)$editingDeadlineType === (string)Event::DEADLINE_TYPE_DAY);
                }
            )
            ->add('editing_deadline_time', $addRule['deadline_time_valid']);

        $validator
            ->requirePresence('cancellation_deadline_time', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyTime(
                'cancellation_deadline_time',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) use ($validator) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    $cancellationDeadlineType = Hash::get($context['data'], 'cancellation_deadline_type');

                    return !($validator->isValid('cancellation_deadline_type')
                        && (string)$cancellationDeadlineType === (string)Event::DEADLINE_TYPE_DAY);
                }
            )
            ->add('cancellation_deadline_time', $addRule['deadline_time_valid']);

        $validator
            ->requirePresence('reservation_status_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('reservation_status_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reservation_status_id', $addRule['reservation_status_id']);

        $validator
            ->requirePresence('waiting_cancellation_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('waiting_cancellation_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('waiting_cancellation_flg', $addRule['waiting_cancellation_flg']);

        $validator
            ->requirePresence('duplication_check_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('duplication_check_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('duplication_check_flg', $addRule['duplication_check_flg']);

        $validator
            ->requirePresence('stock_display_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('stock_display_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('stock_display_type', $addRule['stock_display_type']);

        $validator->requirePresence('event_stock_marks', false, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyArray('event_stock_marks', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (!($validator instanceof KuchenValidator)) {
                    throw new CakeException();
                }

                $stockDisplayType = Hash::get($context['data'], 'stock_display_type');

                return !($validator->isValid('stock_display_type')
                    && (string)$stockDisplayType === (string)Event::STOCK_DISPLAY_TYPE_ICON);
            })
            ->array('event_stock_marks', __(Message::ERROR_NOT_EMPTY));

        $validator
            ->requirePresence('stock_unit', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('stock_unit')
            ->add('stock_unit', $addRule['stock_unit']);

        $validator
            ->requirePresence('background_color_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('background_color_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('background_color_type', $addRule['background_color_type']);

        $validator
            ->requirePresence('color_chip_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString(
                'color_chip_id',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) use ($validator) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    $backgroundColorType = Hash::get($context['data'], 'background_color_type');

                    return !($validator->isValid('background_color_type')
                        && (string)$backgroundColorType === (string)Event::BACKGROUND_COLOR_TYPE_COLOR_CODE);
                }
            )
            ->add('color_chip_id', $addRule['color_chip_id']);

        $validator
            ->requirePresence('background_color_replace_front', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('background_color_replace_front')
            ->add('background_color_replace_front', $addRule['color_replace']);

        $validator
            ->requirePresence('background_color_replace_admin', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('background_color_replace_admin')
            ->add('background_color_replace_admin', $addRule['color_replace']);

        $validator
            ->requirePresence('format_type_display', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('format_type_display', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('format_type_display', $addRule['format_type_display']);

        $validator
            ->requirePresence('form_pattern_id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('form_pattern_id', __(Message::ERROR_NOT_EMPTY), false)
            ->add('form_pattern_id', $addRule['reservation_form_pattern_id']);

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

        $validator
            ->requirePresence('event_images', true, __(Message::ERROR_INVALID_VALUE))
            ->array('event_images', __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('event_images');

        $validator
            ->allowEmptyArray('event_remarks')
            ->requirePresence('event_remarks', false)
            ->array('event_remarks', __(Message::ERROR_NOT_EMPTY_SELECT));

        $validator
            ->requirePresence('event_weeks', true, __(Message::ERROR_INVALID_VALUE))
            ->allowEmptyArray('event_weeks')
            ->array('event_weeks', __(Message::ERROR_NOT_EMPTY_SELECT));

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if ($systemSettingsTable->getData()->canCoordinateVideoMeeting()) {
            $validator
                ->requirePresence('organizer_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
                ->allowEmptyString('organizer_id')
                ->add('organizer_id', [
                    'inList' => [
                        'rule' => ['inList', array_keys($this->getFieldValueOptions('organizers'))],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);
        }

        $validator
            ->requirePresence('qr_code_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('qr_code_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('qr_code_flg', $addRule['qr_code_flg']);

        $validator
            ->requirePresence('registration_deadline_criterion', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('registration_deadline_criterion', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('registration_deadline_criterion', $addRule['registration_deadline_criterion']);

        $validator
            ->requirePresence('editing_deadline_criterion', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('editing_deadline_criterion', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('editing_deadline_criterion', $addRule['editing_deadline_criterion']);

        $validator
            ->requirePresence('cancellation_deadline_criterion', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('cancellation_deadline_criterion', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('cancellation_deadline_criterion', $addRule['cancellation_deadline_criterion']);

        $smartLock = new SmartLockLinkage();
        if ($smartLock->useAkerun()) {
            $validator
                ->requirePresence('event_smart_lock', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyArray('event_smart_lock', __(Message::ERROR_NOT_EMPTY), false);
        }

        return $validator;
    }

    /**
     * Returns the default validator object.
     *
     * @param \Cake\Validation\Validator $validator The validator.
     * @return \Cake\Validation\Validator
     */
    public function validationTogether(Validator $validator)
    {
        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');

        $addRule = $this->getCommonValidator($validator);

        $validator
            ->requirePresence('name', false)
            ->allowEmptyString('name', __(Message::ERROR_NOT_EMPTY), function ($context) {
                return !((string)Hash::get($context, 'data.update.name') === '1');
            })
            ->add('name', $addRule['name']);

        $validator
            ->requirePresence('label_id', false)
            ->allowEmptyString('label_id', __(Message::ERROR_IN_LIST), function ($context) {
                /** @var \App\Model\Entity\Admin $loginData */
                $loginData = $this->commonData()->getAdminLoginData();

                if (
                    (string)Hash::get($context, 'data.update.label') === '1'
                    && !$loginData->isMasterAdmin()
                    && $this->commonData()->getAdminLoginLabel() !== null
                ) {
                    return false;
                }

                return true;
            });
        $validator = $labelTable->addValidateLabelId($validator);
        $validator = $labelTable->addValidateLabelIdAdminUsable($validator);

        $validator->requirePresence('public_flg', false)
            ->allowEmptyString('public_flg', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.public_flg') === '1');
            })
            ->add('public_flg', $addRule['public_flg']);

        $validator->requirePresence('public_from', false)
            ->allowEmptyDateTime('public_from')
            ->add('public_from', $addRule['public_from']);

        $validator->requirePresence('public_to', false, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDateTime('public_to')
            ->add('public_to', $addRule['public_to']);

        $validator
            ->requirePresence('charge', false)
            ->allowEmptyString('charge', __(Message::ERROR_NOT_EMPTY), function ($context) {
                return !((string)Hash::get($context, 'data.update.charge') === '1');
            })
            ->add('charge', $addRule['charge']);

        $validator
            ->requirePresence('stock_unit', false)
            ->allowEmptyString('stock_unit')
            ->add('stock_unit', $addRule['stock_unit']);

        $validator
            ->requirePresence('reservation_limit_future', false)
            ->allowEmptyString('reservation_limit_future')
            ->add('reservation_limit_future', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reservation_limit_month', false)
            ->allowEmptyString('reservation_limit_month')
            ->add('reservation_limit_month', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reservation_limit_day', false)
            ->allowEmptyString('reservation_limit_day')
            ->add('reservation_limit_day', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reservation_limit_all', false)
            ->allowEmptyString('reservation_limit_all')
            ->add('reservation_limit_all', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reception_period_number', false)
            ->allowEmptyString('reception_period_number', __(Message::ERROR_NOT_EMPTY), function ($context) {
                if ((string)Hash::get($context, 'data.update.reception_period_number') === '1') {
                    /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
                    $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
                    if ($systemSettingsTable->getData()->get('payment_use_flg') === SystemSetting::PAYMENT_USE_FLG_ON) {
                        return false;
                    }

                    return true;
                }

                return true;
            })
            ->add('reception_period_number', $addRule['reception_period_number']);

        $validator
            ->requirePresence('reception_period_time', false)
            ->allowEmptyString('reception_period_time', __(Message::ERROR_NOT_EMPTY), function ($context) {
                if ((string)Hash::get($context, 'data.update.reception_period_number') === '1') {
                    $receptionPeriodNumber = Hash::get($context['data'], 'reception_period_number');

                    return empty($receptionPeriodNumber);
                }

                return true;
            })
            ->add('reception_period_time', $addRule['reception_period_time']);

        $validator
            ->requirePresence('registration_deadline_type', false)
            ->allowEmptyString('registration_deadline_type', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.registration_deadline') === '1');
            })
            ->add('registration_deadline_type', $addRule['deadline_type_valid']);

        $validator
            ->requirePresence('editing_deadline_type', false)
            ->allowEmptyString('editing_deadline_type', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.editing_deadline') === '1');
            })
            ->add('editing_deadline_type', $addRule['deadline_type_valid']);

        $validator
            ->requirePresence('cancellation_deadline_type', false)
            ->allowEmptyString('cancellation_deadline_type', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.cancellation_deadline') === '1');
            })
            ->add('cancellation_deadline_type', $addRule['deadline_type_valid']);

        $validator
            ->requirePresence('registration_deadline_number', false)
            ->allowEmptyString('registration_deadline_number', __(Message::ERROR_NOT_EMPTY), function ($context) {
                return !((string)Hash::get($context, 'data.update.registration_deadline') === '1');
            })
            ->add('registration_deadline_number', $addRule['deadline_num_valid_regist']);

        $validator
            ->requirePresence('editing_deadline_number', false)
            ->allowEmptyString('editing_deadline_number', __(Message::ERROR_NOT_EMPTY), function ($context) {
                return !((string)Hash::get($context, 'data.update.editing_deadline') === '1');
            })
            ->add('editing_deadline_number', $addRule['deadline_num_valid_edit']);

        $validator
            ->requirePresence('cancellation_deadline_number', false)
            ->allowEmptyString('cancellation_deadline_number', __(Message::ERROR_NOT_EMPTY), function ($context) {
                return !((string)Hash::get($context, 'data.update.cancellation_deadline') === '1');
            })
            ->add('cancellation_deadline_number', $addRule['deadline_num_valid_cancel']);

        $validator
            ->requirePresence('registration_deadline_criterion', false)
            ->allowEmptyString(
                'registration_deadline_criterion',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) {
                    return !((string)Hash::get($context, 'data.update.registration_deadline') === '1');
                }
            )
            ->add('registration_deadline_criterion', $addRule['registration_deadline_criterion']);

        $validator
            ->requirePresence('editing_deadline_criterion', false)
            ->allowEmptyString('editing_deadline_criterion', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.editing_deadline') === '1');
            })
            ->add('editing_deadline_criterion', $addRule['editing_deadline_criterion']);

        $validator
            ->requirePresence('cancellation_deadline_criterion', false)
            ->allowEmptyString(
                'cancellation_deadline_criterion',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) {
                    return !((string)Hash::get($context, 'data.update.cancellation_deadline') === '1');
                }
            )
            ->add('cancellation_deadline_criterion', $addRule['cancellation_deadline_criterion']);

        $validator
            ->requirePresence('registration_deadline_time', false)
            ->allowEmptyTime(
                'registration_deadline_time',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) use ($validator) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    $registrationDeadlineType = Hash::get($context['data'], 'registration_deadline_type');
                    if ((string)Hash::get($context, 'data.update.registration_deadline') !== '1') {
                        return true;
                    }

                    return !($validator->isValid('registration_deadline_type')
                        && (string)$registrationDeadlineType === (string)Event::DEADLINE_TYPE_DAY);
                }
            )
            ->add('registration_deadline_time', $addRule['deadline_time_valid']);

        $validator
            ->requirePresence('editing_deadline_time', false)
            ->allowEmptyTime(
                'editing_deadline_time',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) use ($validator) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    $editingDeadlineType = Hash::get($context['data'], 'editing_deadline_type');
                    if ((string)Hash::get($context, 'data.update.editing_deadline') !== '1') {
                        return true;
                    }

                    return !($validator->isValid('editing_deadline_type')
                        && (string)$editingDeadlineType === (string)Event::DEADLINE_TYPE_DAY);
                }
            )
            ->add('editing_deadline_time', $addRule['deadline_time_valid']);

        $validator
            ->requirePresence('cancellation_deadline_time', false)
            ->allowEmptyTime(
                'cancellation_deadline_time',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) use ($validator) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    $cancellationDeadlineType = Hash::get($context['data'], 'cancellation_deadline_type');
                    if ((string)Hash::get($context, 'data.update.cancellation_deadline') !== '1') {
                        return true;
                    }

                    return !($validator->isValid('cancellation_deadline_type')
                        && (string)$cancellationDeadlineType === (string)Event::DEADLINE_TYPE_DAY);
                }
            )
            ->add('cancellation_deadline_time', $addRule['deadline_time_valid']);

        $validator
            ->requirePresence('waiting_cancellation_flg', false)
            ->allowEmptyString('waiting_cancellation_flg', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.waiting_cancellation_flg') === '1');
            })
            ->add('waiting_cancellation_flg', $addRule['waiting_cancellation_flg']);

        $validator
            ->requirePresence('duplication_check_flg', false)
            ->allowEmptyString('duplication_check_flg', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.duplication_check_flg') === '1');
            })
            ->add('duplication_check_flg', $addRule['duplication_check_flg']);

        $validator->requirePresence('usage_time_notation', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('usage_time_notation', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.usage_time_notation') === '1');
            })
            ->add('usage_time_notation', $addRule['usage_time_notation']);

        $validator
            ->requirePresence('stock_display_type', false)
            ->allowEmptyString('stock_display_type', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.stock_display_type') === '1');
            })
            ->add('stock_display_type', $addRule['stock_display_type']);

        $validator->requirePresence('event_stock_marks', false)
            ->allowEmptyArray('event_stock_marks', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (!($validator instanceof KuchenValidator)) {
                    throw new CakeException();
                }

                $stockDisplayType = Hash::get($context['data'], 'stock_display_type');
                if ((string)Hash::get($context, 'data.update.stock_display_type') !== '1') {
                    return true;
                }

                return !($validator->isValid('stock_display_type')
                    && (string)$stockDisplayType === (string)Event::STOCK_DISPLAY_TYPE_ICON);
            })
            ->array('event_stock_marks', __(Message::ERROR_NOT_EMPTY));

        $validator
            ->requirePresence('stock_unit', false)
            ->allowEmptyString('stock_unit')
            ->add('stock_unit', $addRule['stock_unit']);

        $validator
            ->requirePresence('background_color_type', false)
            ->allowEmptyString('background_color_type', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.background_color_type') === '1');
            })
            ->add('background_color_type', $addRule['background_color_type']);

        $validator
            ->requirePresence('color_chip_id', false)
            ->allowEmptyString(
                'color_chip_id',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) use ($validator) {
                    if (!($validator instanceof KuchenValidator)) {
                        throw new CakeException();
                    }

                    $backgroundColorType = Hash::get($context['data'], 'background_color_type');
                    if ((string)Hash::get($context, 'data.update.background_color_type') !== '1') {
                        return true;
                    }

                    return !($validator->isValid('background_color_type')
                        && (string)$backgroundColorType === (string)Event::BACKGROUND_COLOR_TYPE_COLOR_CODE);
                }
            )
            ->add('color_chip_id', $addRule['color_chip_id']);

        $validator
            ->requirePresence('background_color_replace_front', false)
            ->allowEmptyArray('background_color_replace_front')
            ->add('background_color_replace_front', $addRule['color_replace']);

        $validator
            ->requirePresence('background_color_replace_admin', false)
            ->allowEmptyArray('background_color_replace_admin')
            ->add('background_color_replace_admin', $addRule['color_replace']);

        $validator
            ->requirePresence('format_type_display', false)
            ->allowEmptyArray('format_type_display', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.format_type_display') === '1');
            })
            ->add('format_type_display', $addRule['format_type_display']);

        $validator
            ->requirePresence('form_pattern_id', false)
            ->allowEmptyString('form_pattern_id', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.form_pattern_id') === '1');
            })
            ->add('form_pattern_id', $addRule['reservation_form_pattern_id']);

        $validator
            ->requirePresence('event_images', false)
            ->array('event_images', __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('event_images');

        $validator->requirePresence('event_tags', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->array('event_tags', __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('event_tags');

        $validator
            ->allowEmptyArray('event_remarks')
            ->requirePresence('event_remarks', false)
            ->array('event_remarks', __(Message::ERROR_NOT_EMPTY_SELECT));

        $validator
            ->requirePresence('reservation_status_id', false)
            ->allowEmptyString('reservation_status_id', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.reservation_status_id') === '1');
            })
            ->add('reservation_status_id', $addRule['reservation_status_id']);

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

        $validator->requirePresence('qr_code_flg', false)
            ->allowEmptyString('qr_code_flg', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) {
                return !((string)Hash::get($context, 'data.update.qr_code_flg') === '1');
            })
            ->add('qr_code_flg', $addRule['qr_code_flg']);

        $validator
            ->allowEmptyArray('event_smart_lock')
            ->requirePresence('event_smart_lock', false)
            ->array('event_smart_lock', __(Message::ERROR_NOT_EMPTY));

        return $validator;
    }

    /**
     * 各画面共通で使うvalidatorの設定を返却
     *
     * @param \Cake\Validation\Validator $validator The validator.
     * @return array
     */
    protected function getCommonValidator($validator)
    {
        if (!($validator instanceof KuchenValidator)) {
            throw new CakeException();
        }

        $addRule = [
            'name' => [
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
            ],
            'public_flg' => [
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
            ],
            'public_from' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => [
                        'dateTime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
            ],
            'public_to' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => [
                        'dateTime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'public_from', '>'],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                ],
            ],
            'label' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'message' => __(Message::ERROR_NUMBER),
                    'last' => true,
                ],
                'exists' => [
                    'rule' => function ($check) {
                        return $this->getTableLocator()->get('Labels')->exists([
                            'id' => $check,
                        ]);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'usage_time_notation' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('usageTimeNotation')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],

            ],
            'reception_period_number' => [
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
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_LESS_OR_EQUAL,
                        $this->getMaxDeadLine('reception_period'),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, $this->getMaxDeadLine('reception_period')),
                ],
            ],
            'reception_period_time' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('receptionPeriodTime')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'deadline_type_valid' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('deadlineType')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'deadline_num_valid_regist' => [
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
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, $this->getMaxDeadLine('time')],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, $this->getMaxDeadLine('time')),
                    'on' => function ($context) use ($validator) {
                        $registrationDeadlineType = Hash::get($context['data'], 'registration_deadline_type');
                        if (
                            $validator->isValid('registration_deadline_type')
                            && (string)$registrationDeadlineType === (string)Event::DEADLINE_TYPE_TIME
                        ) {
                            return true;
                        }

                        return false;
                    },
                ],
                'lessThanOrEqualDay' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, $this->getMaxDeadLine('day')],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, $this->getMaxDeadLine('day')),
                    'on' => function ($context) use ($validator) {
                        $registrationDeadlineType = Hash::get($context['data'], 'registration_deadline_type');
                        if (
                            $validator->isValid('registration_deadline_type')
                            && (string)$registrationDeadlineType === (string)Event::DEADLINE_TYPE_DAY
                        ) {
                            return true;
                        }

                        return false;
                    },
                ],
            ],
            'deadline_num_valid_edit' => [
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
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, $this->getMaxDeadLine('time')],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, $this->getMaxDeadLine('time')),
                    'on' => function ($context) use ($validator) {
                        $editingDeadlineType = Hash::get($context['data'], 'editing_deadline_type');
                        if (
                            $validator->isValid('editing_deadline_type')
                            && (string)$editingDeadlineType === (string)Event::DEADLINE_TYPE_TIME
                        ) {
                            return true;
                        }

                        return false;
                    },
                ],
                'lessThanOrEqualDay' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, $this->getMaxDeadLine('day')],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, $this->getMaxDeadLine('day')),
                    'on' => function ($context) use ($validator) {
                        $editingDeadlineType = Hash::get($context['data'], 'editing_deadline_type');
                        if (
                            $validator->isValid('editing_deadline_type')
                            && (string)$editingDeadlineType === (string)Event::DEADLINE_TYPE_DAY
                        ) {
                            return true;
                        }

                        return false;
                    },
                ],
            ],
            'deadline_num_valid_cancel' => [
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
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, $this->getMaxDeadLine('time')],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, $this->getMaxDeadLine('time')),
                    'on' => function ($context) use ($validator) {
                        $cancellationDeadlineType = Hash::get($context['data'], 'cancellation_deadline_type');
                        if (
                            $validator->isValid('cancellation_deadline_type')
                            && (string)$cancellationDeadlineType === (string)Event::DEADLINE_TYPE_TIME
                        ) {
                            return true;
                        }

                        return false;
                    },
                ],
                'lessThanOrEqualDay' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, $this->getMaxDeadLine('day')],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, $this->getMaxDeadLine('day')),
                    'on' => function ($context) use ($validator) {
                        $cancellationDeadlineType = Hash::get($context['data'], 'cancellation_deadline_type');
                        if (
                            $validator->isValid('cancellation_deadline_type')
                            && (string)$cancellationDeadlineType === (string)Event::DEADLINE_TYPE_DAY
                        ) {
                            return true;
                        }

                        return false;
                    },
                ],
            ],
            'deadline_time_valid' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('deadlineTime')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'waiting_cancellation_flg' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('common')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'duplication_check_flg' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('duplicationCheckFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'stock_display_type' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('stockDisplayType')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'stock_unit' => [
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
            ],
            'background_color_type' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => '不正な値が入力されました',
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('backgroundColorType')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'color_chip_id' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('colorChip')),
                    ],
                    'last' => true,
                    'on' => function ($context) use ($validator) {
                        if (
                            $validator->isValid('background_color_type')
                            && (string)Hash::get(
                                $context['data'],
                                'background_color_type'
                            ) === (string)Event::BACKGROUND_COLOR_TYPE_COLOR_CODE
                        ) {
                            return true;
                        }

                        return false;
                    },
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'color_replace' => [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => [
                        'multiple',
                        [
                            'in' => array_keys($this->getFieldValueOptions('backgroundColorReplace')),
                        ],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'format_type_display' => [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => [
                        'multiple',
                        [
                            'in' => array_keys($this->getFieldValueOptions('formatTypeDisplay')),
                        ],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'reservation_form_pattern_id' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('reservationFormPatternId')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'reservation_limit' => [
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
                'lessThanOrEqualDay' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::RESERVATION_LIMIT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::RESERVATION_LIMIT_MAX),
                ],
            ],
            'charge' => [
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
            ],
            'reservation_status_id' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('reservationStatusId')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'qr_code_flg' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('qrCodeFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'registration_deadline_criterion' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('deadlineCriterion')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'editing_deadline_criterion' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('deadlineCriterion')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
            'cancellation_deadline_criterion' => [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('deadlineCriterion')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ],
        ];

        return $addRule;
    }

    /**
     * Returns the default validator object.
     *
     * @param \Cake\Validation\Validator $validator The validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUpdatePublic(Validator $validator)
    {
        $addRule = $this->getCommonValidator($validator);

        $validator
            ->requirePresence('public_flg', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('public_flg')
            ->add('public_flg', $addRule['public_flg']);

        return $validator;
    }

    /**
     * 締切の最大値を取得
     *
     * @param string $type 予約枠タイプ
     * @return mixed
     */
    protected function getMaxDeadLine($type)
    {
        if (!isset($this->deadLineMax)) {
            /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
            $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

            if ($systemSettingsTable->getData()->get('payment_use_flg')) {
                $this->deadLineMax['day'] = static::DEAD_LINE_DAY_MAX_USE_PAYMENT;
                $this->deadLineMax['time'] = static::DEAD_LINE_TIME_MAX_USE_PAYMENT;
            } else {
                $this->deadLineMax['day'] = static::DEAD_LINE_MAX;
                $this->deadLineMax['time'] = static::DEAD_LINE_MAX;
            }
            $this->deadLineMax['reception_period'] = static::RECEPTION_PERIOD_MAX;
        }

        return $this->deadLineMax[$type];
    }

    /**
     * validatorで実施できない入力チェック
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param array $inputs POST
     * @return void
     */
    public function inputCheckOther(EntityInterface $entity, array $inputs)
    {
        /** @var \App\Model\Table\EventRemarksTable $eventRemarksTable */
        $eventRemarksTable = $this->getAssociation('EventRemarks')->getTarget();

        // 注釈項目の重複チェック
        if (is_array($entity->get('event_remarks'))) {
            $eventRemarksTable->checkDuplicationFormId($entity);
        }
    }

    /**
     * 初期選択
     *
     * @param string|null $key キー
     * @param mixed|null $default デフォルト値
     * @return mixed デフォルト値
     */
    public function getDefaultFieldValues(?string $key = null, $default = null)
    {
        $inputs = parent::getDefaultFieldValues($key, $default);

        $inputs['event_stock_marks'][0]['number'] = 0;
        $inputs['event_stock_marks'][1]['number'] = 1;

        $inputs['type'] = Event::TYPE_TIME;
        $inputs['time_plan'] = Event::PLAN_SINGLE;
        $inputs['stock_display_type'] = Event::STOCK_DISPLAY_TYPE_NUMBER;
        $inputs['background_color_type'] = Event::BACKGROUND_COLOR_TYPE_DEFAULT;

        $inputs['background_color_replace_front'] = array_keys($this->getFieldValueOptions('backgroundColorReplace'));
        $inputs['background_color_replace_admin'] = $inputs['background_color_replace_front'];

        $defaultWeeks = [];
        foreach (array_keys($this->getFieldValueOptions('week')) as $key => $week) {
            $defaultWeeks[$key]['week'] = $week;
        }

        $inputs['event_weeks'] = $defaultWeeks;

        return $inputs;
    }

    /**
     * 予約状況の取得
     *
     * @param int $id 予約枠ID
     * @return void
     */
    public function setEditAbled(int $id)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $this->editAbled = $reservationsTable->getReserveFutureAndAll($id);
    }

    /**
     * 編集可否を取得
     *
     * @return mixed
     */
    public function getEditAbled()
    {
        return $this->editAbled;
    }

    /**
     * 予約数チェック
     *
     * @param mixed $val 入力値
     * @param mixed $context context
     * @param \Kuchen\Validation\Validation\Validator $validator バリデーター
     * @return bool
     */
    protected function checkUsageTime($val, $context, KuchenValidator $validator)
    {
        $data = $context['data'];

        // 編集時以外はチェックしない
        if ($context['newRecord']) {
            return true;
        }

        if (!$validator->isValid('usage_time_from')) {
            return false;
        }
        $from = Hash::get($data, 'usage_time_from');

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        if (Validation::notBlank($from) && CustomValidation::integer($from)) {
            $options['event_id'] = Hash::get($data, 'id');
            // 日は分に変換
            if ((string)Hash::get($context['data'], 'type') === (string)Event::TYPE_DAY) {
                $from = $from * 24 * 60;
                $val = $val * 24 * 60;
            }

            if ($val > static::UNIT_TIME_MAX) {
                $options['usageTimeFrom'] = $from;
                $options['usageTimeTo'] = $val;

                $query = $reservationsTable->find('count', [
                    'inputs' => $options,
                ]);
                if ($query->count() >= 1) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('form_pattern_id')
            ->value('id')
            ->value('type', [
                'multiValue' => true,
            ])
            ->value('id', [
                'multiValue' => true,
            ])
            ->value('public_flg', [
                'multiValue' => true,
            ])
            ->value('event_id', [
                'fields' => 'id',
            ])
            ->like('event_name', [
                'before' => true,
                'after' => true,
                'fields' => 'name',
            ])
            ->callback('event_tags', [
                'callback' => function (Query $query, array $args) {
                    $in = [];
                    foreach ($args['event_tags'] as $tags) {
                        if ($tags['tag_id'] !== '0') {
                            $in[] = $tags['tag_id'];
                        }
                    }

                    if (empty($in)) {
                        return false;
                    }

                    /** @var \App\Model\Table\EventTagsTable $eventTagsTable */
                    $eventTagsTable = $this->getAssociation('EventTags')->getTarget();
                    $subQuery = $eventTagsTable->find()->select(['event_id'])
                        ->distinct(['event_id'])
                        ->where(['EventTags.tag_id IN' => $in]);

                    $query->where(['Events.id IN' => $subQuery]);
                },
            ])
            ->callback('schedule_date_from', [
                'callback' => function (Query $query, $args, $filter) {
                    $query->where(['OR' => [
                        ['Events.date_to >=' => $args['schedule_date_from']],
                        ['Events.date_to IS NULL'],
                    ]]);
                }])
            ->callback('schedule_date_to', [
                'callback' => function (Query $query, $args, $filter) {
                    $to = new FrozenDate($args['schedule_date_to']);

                    $query->where(['OR' => [
                        ['Events.date_from <' => $to->addDays(1)],
                        ['Events.date_from IS NULL'],
                    ]]);
                }])
            ->callback('public_from', [
                'callback' => function (Query $query, $args, $filter) {
                    $query->where(['OR' => [
                        'Events.public_to >=' => $args['public_from'],
                        'Events.public_to IS NULL',
                    ]]);
                }])
            ->callback('public_to', [
                'callback' => function (Query $query, $args, $filter) {
                    $publicTo = new FrozenDate($args['public_to']);

                    $query->where(['OR' => [
                        'Events.public_from <' => $publicTo->addDays(1),
                        'Events.public_from IS NULL',
                    ]]);
                }])
            ->like('name', [
                'before' => true,
                'after' => true,
            ])
            ->value('organizer_id')
            ->like('event_smart_lock.smart_lock_device_key', [
                'before' => true,
                'after' => true,
                'fields' => 'EventSmartLocks.smart_lock_device_key',
            ]);

        $this->searchManager()->useCollection('calendar');
        $this->searchManager()
            ->value('id', [
                'multiValue' => true,
            ])
            ->like('name', [
                'before' => true,
                'after' => true,
            ])
            ->callback('public_from', [
                'callback' => function (Query $query, $args, $filter) {
                    $query->where([
                        'OR' => [
                            'Events.public_to >=' => $args['public_from'],
                            'Events.public_to IS NULL',
                        ],
                    ]);
                },
            ])
            ->callback('public_to', [
                'callback' => function (Query $query, $args, $filter) {
                    $publicTo = new FrozenDate($args['public_to']);

                    $query->where([
                        'OR' => [
                            'Events.public_from <' => $publicTo->addDays(1),
                            'Events.public_from IS NULL',
                        ],
                    ]);
                },
            ])
            ->value('public_flg', [
                'multiValue' => true,
            ])
            ->value('type', [
                'multiValue' => true,
            ])
            ->callback('calendar_type', [
                'callback' => function ($query, $args, $filter) {
                    if (isset($args[$filter->name()])) {
                        $calendarType = $args[$filter->name()];

                        $query->where([
                            $this->driverExpression()->jsonArrayContains(
                                'Events.format_type_display',
                                (string)$calendarType
                            ),
                        ]);
                    }
                },
            ]);
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
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        if (isset($options['contain']) && is_array($options['contain'])) {
            $query->contain($options['contain']);
        }
        $query->select([
            'id',
            'label_id',
            'name',
            'date_from',
            'date_to',
            'public_flg',
            'public_from',
            'time_from',
            'time_to',
            'sort_no',
            'public_to',
            'created',
            'modified',
        ])
        ->contain([
            'Labels' => [
                'fields' => ['id', 'name', 'parent_id'],
            ],
            'Organizers' => [
                'fields' => ['id', 'name', 'video_meeting_type'],
            ],
            'EventPlans' => [
                'fields' => ['id', 'name', 'event_id'],
            ],
            'EventSmartLocks' => [
                'fields' => ['id', 'event_id', 'smart_lock_device_key'],
            ],
        ]);

        $query->join([
            'Labels' => [
                'table' => 'labels',
                'type' => 'LEFT',
                'conditions' => 'Labels.id = Events.label_id',
            ],
        ]);
        $labelsTable->joinQuery($query, 'Labels');

        $labelId = Hash::get($options, 'inputs.label_id');
        if (isset($labelId) && $labelId !== '') {
            $labelsTable->addNestWhere($query, (int)$labelId);
        }

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));
        if ($sort === 'organizer_id') {
            $query->order([
                'Organizers.sort_key' => $direction,
                'Organizers.name' => $direction,
                'Organizers.id' => $direction,
                'Events.id' => $direction,
            ], true);
        } elseif ($sort === 'smart_lock_device_key') {
            $query->order([
                'EventSmartLocks.' . $sort => $direction,
                'Events.id' => $direction,
            ], true);
        } else {
            $query->order([
                'Events.' . $sort => $direction,
            ] + [
                'Events.id' => $direction,
            ], true);
        }

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * まとめて編集対象を取得
     *
     * @param array|string|null $checked チェック情報
     * @param array|string|null $searchInputs 検索条件
     * @return array
     */
    public function getTogetherEditEvents($checked, $searchInputs = null)
    {
        $result = [];
        $contain = [
            'EventStockMarks',
            'EventTags',
            'EventImages',
            'EventRemarks',
            'EventWeeks',
            'EventPlans',
            'EventSmartLocks',
        ];

        if (empty($checked) || !is_array($checked)) {
            return $result;
        }

        if (!is_array($searchInputs)) {
            $searchInputs = [];
        }

        $searchInputs['sort'] = 'id';
        $allCheck = Hash::get($checked, 'allCheck', false);
        if ((string)$allCheck === (string)Configure::readOrFail('Master.common.listCheckId.check')) {
            $query = $this->findSearchList($this->find('all'), ['inputs' => $searchInputs, 'contain' => $contain]);
        } else {
            $query = $this->findSearchList($this->find('all'), ['inputs' => ['id' => $checked], 'contain' => $contain]);
        }
        $query->select([], true);

        return $query->toArray();
    }

    /**
     * まとめて編集の関連データを保存
     *
     * @param \Cake\Datasource\EntityInterface $event エンティティ
     * @param array $inputs 入力値
     * @return \Cake\Datasource\EntityInterface
     */
    public function saveAssociationForTogether(EntityInterface $event, $inputs)
    {
        if ((string)Hash::get($inputs, 'stock_display_type') === (string)Event::STOCK_DISPLAY_TYPE_ICON) {
            $eventStockMarks = $event->get('event_stock_marks');
            foreach (array_keys($inputs['event_stock_marks']) as $key) {
                if (isset($eventStockMarks[$key])) {
                    $inputs['event_stock_marks'][$key]['id'] = $eventStockMarks[$key]->id;
                }
                $inputs['event_stock_marks'][$key]['event_id'] = $event->id;
            }
        } else {
            $inputs['event_stock_marks'] = [];
        }

        $patchInput['event_stock_marks'] = $inputs['event_stock_marks'];

        $event = $this->patchEntity($event, $patchInput, [
            'validate' => false,
            'associated' => [
                'EventStockMarks' => [],
            ],
        ]);

        return $event;
    }

    /**
     * 締め切り日の時間選択
     *
     * @return mixed
     */
    protected function getDeadlineTimeList()
    {
        $deadlineTime = Configure::readOrFail('Master.event.deadlineTime');

        return DateTimeUtility::createTimeList(
            $deadlineTime['min'],
            $deadlineTime['max'],
            $deadlineTime['interval'],
            $deadlineTime['suffix']
        );
    }

    /**
     * 実施日の判定
     *
     * @param mixed $val 値
     * @param array $context context
     * @return bool
     */
    protected function checkReserveDate($val, $context)
    {
        // 編集の場合のみ
        if ($context['newRecord']) {
            return true;
        }

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $reserveData = $reservationsTable->find('minMaxDateTime', [
            'allStatus' => true,
            'inputs' => [
                'event_id' => $context['data']['id'],
            ],
        ])->first();

        // 予約データが存在しない場合はエラーチェックを実施しない
        if (is_null($reserveData)) {
            return true;
        }

        switch ($context['field']) {
            case 'date_from':
                $date = new FrozenDate($val);
                $checkDate = $date->i18nFormat('yyyy-MM-dd');

                if (
                    $checkDate <= $reserveData['min']->i18nFormat('yyyy-MM-dd')
                    && $checkDate <= $reserveData['max']->i18nFormat('yyyy-MM-dd')
                ) {
                    return true;
                }

                break;
            case 'date_to':
                $date = new FrozenDate($val);
                $checkDate = $date->i18nFormat('yyyy-MM-dd');

                $max = $reserveData['max'];
                /** @var \App\Model\Entity\Event $event */
                $event = $this->find()
                    ->select(['id', 'time_from', 'time_to'])
                    ->where(['id' => $context['data']['id']])
                    ->firstOrFail();
                if (new FrozenTime($event->get('time_from')) >= new FrozenTime($event->get('time_to'))) {
                    $max = clone $max;
                    $max = $max->subDays(1);
                }

                if (
                    $checkDate >= $reserveData['min']->i18nFormat('yyyy-MM-dd')
                    && $checkDate >= $max->i18nFormat('yyyy-MM-dd')
                ) {
                    return true;
                }

                break;
            case 'time_to':
                $options['event_id'] = $context['data']['id'];
                $reserveCountQuery = $reservationsTable->find('count', [
                    'inputs' => $options,
                ]);
                $reserveCount = $reserveCountQuery->count();

                $options['time_from'] = $context['data']['time_from'];
                $options['time_to'] = $context['data']['time_to'];
                $editCountQuery = $reservationsTable->find('count', [
                    'inputs' => $options,
                ]);
                $editCount = $editCountQuery->count();

                if ($reserveCount === $editCount) {
                    return true;
                }

                break;
        }

        return false;
    }

    /**
     * 時間の倍数チェック
     *
     * @param mixed $val 入力値
     * @param array $context context
     * @param \Kuchen\Validation\Validation\Validator $validator バリデーター
     * @return bool|string|null
     */
    protected function checkMultipleNumTime($val, $context, KuchenValidator $validator)
    {
        // Day版はチェックしない
        if ($validator->isValid('type') && (string)$context['data']['type'] === (string)Event::TYPE_DAY) {
            return true;
        }

        $input = $context['data'];

        $timeFrom = Hash::get($input, 'time_from');
        $timeTo = Hash::get($input, 'time_to');
        $eventUnitTime = Hash::get($input, 'event_unit_time');

        if (!Validation::naturalNumber($eventUnitTime)) {
            return false;
        }

        if (CustomValidation::time24h($timeFrom, $context) && CustomValidation::time24h($timeTo, $context)) {
            $timeFromObj = new FrozenTime($timeFrom);
            $timeToObj = new FrozenTime($timeTo);
            $diffTime = $timeFromObj->diff($timeToObj);

            //反転している場合は24時間から引く
            if ($diffTime->invert === 1) {
                $diffTime = 24 * 60 - ($diffTime->h * 60) + $diffTime->i;
            } elseif ($diffTime->invert === 0 && $diffTime->h === 0 && $diffTime->i === 0) {
                $diffTime = 24 * 60;
            } else {
                $diffTime = ($diffTime->h * 60) + $diffTime->i;
            }

            if ($diffTime % (int)$eventUnitTime != 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * 初期遷移時のEntityを整形
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param array $tagList tag
     * @return void
     */
    public function formatDefault(EntityInterface $entity, array $tagList)
    {
        $tags = $entity->get('event_tags');

        $entityTags = array_column($tags, 'tag_id');
        $eventTags = null;
        $i = 0;
        foreach ($tagList as $tag) {
            foreach (array_keys($tag['tag']) as $tagKey) {
                $search = ArrayUtility::arraySearch($tagKey, $entityTags);
                if ($search !== false) {
                    $eventTags[$i] = $tags[$search];
                } else {
                    $eventTags[$i] = [];
                }
                $i++;
            }
        }
        $entity->set('event_tags', $eventTags);

        if ($entity->get('stock_display_type') === Event::STOCK_DISPLAY_TYPE_NUMBER) {
            $inputs['event_stock_marks'][0]['number'] = 0;
            $inputs['event_stock_marks'][1]['number'] = 1;
            $entity->set('event_stock_marks', $inputs['event_stock_marks']);
        }

        $eventWeeks = $entity->get('event_weeks');
        if ($eventWeeks !== null) {
            /** @var \App\Model\Table\EventWeeksTable $eventWeeksTable */
            $eventWeeksTable = $this->getTableLocator()->get('EventWeeks');

            $formatEntityWeeks = $eventWeeksTable->formatDefaultWeeks($eventWeeks);
            $entity->set('event_weeks', $formatEntityWeeks);
        }

        $eventHolidays = $entity->get('event_holidays');
        if ($eventHolidays !== null) {
            /** @var \App\Model\Table\EventHolidaysTable $eventHolidaysTable */
            $eventHolidaysTable = $this->getTableLocator()->get('EventHolidays');
            $eventHolidaysTable->formatDefault($eventHolidays);
        }

        $eventStockSettings = $entity->get('event_stock_settings');
        if ($eventStockSettings !== null) {
            /** @var \App\Model\Table\EventStockSettingsTable $eventStockSettingsTable */
            $eventStockSettingsTable = $this->getTableLocator()->get('EventStockSettings');

            foreach ($eventStockSettings as $eventStockSetting) {
                $eventStockSettingsTable->formatDefault($eventStockSetting);
            }
        }
    }

    /**
     * 料金をまとめて変更できるか
     *
     * @param array $eventList 予約枠リスト
     * @return bool 変更可否
     */
    public function isChangeCharge(array $eventList)
    {
        if (
            ArrayUtility::arraySearch(Event::PLAN_MULTIPLE, array_column($eventList, 'time_plan'))
            !== false
        ) {
            return false;
        }

        return true;
    }

    /**
     * CSV要のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCsv(Query $query, array $options)
    {
        $query = $query->find('searchList', $options);
        $query->select(array_values($this->getSchema()->columns()), true);
        $query->contain([
            'Labels' => [
                'fields' => ['id', 'name'],
            ],
            'ColorChips' => [
                'fields' => ['id', 'name'],
            ],
            'EventWeeks' => [
                'fields' => ['id', 'event_id', 'week'],
            ],
            'EventTags' => [
                'Tags' => [
                    'fields' => ['id', 'name'],
                ],
            ],
            'EventStockMarks' => [
                'fields' => ['id', 'event_id', 'number', 'symbolic'],
            ],
            'EventImages' => [
                'fields' => ['id', 'event_id', 'url'],
            ],
            'EventRemarks' => [
                'fields' => ['id', 'event_id', 'name', 'form_item_id', 'detail_display_flg', 'remark'],
            ],
            'EventPlans' => [
                'fields' => ['id', 'event_id', 'name', 'usage_time', 'usage_day', 'charge', 'public_flg'],
            ],
            'FormPatterns' => [
                'fields' => ['id', 'name'],
            ],
            'EventSmartLocks' => [
                'fields' => ['id', 'smart_lock_device_key', 'smart_lock_key_url_flg'],
            ],
        ]);

        return $query;
    }

    /**
     * CSVファイルを生成
     *
     * @param array $searchCondition 検索条件
     * @param string $finder ファインダー
     * @param array $options オプション
     * @return callable
     */
    public function createCsv(array $searchCondition, string $finder = 'csv', array $options = [])
    {
        $header = $this->generateCsvHeader();
        $query = $this->find($finder, $options + [
                'inputs' => $searchCondition,
            ]);

        $callback = $this->getCsvStreamCallback($header, function () use ($header, $query) {
            foreach ($query as $event) {
                yield $this->generateCsvData($event, [
                    'event' => $event,
                    'header' => $header,
                ]);
            }
        });

        return $callback;
    }

    /**
     * フォーマットダウンロード用CSV
     *
     * @return string
     */
    public function createSampleCsv()
    {
        $header = $this->generateCsvHeader();

        $filePath = $this->createCsvFile($header, function () {
            yield $this->getCsvSample();
        });

        return $filePath;
    }

    /**
     * CSVへ出力するデータを生成
     *
     * @param string $column カラム
     * @param array $options オプション
     * @return string データ
     */
    public function formatCsvData(string $column, array $options)
    {
        /** @var \App\Model\Entity\Event $event */
        $event = $options['event'];
        $valueOptions = $this->getFieldValueOptions();

        $data = $event->get($column);
        $value = '';
        if (!is_null($data)) {
            switch ($column) {
                case static::CSV_COLUMN_EVENT_TAGS:
                    /** @var \App\Model\Table\EventTagsTable $eventTags */
                    $eventTags = $this->getTableLocator()->get('EventTags');
                    $value = $eventTags->generateCsvData($data, $options);

                    break;
                case static::CSV_COLUMN_EVENT_WEEKS:
                    /** @var \App\Model\Table\EventWeeksTable $eventWeeks */
                    $eventWeeks = $this->getTableLocator()->get('EventWeeks');
                    $value = $eventWeeks->generateCsvData($data, $options);
                    break;
                case static::CSV_COLUMN_LABEL_ID:
                    $value = $this->csvFormat()->csvForId($data, $event->get('label')->get('name'));
                    break;
                case static::CSV_COLUMN_TYPE:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['type'][$data]);
                    break;
                case static::CSV_COLUMN_FORM_PATTERN_ID:
                    $value = $this->csvFormat()->csvForId($data, $event->get('form_pattern')->get('name'));
                    break;
                case static::CSV_COLUMN_DATE_FROM:
                case static::CSV_COLUMN_DATE_TO:
                    $value = $this->csvFormat()->csvForDate($data);

                    break;
                case static::CSV_COLUMN_PUBLIC_FROM:
                case static::CSV_COLUMN_PUBLIC_TO:
                    $value = $this->csvFormat()->csvForTimestamp($data);
                    break;
                case static::CSV_COLUMN_CREATED:
                case static::CSV_COLUMN_MODIFIED:
                    $value = $this->csvFormat()->csvForTimestamp($data, 'timestampFormatFull');
                    break;
                case static::CSV_COLUMN_TIME_FROM:
                case static::CSV_COLUMN_TIME_TO:
                case static::CSV_COLUMN_RECEPTION_PERIOD_TIME:
                case static::CSV_COLUMN_REGISTRATION_DEADLINE_TIME:
                case static::CSV_COLUMN_EDITING_DEADLINE_TIME:
                case static::CSV_COLUMN_CANCELLATION_DEADLINE_TIME:
                    $value = $this->csvFormat()->csvForTime($data);
                    break;
                case static::CSV_COLUMN_RESERVATION_STATUS_ID:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['reservationStatusId'][$data]);
                    break;
                case static::CSV_COLUMN_TIME_PLAN:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['plan'][$data]);
                    break;
                case static::CSV_COLUMN_MULTIPLE_TIME_PLAN_TYPE:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['multipleTimePlanType'][$data]);
                    break;

                case static::CSV_COLUMN_EVENT_PLANS:
                    /** @var \App\Model\Table\EventPlansTable $eventPlans */
                    $eventPlans = $this->getTableLocator()->get('EventPlans');
                    $header = [
                        'asHeader' => array_keys(
                            Configure::readOrFail(
                                'Setting.csv.download.event.associationsHeader.' . static::CSV_COLUMN_EVENT_PLANS
                            )
                        ),
                    ];
                    $value = $eventPlans->generateCsvData(
                        $data,
                        $options + $header
                    );

                    break;
                case static::CSV_COLUMN_EVENT_IMAGES:
                    /** @var \App\Model\Table\EventImagesTable $eventImages */
                    $eventImages = $this->getTableLocator()->get('EventImages');
                    $value = $eventImages->generateCsvData($data);

                    break;
                case static::CSV_COLUMN_BACKGROUND_COLOR_TYPE:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['backgroundColorType'][$data]);
                    break;
                case static::CSV_COLUMN_BACKGROUND_COLOR_REPLACE_FRONT:
                case static::CSV_COLUMN_BACKGROUND_COLOR_REPLACE_ADMIN:
                    $multiple = [];

                    foreach ($data as $bcr) {
                        $multiple[] = $this->csvFormat()->csvForId($bcr, $valueOptions['backgroundColorReplace'][$bcr]);
                    }

                    $value = $this->csvFormat()->csvForMultiple($multiple);
                    break;
                case static::CSV_COLUMN_STOCK_DISPLAY_TYPE:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['stockDisplayType'][$data]);
                    break;
                case static::CSV_COLUMN_EVENT_STOCK_MARKS:
                    /** @var \App\Model\Table\EventStockMarksTable $eventStockMarks */
                    $eventStockMarks = $this->getTableLocator()->get('EventStockMarks');
                    $value = $eventStockMarks->generateCsvData($data);

                    break;
                case static::CSV_COLUMN_USAGE_TIME_NOTATION:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['usageTimeNotation'][$data]);
                    break;
                case static::CSV_COLUMN_REGISTRATION_DEADLINE_TYPE:
                case static::CSV_COLUMN_EDITING_DEADLINE_TYPE:
                case static::CSV_COLUMN_CANCELLATION_DEADLINE_TYPE:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['deadlineType'][$data]);
                    break;
                case static::CSV_COLUMN_WAITING_CANCELLATION_FLG:
                case static::CSV_COLUMN_DUPLICATION_CHECK_FLG:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['common'][$data]);
                    break;

                case static::CSV_COLUMN_PUBLIC_FLG:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['publicFlg'][$data]);
                    break;

                case static::CSV_COLUMN_FORMAT_TYPE_DISPLAY:
                    $multiple = [];
                    foreach ($data as $formatTypeDisplay) {
                        $multiple[] = $this->csvFormat()->csvForId(
                            $formatTypeDisplay,
                            $valueOptions['formatTypeDisplay'][$formatTypeDisplay]
                        );
                    }

                    $value = $this->csvFormat()->csvForMultiple($multiple);
                    break;

                case static::CSV_COLUMN_EVENT_REMARKS:
                    /** @var \App\Model\Table\EventRemarksTable $eventRemarks */
                    $eventRemarks = $this->getTableLocator()->get('EventRemarks');
                    $header = [
                        'asHeader' => array_keys(
                            Configure::readOrFail(
                                'Setting.csv.download.event.associationsHeader.' . static::CSV_COLUMN_EVENT_REMARKS
                            )
                        ),
                    ];
                    $value = $eventRemarks->generateCsvData(
                        $data,
                        $options + $header
                    );

                    break;

                case static::CSV_COLUMN_ORGANIZER_ID:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['organizers'][$data]);
                    break;

                case static::CSV_COLUMN_QR_CODE_FLG:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['qrCodeFlg'][$data]);
                    break;

                case static::CSV_COLUMN_REGISTRATION_DEADLINE_CRITERION:
                case static::CSV_COLUMN_EDITING_DEADLINE_CRITERION:
                case static::CSV_COLUMN_CANCELLATION_DEADLINE_CRITERION:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['deadlineCriterion'][$data]);
                    break;

                case static::CSV_COLUMN_EVENT_SMART_LOCK:
                    $asHeaders = Configure::readOrFail(
                        'Setting.csv.download.event.associationsHeader.' . static::CSV_COLUMN_EVENT_SMART_LOCK
                    );
                    /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
                    $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
                    if (!$systemSettingsTable->getData()->useSmartLock()) {
                        break;
                    }
                    $smartLock = new SmartLockLinkage();
                    if ($smartLock->useRemoteLock()) {
                        $asHeader = $asHeaders['remoteLock'];
                    } elseif ($smartLock->useAkerun()) {
                        $asHeader = $asHeaders['akerun'];
                    } else {
                        break;
                    }
                    $header = ['asHeader' => array_keys($asHeader)];
                    /** @var \App\Model\Table\EventSmartLocksTable $eventSmartLocks */
                    $eventSmartLocks = $this->getTableLocator()->get('EventSmartLocks');
                    $value = $eventSmartLocks->generateCsvData(
                        $data,
                        $options + $header
                    );

                    break;
                default:
                    //整形不要なものはそのまま
                    $value = $data;
                    break;
            }
        }

        return $value;
    }

    /**
     * CSVのヘッダを生成
     *
     * @return array ヘッダ
     */
    public function generateCsvHeader()
    {
        $header = [];
        $headerTemplate = Configure::readOrFail('Setting.csv.download.event.header');
        $headerColumnId = Configure::readOrFail('Setting.csv.download.event.headerColumnId');

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if (!$systemSettingsTable->getData()->canCoordinateVideoMeeting()) {
            // ビデオ会議連携のカラムを削除
            unset($headerTemplate[EventsTable::CSV_COLUMN_ORGANIZER_ID]);
            unset($headerColumnId[EventsTable::CSV_COLUMN_ORGANIZER_ID]);
        }

        foreach (array_keys($headerTemplate) as $column) {
            $header[$column] = $this->csvFormat()->csvHeader(
                Hash::get($headerTemplate, (string)$column),
                $headerColumnId[$column]
            );
        }
        $smartLock = new SmartLockLinkage();
        $smartLockHeader = Configure::readOrFail('Setting.csv.download.event.eventSmartLockHeader');
        if ($smartLock->useRemoteLock()) {
            $replacement = $smartLockHeader['remoteLock'];
        } elseif ($smartLock->useAkerun()) {
            $replacement = $smartLockHeader['akerun'];
        }
        if (isset($replacement)) {
            $header[static::CSV_COLUMN_EVENT_SMART_LOCK] = (string)preg_replace(
                '/%CSV_COLUMN_NAME%/',
                $replacement,
                $header[static::CSV_COLUMN_EVENT_SMART_LOCK]
            );
        } else {
            unset($header[static::CSV_COLUMN_EVENT_SMART_LOCK]);
        }

        return $header;
    }

    /**
     * CSVへ出力するデータを生成
     *
     * @param \Cake\ORM\Entity $csvItems 出力項目
     * @param array $options オプション
     * @return array データ
     */
    public function generateCsvData(Entity $csvItems, array $options = [])
    {
        $data = [];
        foreach (array_keys($options['header']) as $column) {
            $data[$column] = $this->formatCsvData((string)$column, $options);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    protected function getCsvSample()
    {
        $valueOptions = $this->getFieldValueOptions();

        $description = Configure::readOrFail('Setting.csv.import.sample');
        $header = $this->generateCsvHeader();

        $sample = [];
        foreach (array_keys($header) as $column) {
            switch ($column) {
                case static::CSV_COLUMN_EVENT_TAGS:
                    /** @var \App\Model\Table\TagsTable $tags */
                    $tags = $this->getTableLocator()->get('Tags');
                    $tagList = $tags->find('list', [
                        'keyField' => 'id',
                        'valueField' => 'name',
                    ])->toArray();

                    $value = $description['hasMany'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(array_keys($tagList), $tagList);
                    break;
                case static::CSV_COLUMN_EVENT_WEEKS:
                    $value = $description['hasMany'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['week']),
                            $valueOptions['week']
                        );
                    break;
                case static::CSV_COLUMN_LABEL_ID:
                    /** @var \App\Model\Table\LabelsTable $labels */
                    $labels = $this->getTableLocator()->get('Labels');
                    $labelList = $labels->getAdminLabelIdList();

                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(array_keys($labelList), $labelList);
                    break;
                case static::CSV_COLUMN_TYPE:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(array_keys($valueOptions['type']), $valueOptions['type']);
                    break;
                case static::CSV_COLUMN_FORM_PATTERN_ID:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['reservationFormPatternId']),
                            $valueOptions['reservationFormPatternId']
                        );
                    break;
                case static::CSV_COLUMN_DATE_FROM:
                case static::CSV_COLUMN_DATE_TO:
                case static::CSV_COLUMN_PUBLIC_FROM:
                case static::CSV_COLUMN_PUBLIC_TO:
                    $value = $description['date'];
                    break;
                case static::CSV_COLUMN_ID:
                case static::CSV_COLUMN_CREATED:
                case static::CSV_COLUMN_MODIFIED:
                    $value = $description['noValues'];
                    break;
                case static::CSV_COLUMN_TIME_FROM:
                case static::CSV_COLUMN_TIME_TO:
                    $value = $description['time'];
                    break;
                case static::CSV_COLUMN_REGISTRATION_DEADLINE_NUMBER:
                case static::CSV_COLUMN_EDITING_DEADLINE_NUMBER:
                case static::CSV_COLUMN_CANCELLATION_DEADLINE_NUMBER:
                    $value = $description['eventDeadlineNumber'];
                    break;
                case static::CSV_COLUMN_REGISTRATION_DEADLINE_TIME:
                case static::CSV_COLUMN_EDITING_DEADLINE_TIME:
                case static::CSV_COLUMN_CANCELLATION_DEADLINE_TIME:
                    $value = $description['eventDeadlineTime'];
                    break;
                case static::CSV_COLUMN_RESERVATION_STATUS_ID:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['reservationStatusId']),
                            $valueOptions['reservationStatusId']
                        );
                    break;
                case static::CSV_COLUMN_TIME_PLAN:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(array_keys($valueOptions['plan']), $valueOptions['plan']);
                    break;
                case static::CSV_COLUMN_MULTIPLE_TIME_PLAN_TYPE:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['multipleTimePlanType']),
                            $valueOptions['multipleTimePlanType']
                        );
                    break;
                case static::CSV_COLUMN_EVENT_PLANS:
                    $value = $description['eventPlans'];
                    $value .= "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['publicFlg']),
                            $valueOptions['publicFlg']
                        );

                    break;
                case static::CSV_COLUMN_EVENT_IMAGES:
                    $value = $description['eventImages'];
                    break;
                case static::CSV_COLUMN_BACKGROUND_COLOR_TYPE:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['backgroundColorType']),
                            $valueOptions['backgroundColorType']
                        );
                    break;
                case static::CSV_COLUMN_BACKGROUND_COLOR_REPLACE_FRONT:
                case static::CSV_COLUMN_BACKGROUND_COLOR_REPLACE_ADMIN:
                    $value = $description['eventMultipleWaku'] . "\n" .
                        $this->csvFormat()->csvForMultipleId(
                            array_keys($valueOptions['backgroundColorReplace']),
                            $valueOptions['backgroundColorReplace']
                        );
                    break;
                case static::CSV_COLUMN_STOCK_DISPLAY_TYPE:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['stockDisplayType']),
                            $valueOptions['stockDisplayType']
                        );
                    break;
                case static::CSV_COLUMN_EVENT_STOCK_MARKS:
                    /** @var \App\Model\Table\EventStockMarksTable $eventStockMarks */
                    $eventStockMarks = $this->getTableLocator()->get('EventStockMarks');
                    $value = $description['stockMarks'];
                    $symbolicDisp = $eventStockMarks->getFieldValueOptions('symbolicDisp');
                    $value .= "\n" . $this->csvFormat()->csvForHasManyId(array_keys($symbolicDisp), $symbolicDisp);

                    break;
                case static::CSV_COLUMN_USAGE_TIME_NOTATION:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['usageTimeNotation']),
                            $valueOptions['usageTimeNotation']
                        );
                    break;
                case static::CSV_COLUMN_REGISTRATION_DEADLINE_TYPE:
                case static::CSV_COLUMN_EDITING_DEADLINE_TYPE:
                case static::CSV_COLUMN_CANCELLATION_DEADLINE_TYPE:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['deadlineType']),
                            $valueOptions['deadlineType']
                        );
                    break;
                case static::CSV_COLUMN_WAITING_CANCELLATION_FLG:
                case static::CSV_COLUMN_DUPLICATION_CHECK_FLG:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['common']),
                            $valueOptions['common']
                        );
                    break;

                case static::CSV_COLUMN_PUBLIC_FLG:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['publicFlg']),
                            $valueOptions['publicFlg']
                        );
                    break;
                case static::CSV_COLUMN_COLOR_CHIP_ID:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['colorChip']),
                            $valueOptions['colorChip']
                        );
                    break;

                case static::CSV_COLUMN_FORMAT_TYPE_DISPLAY:
                    $value = $description['eventMultipleWaku'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['formatTypeDisplay']),
                            $valueOptions['formatTypeDisplay']
                        );
                    break;

                case static::CSV_COLUMN_EVENT_REMARKS:
                    $header = Configure::readOrFail(
                        'Setting.csv.download.event.associationsHeader.' . static::CSV_COLUMN_EVENT_REMARKS
                    );
                    $value = $description['hasMany'] . "\n" . $description['association'];
                    $value .= "\n" . $header[EventRemarksTable::CSV_COLUMN_DETAIL_DISPLAY_FLG];
                    $value .= "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['publicFlg']),
                            $valueOptions['publicFlg']
                        );
                    $value .= "\n" . $header[EventRemarksTable::CSV_COLUMN_FORM_ITEM_ID];
                    $value .= "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['formItemId']),
                            $valueOptions['formItemId']
                        );

                    break;
                case static::CSV_COLUMN_EVENT_UNIT_TIME:
                    $value = $description['eventUnitTime'];
                    break;
                case static::CSV_COLUMN_USAGE_UNIT_TIME:
                    $value = $description['eventUsageUnitTime'];
                    break;
                case static::CSV_COLUMN_USAGE_TIME_FROM:
                    $value = $description['eventUsageUnitTimeFrom'];
                    break;
                case static::CSV_COLUMN_USAGE_TIME_TO:
                    $value = $description['eventUsageUnitTimeTo'];
                    break;
                case static::CSV_COLUMN_USAGE_UNIT_DAY:
                    $value = $description['eventUsageUnitDay'];
                    break;
                case static::CSV_COLUMN_USAGE_DAY_FROM:
                    $value = $description['eventUsageUnitDayFrom'];
                    break;
                case static::CSV_COLUMN_USAGE_DAY_TO:
                    $value = $description['eventUsageUnitDayTo'];
                    break;
                case static::CSV_COLUMN_INTERVAL_TIME:
                    $value = $description['eventIntervalTime'];
                    break;
                case static::CSV_COLUMN_INTERVAL_DAY:
                    $value = $description['eventIntervalDay'];
                    break;
                case static::CSV_COLUMN_STOCK_RANGE_FROM:
                    $value = $description['eventStockRangeFrom'];
                    break;
                case static::CSV_COLUMN_STOCK_RANGE_TO:
                    $value = $description['eventStockRangeTo'];
                    break;
                case static::CSV_COLUMN_RECEPTION_PERIOD_NUMBER:
                    $value = $description['eventReceptionPeriodNumber'];
                    break;
                case static::CSV_COLUMN_RECEPTION_PERIOD_TIME:
                    $value = $description['eventReceptionPeriodTime'];
                    break;
                case static::CSV_COLUMN_RESERVATION_LIMIT_ALL:
                case static::CSV_COLUMN_RESERVATION_LIMIT_DAY:
                case static::CSV_COLUMN_RESERVATION_LIMIT_FUTURE:
                case static::CSV_COLUMN_RESERVATION_LIMIT_MONTH:
                    $value = $description['optionalNumber'];
                    break;

                case static::CSV_COLUMN_CHARGE:
                    $value = $description['eventCharge'];
                    break;
                case static::CSV_COLUMN_STOCK_UNIT:
                    $value = $description['stockUnit'];
                    break;
                case static::CSV_COLUMN_STOCK:
                    $value = $description['number'];
                    break;
                case static::CSV_COLUMN_SORT_NO:
                    $value = $description['eventSortNo'];
                    break;
                case static::CSV_COLUMN_DESCRIPTION:
                    $value = $description['optional'];
                    break;
                case static::CSV_COLUMN_NAME:
                    $value = $description['eventName'];
                    break;
                case static::CSV_COLUMN_QR_CODE_FLG:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['qrCodeFlg']),
                            $valueOptions['qrCodeFlg']
                        );
                    break;
                case static::CSV_COLUMN_REGISTRATION_DEADLINE_CRITERION:
                case static::CSV_COLUMN_EDITING_DEADLINE_CRITERION:
                case static::CSV_COLUMN_CANCELLATION_DEADLINE_CRITERION:
                    $value = $description['belongsTo'] . "\n" .
                        $this->csvFormat()->csvForHasManyId(
                            array_keys($valueOptions['deadlineCriterion']),
                            $valueOptions['deadlineCriterion']
                        );
                    break;
                case static::CSV_COLUMN_EVENT_SMART_LOCK:
                    $headers = Configure::readOrFail(
                        'Setting.csv.download.event.associationsHeader.' . static::CSV_COLUMN_EVENT_SMART_LOCK
                    );
                    /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
                    $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
                    if (!$systemSettingsTable->getData()->useSmartLock()) {
                        $value = '';
                        break;
                    }
                    $smartLock = new SmartLockLinkage();
                    if ($smartLock->useRemoteLock()) {
                        $header = $headers['remoteLock'];
                    } elseif ($smartLock->useAkerun()) {
                        $header = $headers['akerun'];
                    } else {
                        $value = '';
                        break;
                    }

                    $value = $description['association'];
                    $value .= "\n" . $header[EventSmartLocksTable::CSV_COLUMN_SMART_LOCK_DEVICE_KEY];
                    $value .= "\n" . $description['values'];
                    if ($smartLock->useAkerun()) {
                        $value .= "\n" . $header[EventSmartLocksTable::CSV_COLUMN_SMART_LOCK_KEY_URL_FLG];
                        $value .= "\n" .
                            $this->csvFormat()->csvForHasManyId(
                                array_keys($valueOptions['common']),
                                $valueOptions['common']
                            );
                    }

                    break;
                default:
                    //整形不要なものはそのまま
                    $value = $description['values'];
                    break;
            }
            $sample[$column] = $value;
        }

        return $sample;
    }

    /**
     * 予約時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReservation(Query $query, array $options)
    {
        $adminFlg = $this->commonData()->existsAdminLoginData();

        $query->select([
            'id',
            'label_id',
            'name',
            'type',
            'date_from',
            'date_to',
            'time_from',
            'time_to',
            'event_unit_time',
            'time_plan',
            'multiple_time_plan_type',
            'usage_unit_time',
            'usage_time_from',
            'usage_time_to',
            'usage_unit_day',
            'usage_day_from',
            'usage_day_to',
            'usage_time_notation',
            'interval_time',
            'interval_day',
            'charge',
            'stock',
            'stock_range_from',
            'stock_range_to',
            'stock_unit',
            'stock_display_type',
            'public_flg',
            'public_from',
            'public_to',
            'reception_period_number',
            'reception_period_time',
            'registration_deadline_type',
            'registration_deadline_number',
            'registration_deadline_time',
            'registration_deadline_criterion',
            'editing_deadline_type',
            'editing_deadline_number',
            'editing_deadline_time',
            'editing_deadline_criterion',
            'cancellation_deadline_type',
            'cancellation_deadline_number',
            'cancellation_deadline_time',
            'cancellation_deadline_criterion',
            'reservation_status_id',
            'waiting_cancellation_flg',
            'reservation_limit_all',
            'reservation_limit_future',
            'reservation_limit_month',
            'reservation_limit_day',
            'duplication_check_flg',
            'organizer_id',
            'qr_code_flg',
        ]);

        $query->contain([
            'Labels' => [
                'fields' => [
                    'id',
                    'name',
                    'public_flg',
                ],
                'queryBuilder' => function ($labelQuery) use ($adminFlg) {
                    if (!$adminFlg) {
                        $labelQuery->where([
                            'Labels.public_flg' => Label::PUBLIC_FLG_ON,
                        ]);
                    }

                    return $labelQuery;
                },
            ],
            'EventWeeks' => [
                'fields' => [
                    'id',
                    'event_id',
                    'week',
                ],
            ],
            'EventStockSettings' => [
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
            'EventStockSettings.EventStockSettingWeeks' => [
                'fields' => [
                    'id',
                    'event_stock_setting_id',
                    'week',
                ],
            ],
            'EventStockSettings.EventStockSettingExcludeDates' => [
                'fields' => [
                    'id',
                    'event_stock_setting_id',
                    'date',
                ],
            ],
            'EventHolidays' => [
                'fields' => [
                    'id',
                    'event_id',
                    'date_from',
                    'date_to',
                    'time_from',
                    'time_to',
                ],
            ],
            'EventHolidays.EventHolidayWeeks' => [
                'fields' => [
                    'id',
                    'event_holiday_id',
                    'week',
                ],
            ],
            'EventHolidays.EventHolidayExcludeDates' => [
                'fields' => [
                    'id',
                    'event_holiday_id',
                    'date',
                ],
            ],
            'EventPlans' => [
                'fields' => [
                    'id',
                    'event_id',
                    'name',
                    'usage_time',
                    'usage_day',
                    'charge',
                    'public_flg',
                ],
            ],
            'EventTags' => [
                'fields' => [
                    'id',
                    'event_id',
                    'tag_id',
                ],
            ],
            'EventRemarks' => [
                'fields' => [
                    'id',
                    'event_id',
                    'name',
                    'form_item_id',
                    'remark',
                ],
            ],
            'EventSmartLocks' => [
                'fields' => [
                    'id',
                    'event_id',
                    'smart_lock_device_key',
                    'smart_lock_key_url_flg',
                ],
            ],
        ], true);

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

        $eventIds = [];
        foreach (Hash::get($options, 'reservations') as $reservation) {
            $eventIds[$reservation->get('event_id')] = $reservation->get('event_id');
        }
        $query->where([
            'Events.id IN' => $eventIds,
        ]);

        $query->epilog('FOR UPDATE');

        $query->enableHydration(false);
        $query->disableBufferedResults();

        return $query;
    }

    /**
     * カレンダー生成時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCalendar(Query $query, array $options)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        $adminFlg = $this->commonData()->existsAdminLoginData();

        $dateFrom = DateTimeUtility::convertToDateObject(Hash::get($options, 'inputs.date_from'));
        if (isset($dateFrom)) {
            $dateFrom = $dateFrom->subDays(1); // 日またぎの考慮
        }
        $dateTo = DateTimeUtility::convertToDateObject(Hash::get($options, 'inputs.date_to'));

        // インターバルの最大値で範囲を決定
        $intervalFrom = null;
        if (isset($dateFrom)) {
            $intervalFrom = new FrozenDate($dateFrom->format('Y-m-d'));
            $intervalFrom = $intervalFrom->subDays($this->getMaxInterval());
        }
        $intervalTo = null;
        if (isset($dateTo)) {
            $intervalTo = new FrozenDate($dateTo->format('Y-m-d'));
            $intervalTo = $intervalTo->addDays($this->getMaxInterval());
        }

        $query->select([
            'id',
            'label_id',
            'name',
            'type',
            'date_from',
            'date_to',
            'time_from',
            'time_to',
            'event_unit_time',
            'time_plan',
            'usage_unit_time',
            'usage_time_from',
            'usage_unit_day',
            'usage_day_from',
            'usage_time_notation',
            'interval_time',
            'interval_day',
            'stock',
            'stock_unit',
            'stock_display_type',
            'reception_period_number',
            'reception_period_time',
            'registration_deadline_type',
            'registration_deadline_number',
            'registration_deadline_time',
            'registration_deadline_criterion',
            'waiting_cancellation_flg',
            'background_color_type',
            'color_chip_id',
            'background_color_replace_front',
            'background_color_replace_admin',
        ]);

        $query->contain([
            'Labels' => [
                'fields' => [
                    'id',
                    'name',
                    'public_flg',
                ],
                'queryBuilder' => function ($labelQuery) use ($adminFlg) {
                    if (!$adminFlg) {
                        $labelQuery->where([
                            'Labels.public_flg' => Label::PUBLIC_FLG_ON,
                        ]);
                    }

                    return $labelQuery;
                },
            ],
            'EventWeeks' => [
                'fields' => [
                    'id',
                    'event_id',
                    'week',
                ],
            ],
            'EventStockMarks' => [
                'fields' => [
                    'id',
                    'event_id',
                    'number',
                    'symbolic',
                ],
            ],
            'EventStockSettings' => [
                'fields' => [
                    'id',
                    'event_id',
                    'date_from',
                    'date_to',
                    'time_from',
                    'time_to',
                    'stock',
                ],
                'queryBuilder' => function ($stockSettingQuery) use ($intervalFrom, $intervalTo) {
                    if (isset($intervalFrom)) {
                        $stockSettingQuery->where([
                            'OR' => [
                                'EventStockSettings.date_to IS NULL',
                                'EventStockSettings.date_to >=' => $intervalFrom,
                            ],
                        ]);
                    }
                    if (isset($intervalTo)) {
                        $stockSettingQuery->where([
                            'OR' => [
                                'EventStockSettings.date_from IS NULL',
                                'EventStockSettings.date_from <=' => $intervalTo,
                            ],
                        ]);
                    }

                    return $stockSettingQuery;
                },
            ],
            'EventStockSettings.EventStockSettingWeeks' => [
                'fields' => [
                    'id',
                    'event_stock_setting_id',
                    'week',
                ],
            ],
            'EventStockSettings.EventStockSettingExcludeDates' => [
                'fields' => [
                    'id',
                    'event_stock_setting_id',
                    'date',
                ],
            ],
            'EventHolidays' => [
                'fields' => [
                    'id',
                    'event_id',
                    'date_from',
                    'date_to',
                    'time_from',
                    'time_to',
                ],
                'queryBuilder' => function ($holidayQuery) use ($intervalFrom, $intervalTo) {
                    if (isset($intervalFrom)) {
                        $holidayQuery->where([
                            'OR' => [
                                'EventHolidays.date_to IS NULL',
                                'EventHolidays.date_to >=' => $intervalFrom,
                            ],
                        ]);
                    }
                    if (isset($intervalTo)) {
                        $holidayQuery->where([
                            'OR' => [
                                'EventHolidays.date_from IS NULL',
                                'EventHolidays.date_from <=' => $intervalTo,
                            ],
                        ]);
                    }

                    return $holidayQuery;
                },
            ],
            'EventHolidays.EventHolidayWeeks' => [
                'fields' => [
                    'id',
                    'event_holiday_id',
                    'week',
                ],
            ],
            'EventHolidays.EventHolidayExcludeDates' => [
                'fields' => [
                    'id',
                    'event_holiday_id',
                    'date',
                ],
            ],
            'EventPlans' => [
                'fields' => [
                    'id',
                    'event_id',
                    'usage_time',
                    'usage_day',
                    'public_flg',
                ],
            ],
        ], true);

        $query->join([
            'Labels' => [
                'table' => 'labels',
                'type' => 'LEFT',
                'conditions' => 'Labels.id = Events.label_id',
            ],
        ]);
        $labelsTable->joinQuery($query, 'Labels', [], null, !$adminFlg);

        if (isset($dateFrom)) {
            $query->where([
                'OR' => [
                    'Events.date_to IS NULL',
                    'Events.date_to >=' => $dateFrom,
                ],
            ]);
        }
        if (isset($dateTo)) {
            $query->where([
                'OR' => [
                    'Events.date_from IS NULL',
                    'Events.date_from <=' => $dateTo,
                ],
            ]);
        }

        if (!$adminFlg) {
            $query->where([
                'Events.public_flg' => Event::PUBLIC_FLG_ON,
                [
                    'OR' => [
                        'Events.public_from IS NULL',
                        'Events.public_from <=' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
                    ],
                ],
                [
                    'OR' => [
                        'Events.public_to IS NULL',
                        'Events.public_to >' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
                    ],
                ],
            ]);
        }

        $labelId = Hash::get($options, 'inputs.label_id');
        if (isset($labelId) && $labelId !== '') {
            $labelsTable->addNestWhere($query, (int)$labelId);
        }

        $tagQueryBuilder = [
            SiteSetting::TAG_SEARCH_METHOD_OR => function ($query, $tagIds) {
                $eventTagsQuery = $this->getAssociation('EventTags')->find();
                $eventTagsQuery->select(['EventTags.event_id']);
                $eventTagsQuery->where(['EventTags.tag_id IN' => $tagIds]);
                $query->where([
                    'Events.id IN' => $eventTagsQuery,
                ]);
            },
            SiteSetting::TAG_SEARCH_METHOD_AND => function ($query, $tagIds) {
                $eventTagsQuery = $this->getAssociation('EventTags')->find();
                $eventTagsQuery->select(['count' => $query->func()->count('*')]);
                $eventTagsQuery->where([
                    'EventTags.event_id = Events.id',
                    'EventTags.tag_id IN' => $tagIds,
                ]);
                $query->where($query->newExpr()->eq($eventTagsQuery, count($tagIds)));
            },
        ];
        foreach ((array)Hash::get($options, 'inputs.tag_id') as $tagIds) {
            if (!empty($tagIds)) {
                $siteSetting = $siteSettingsTable->getData();
                call_user_func($tagQueryBuilder[$siteSetting->get('tag_search_method')], $query, (array)$tagIds);
            }
        }

        $name = Hash::get($options, 'inputs.event_name');
        if (isset($name)) {
            $options['inputs']['name'] = $name;
        }

        $query->disableBufferedResults();

        $this->callFinder('search', $query, [
            'search' => Hash::get($options, 'inputs', []),
            'collection' => 'calendar',
        ]);

        $queryBuilder = Hash::get($options, 'calendarQueryBuilder');
        if (isset($queryBuilder)) {
            $query = call_user_func($queryBuilder, $query);
        }

        return $query;
    }

    /**
     * カレンダーポップアップ生成時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCalendarPopup(Query $query, array $options)
    {
        $query = $this->callFinder('calendar', $query, $options);
        $query->order([
            'Events.sort_no' => 'ASC',
            'Events.time_from' => 'ASC',
            'Events.time_to' => 'ASC',
            'Events.id' => 'ASC',
        ], true);

        return $query;
    }

    /**
     * インターバルの最大値を取得
     *
     * @return int 日数（分単位切り上げ）
     */
    public function getMaxInterval()
    {
        if (!isset($this->maxInterval)) {
            $maxInterval = 0;

            $data = $this->find('maxInterval')->first();
            if (isset($data['interval_time']) || isset($data['interval_day'])) {
                if (isset($data['interval_time']) && $data['interval_time'] > 0) {
                    $time = $data['interval_time'];
                    if ($time % 1440 > 0) {
                        $time += 1440 - ($time % 1440);
                    }
                    $maxInterval = (int)max($maxInterval, $time);
                }
                if (isset($data['interval_day']) && $data['interval_day'] > 0) {
                    $maxInterval = (int)max($maxInterval, $data['interval_day']);
                }
            }

            $this->maxInterval = $maxInterval;
        }

        return $this->maxInterval;
    }

    /**
     * インターバルの最大値取得用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findMaxInterval(Query $query, array $options)
    {
        $query->select([
            'interval_time' => $query->func()->max('Events.interval_time'),
            'interval_day' => $query->func()->max('Events.interval_day'),
        ]);

        return $query;
    }

    /**
     * 公開側詳細画面のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findForUser(Query $query, array $options)
    {
        $query->select([
            'id',
            'label_id',
            'name',
            'description',
        ]);

        $query->contain([
            'Labels' => [
                'fields' => [
                    'id',
                    'name',
                    'public_flg',
                ],
                'queryBuilder' => function ($labelQuery) {
                    $labelQuery->where([
                        'Labels.public_flg' => Label::PUBLIC_FLG_ON,
                    ]);

                    return $labelQuery;
                },
            ],
            'EventImages' => [
                'fields' => [
                    'id',
                    'event_id',
                    'url',
                ],
            ],
            'EventRemarks' => [
                'fields' => ['id', 'event_id', 'name', 'form_item_id', 'detail_display_flg', 'remark'],
                'queryBuilder' => function ($remarksQuery) {
                    $remarksQuery->where([
                        'EventRemarks.detail_display_flg' => EventRemark::DETAIL_DISPLAY_FLG_ON,
                    ]);

                    return $remarksQuery;
                },

            ],
        ], true);

        $query->where([
            'Events.public_flg' => Event::PUBLIC_FLG_ON,
            [
                'OR' => [
                    'Events.public_from IS NULL',
                    'Events.public_from <=' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
                ],
            ],
            [
                'OR' => [
                    'Events.public_to IS NULL',
                    'Events.public_to >' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
                ],
            ],
        ]);

        return $query;
    }

    /**
     * キャンセル待ち通知登録のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findWaitingCancellation(Query $query, array $options)
    {
        $query->select([
            'id',
            'name',
            'type',
            'date_from',
            'date_to',
            'time_from',
            'time_to',
            'event_unit_time',
            'time_plan',
            'multiple_time_plan_type',
            'usage_unit_time',
            'usage_time_from',
            'usage_time_to',
            'usage_unit_day',
            'usage_day_from',
            'usage_day_to',
            'interval_time',
            'interval_day',
            'stock',
            'stock_range_from',
            'stock_range_to',
            'public_flg',
            'public_from',
            'public_to',
            'waiting_cancellation_flg',
        ]);

        $query->contain([
            'EventWeeks' => [
                'fields' => [
                    'id',
                    'event_id',
                    'week',
                ],
            ],
            'EventStockSettings' => [
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
            'EventStockSettings.EventStockSettingWeeks' => [
                'fields' => [
                    'id',
                    'event_stock_setting_id',
                    'week',
                ],
            ],
            'EventStockSettings.EventStockSettingExcludeDates' => [
                'fields' => [
                    'id',
                    'event_stock_setting_id',
                    'date',
                ],
            ],
            'EventHolidays' => [
                'fields' => [
                    'id',
                    'event_id',
                    'date_from',
                    'date_to',
                    'time_from',
                    'time_to',
                ],
            ],
            'EventHolidays.EventHolidayWeeks' => [
                'fields' => [
                    'id',
                    'event_holiday_id',
                    'week',
                ],
            ],
            'EventHolidays.EventHolidayExcludeDates' => [
                'fields' => [
                    'id',
                    'event_holiday_id',
                    'date',
                ],
            ],
        ], true);

        return $query;
    }

    /**
     * 配列データのキー振り直し
     *
     * @param array $reArray 配列
     * @param mixed $keys 振り直しを実施するキー
     * @param array $options オプション
     * @return array
     */
    public function rebalanceArrayKey(array $reArray, $keys, array $options = [])
    {
        $reArray = parent::rebalanceArrayKey($reArray, $keys);

        if (!empty($reArray['event_holidays'])) {
            foreach ($reArray['event_holidays'] as $index => $eventHolidays) {
                $reArray['event_holidays'][$index] = parent::rebalanceArrayKey(
                    $eventHolidays,
                    'event_holiday_exclude_dates'
                );
            }
        }

        if (!empty($reArray['event_stock_settings'])) {
            foreach ($reArray['event_stock_settings'] as $index => $eventHolidays) {
                $reArray['event_stock_settings'][$index] = parent::rebalanceArrayKey(
                    $eventHolidays,
                    'event_stock_setting_exclude_dates'
                );
            }
        }

        return $reArray;
    }

    /**
     * 不要な入力値を消去
     *
     * @param array $data 入力値
     * @return array
     */
    public function clearUnnecessaryInputs($data)
    {
        $clearKeys = [];

        $type = null;
        if (isset($data['type']) && is_scalar($data['type'])) {
            $type = $data['type'];
        }
        if ((string)$type === (string)Event::TYPE_TIME) {
            // [タイプ：時間単位での予約]で不要な値
            $clearKeys = array_merge($clearKeys, [
                'usage_unit_day',
                'usage_day_from',
                'usage_day_to',
                'interval_day',
            ]);
        } elseif ((string)$type === (string)Event::TYPE_DAY) {
            // [タイプ：日にち単位での予約]で不要な値
            $clearKeys = array_merge($clearKeys, [
                'event_unit_time',
                'usage_unit_time',
                'usage_time_from',
                'usage_time_to',
                'interval_time',
            ]);
        }

        $timePlan = null;
        if (isset($data['time_plan']) && is_scalar($data['time_plan'])) {
            $timePlan = $data['time_plan'];
        }
        if ((string)$timePlan === (string)Event::MULTIPLE_TIME_PLAN_TYPE_SINGLE) {
            // [料金設定：一律]で不要な値
            $clearKeys = array_merge($clearKeys, [
                'multiple_time_plan_type',
                'event_plans',
            ]);
        } elseif ((string)$timePlan === (string)Event::MULTIPLE_TIME_PLAN_TYPE_MULTI) {
            // [料金設定：プラン]で不要な値
            $clearKeys = array_merge($clearKeys, [
                'usage_unit_time',
                'usage_time_from',
                'usage_time_to',
                'usage_day_from',
                'usage_day_to',
                'usage_unit_day',
                'charge',
            ]);
        }

        $stockDisplayType = null;
        if (isset($data['stock_display_type']) && is_scalar($data['stock_display_type'])) {
            $stockDisplayType = $data['stock_display_type'];
        }
        if ((string)$stockDisplayType === (string)Event::STOCK_DISPLAY_TYPE_NUMBER) {
            // [在庫数表示設定：数字で表示]で不要な値
            $clearKeys = array_merge($clearKeys, [
                'event_stock_marks',
            ]);
        }

        $backgroundColorType = null;
        if (isset($data['background_color_type']) && is_scalar($data['background_color_type'])) {
            $backgroundColorType = $data['background_color_type'];
        }
        if ((string)$backgroundColorType === (string)Event::BACKGROUND_COLOR_TYPE_DEFAULT) {
            // [「空きあり」のカラー：デフォルト]で不要な値
            $clearKeys = array_merge($clearKeys, [
                'color_chip_id',
            ]);
        }

        $deadlines = [
            'registration',
            'editing',
            'cancellation',
        ];
        foreach ($deadlines as $deadline) {
            $deadlineType = null;
            $deadlineTypeKey = $deadline . '_deadline_type';
            if (isset($data[$deadlineTypeKey]) && is_scalar($data[$deadlineTypeKey])) {
                $deadlineType = $data[$deadlineTypeKey];
            }
            if ((string)$deadlineType === (string)Event::DEADLINE_TYPE_TIME) {
                // [締切タイミング：時間前]で不要な値
                $clearKeys = array_merge($clearKeys, [
                    $deadline . '_deadline_time',
                ]);
            }
        }

        foreach ($clearKeys as $key) {
            $data[$key] = null;
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function tryLockForImport()
    {
        return $this->tryLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_EVENT);
    }

    /**
     * @inheritDoc
     */
    public function getLockForImport()
    {
        $this->getLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_EVENT);
    }

    /**
     * @inheritDoc
     */
    public function releaseLockForImport()
    {
        $this->releaseLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_EVENT);
    }

    /**
     * Model.beforeDeleteイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        //孫アソシエーションが削除されないため先に孫を持つデータを削除する
        $eventHolidays = $entity->get('event_holidays');
        /** @var \App\Model\Table\EventHolidaysTable $eventHolidaysTable */
        $eventHolidaysTable = $this->getTableLocator()->get('EventHolidays');
        if (!empty($eventHolidays)) {
            foreach ($eventHolidays as $eventHoliday) {
                $eventHolidaysTable->delete($eventHoliday, ['checkRules' => false]);
            }
        }

        $eventStockSettings = $entity->get('event_stock_settings');
        /** @var \App\Model\Table\EventStockSettingsTable $eventHolidaysTable */
        $eventStockSettingsTable = $this->getTableLocator()->get('EventStockSettings');
        if (!empty($eventStockSettings)) {
            foreach ($eventStockSettings as $eventStockSetting) {
                $eventStockSettingsTable->delete($eventStockSetting, ['checkRules' => false]);
            }
        }
    }

    /**
     * プラン一覧のCSVのヘッダを生成
     *
     * @return array ヘッダ
     */
    public function generatePlanCsvHeader()
    {
        $header = [];
        $headerTemplate = Configure::readOrFail('Setting.csv.download.eventPlan.header');
        $headerColumnId = Configure::readOrFail('Setting.csv.download.eventPlan.headerColumnId');

        foreach (array_keys($headerTemplate) as $column) {
            $header[$column] = $this->csvFormat()->csvHeader(
                Hash::get($headerTemplate, (string)$column),
                $headerColumnId[$column]
            );
        }

        return $header;
    }

    /**
     * CSVへ出力するデータを生成（プラン一覧エキスポート）
     *
     * @param array|null $csvItems 出力項目
     * @param array $options オプション
     * @return array データ
     */
    public function generatePlanCsvData($csvItems, array $options = [])
    {
        $data = [];

        foreach (array_keys($options['header']) as $column) {
            $data[$column] = $this->formatPlanCsvData((string)$column, $options);
        }

        return $data;
    }

    /**
     * CSVへ出力するデータを生成（プラン一覧エキスポート）
     *
     * @param string $column カラム
     * @param array $options オプション
     * @return string データ
     */
    public function formatPlanCsvData(string $column, array $options)
    {
        $events = $options['event'];

        $data = $events->get($column);
        $value = '';
        if (!is_null($data)) {
            switch ($column) {
                case static::CSV_COLUMN_EVENT_PLANS:
                    /** @var \App\Model\Table\EventPlansTable $eventPlans */
                    $eventPlans = $this->getTableLocator()->get('EventPlans');
                    $header = [
                        'asHeader' => array_keys(
                            Configure::readOrFail(
                                'Setting.csv.download.eventPlan.associationsHeader.' . static::CSV_COLUMN_EVENT_PLANS
                            )
                        ),
                    ];
                    $value = $eventPlans->generateCsvData(
                        $data,
                        $options + $header
                    );

                    break;
                default:
                    $value = $data;
                    break;
            }
        }

        return $value;
    }

    /**
     * プラン一覧のCSVファイルを生成
     *
     * @param array $searchCondition 検索条件
     * @param string $finder ファインダー
     * @param array $options オプション
     * @return string ファイルパス
     */
    public function createPlanCsv(array $searchCondition, string $finder = 'csv', array $options = [])
    {
        $header = $this->generatePlanCsvHeader();
        $query = $this->find($finder, $options + [
                'inputs' => $searchCondition,
            ]);

        $filePath = $this->createCsvFile($header, function () use ($header, $query) {
            foreach ($query as $event) {
                yield $this->generatePlanCsvData($event, [
                    'event' => $event,
                    'header' => $header,
                ]);
            }
        });

        return $filePath;
    }

    /**
     * 公開側予約枠名検索用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEventNameForPublic(Query $query, array $options)
    {
        // 予約枠名の重複は取り除く
        $sub = clone $this;
        $sub->setAlias('sub');
        $subQuery = $sub->find()->select([
            'id' => 'sub.id',
            'name' => 'sub.name',
            'sort_no' => 'sub.sort_no',
            'date_to' => 'sub.date_to',
            'public_flg' => 'sub.public_flg',
            'public_from' => 'sub.public_from',
            'public_to' => 'sub.public_to',
        ])->distinct(['sub.name']);

        $query = $this->find()->select([
            'id',
            'name',
            'sort_no',
            'date_to',
            'public_flg',
            'public_from',
            'public_to',
        ], true);

        $query->from(['Events' => $subQuery]);

        $query->where([
            'public_flg' => Event::PUBLIC_FLG_ON,
            [
                'OR' => [
                    'public_from IS NULL',
                    'public_from <=' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
                ],
            ],
            [
                'OR' => [
                    'public_to IS NULL',
                    'public_to >' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
                ],
            ],
        ]);

        $searchDateToFlg = Hash::get($options, 'searchDateToFlg');
        if (isset($searchDateToFlg)) {
            $date = DateTimeUtility::convertToDateObject(FrozenTime::now());
            $query->where([
                'OR' => [
                    'Events.date_to IS NULL',
                    'Events.date_to >=' => $date,
                ],
            ]);
        }

        $query->order([
            'sort_no' => 'ASC',
            'id' => 'ASC',
        ], true);

        return $query;
    }

    /**
     * 公開側検索で表示させる予約枠名を取得
     *
     * @param bool $searchDateToFlg date_toを検索条件に含ませるかどうか
     * @return array
     */
    public function getEventNameForPublic($searchDateToFlg = false)
    {
        $query = $this->find('eventNameForPublic', ['searchDateToFlg' => $searchDateToFlg]);

        return $this->callFinder('list', $query, [
            'keyField' => 'id',
            'valueField' => 'name',
        ])->toArray();
    }
}
