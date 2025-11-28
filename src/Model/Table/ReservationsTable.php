<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Controller\Component\PaginationComponent;
use App\Exception\PaymentRollbackException;
use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\AutoReplyMail;
use App\Model\Entity\Event as EventEntity;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\Entity\PaymentMethod;
use App\Model\Entity\PaymentSetting;
use App\Model\Entity\PaymentStatus;
use App\Model\Entity\ReceptionStatus;
use App\Model\Entity\Reservation;
use App\Model\Entity\ReservationPayment;
use App\Model\Entity\ReservationSmartLock;
use App\Model\Entity\ReservationStatus;
use App\Model\Entity\User;
use App\Model\EventCalendar\AbstractCalendarPopup;
use App\Model\EventCalendar\AbstractCalendarType;
use App\Model\EventCalendar\CalendarPopupFactory;
use App\Model\EventCalendar\CalendarTypeFactory;
use App\Model\ImportableTableInterface;
use App\Model\InputType\Item\Type\AdditionTypeInterface;
use App\Model\InputType\Item\Type\FileUploadInterface;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\OptionTypeInterface;
use App\Model\Table\Traits\FileUploadTrait;
use App\Model\Table\Traits\PaymentTrait;
use App\Utility\ArrayUtility;
use App\Utility\DateTimeUtility;
use App\Utility\FileUtility;
use App\Utility\QrCodeUtility;
use App\Utility\SmartLock\SmartLockLinkage;
use App\Utility\StringUtility;
use App\Validation\CustomValidation;
use ArrayObject;
use Cake\Collection\CollectionInterface;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Query as DatabaseQuery;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\Paging\NumericPaginator;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;
use Throwable;

/**
 * Reservations Model
 *
 * @method \App\Model\Entity\Reservation newEmptyEntity()
 * @method \App\Model\Entity\Reservation newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Reservation[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Reservation get($primaryKey, $options = [])
 * @method \App\Model\Entity\Reservation findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Reservation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Reservation[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Reservation|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Reservation saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Reservation[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Reservation[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Reservation[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Reservation[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ReservationsTable extends AppTable implements ImportableTableInterface
{
    use MailerAwareTrait;
    use PaymentTrait;
    use FileUploadTrait;

    public const RESERVATION_TYPE_EXISTING_USER = 1;
    public const RESERVATION_TYPE_NEW_USER = 2;
    public const RESERVATION_TYPE_NON_USER = 3;

    /**
     * CSV出力時の1回の取得件数
     */
    public const CSV_PAGEVIEW = 5000;

    public const CHARGE_MAX = 1000000000;

    /**
     * 決済トラッキングID：MAX
     */
    public const PAYMENT_TRACKING_ID_MAX = 14;

    /**
     * @var array|null
     */
    protected $fieldValueOptionsForRegister = null;

    /**
     * @var array|null
     */
    protected $fieldValueOptionsForEdit = null;

    /**
     * @var bool
     */
    protected $isThreeDSecure = false;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ReservationStatuses', [
            'foreignKey' => 'reservation_status_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ReceptionStatuses', [
            'foreignKey' => 'reception_status_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('PaymentMethods', [
            'foreignKey' => 'payment_method_id',
        ]);
        $this->belongsTo('PaymentStatuses', [
            'foreignKey' => 'payment_status_id',
        ]);
        $this->hasMany('AutoReplyMailHistories', [
            'foreignKey' => 'reservation_id',
            'dependent' => true,
        ]);
        $this->hasMany('ReservationAdditions', [
            'foreignKey' => 'reservation_id',
            'dependent' => true,
            'saveStrategy' => 'replace',
        ]);
        $this->hasMany('ReservationEventPlans', [
            'foreignKey' => 'reservation_id',
            'dependent' => true,
            'saveStrategy' => 'replace',
        ]);
        $this->hasMany('ReservationGuestCodes', [
            'foreignKey' => 'reservation_id',
            'dependent' => true,
        ]);
        $this->hasMany('ReservationOptions', [
            'foreignKey' => 'reservation_id',
            'dependent' => true,
            'saveStrategy' => 'replace',
        ]);
        $this->hasMany('ReservationPayments', [
            'foreignKey' => 'reservation_id',
            'dependent' => true,
        ]);
        $this->hasMany('ReservationVideoMeetings', [
            'foreignKey' => 'reservation_id',
            'dependent' => true,
        ]);
        $this->hasOne('ReservationSmartLocks', [
            'foreignKey' => 'reservation_id',
            'dependent' => true,
        ]);
        $this->addBehavior('FileUpload');
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $adminFlg = $this->commonData()->existsAdminLoginData();

        if ($adminFlg) {
            $userIdRequired = function ($context) {
                $reservationType = $context['data']['reservation_type'];
                if (((string)$reservationType) !== ((string)static::RESERVATION_TYPE_EXISTING_USER)) {
                    return false;
                }

                return true;
            };
            $validator
                ->requirePresence('user_id', $userIdRequired, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('user_id', __(Message::ERROR_NOT_EMPTY), function ($context) use ($userIdRequired) {
                    return !call_user_func($userIdRequired, $context);
                })
                ->add('user_id', [
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
                ]);

            $validator
                ->requirePresence('charge', false)
                ->allowEmptyString('charge')
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
                    'compareLessOrEqual' => [
                        'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::CHARGE_MAX],
                        'last' => true,
                        'message' => __(Message::ERROR_OVER_DIGIT, static::CHARGE_MAX),
                    ],
                ]);

            $validator
                ->requirePresence('calculate_charge', false)
                ->allowEmptyString('calculate_charge')
                ->add('calculate_charge', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => ['inList', Configure::readOrFail('Master.common.flg')],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);

            $validator
                ->requirePresence('reservation_status_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
                ->allowEmptyString('reservation_status_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
                ->add('reservation_status_id', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => function ($value, $context) {
                            $valueOptions = [];
                            if ($context['newRecord']) {
                                $valueOptions = $this->getFieldValueOptionsForRegister('reservationStatusId');
                            } else {
                                $valueOptions = $this->getFieldValueOptionsForEdit('reservationStatusId');
                            }

                            return Validation::inList($value, array_keys($valueOptions));
                        },
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);

            $validator
                ->requirePresence('reception_status_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
                ->allowEmptyString('reception_status_id')
                ->add('reception_status_id', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => function ($value, $context) {
                            $valueOptions = [];
                            if ($context['newRecord']) {
                                $valueOptions = $this->getFieldValueOptionsForRegister('receptionStatusId');
                            } else {
                                $valueOptions = $this->getFieldValueOptionsForEdit('receptionStatusId');
                            }

                            return Validation::inList($value, array_keys($valueOptions));
                        },
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);

            $validator
                ->requirePresence('payment_method_id', false)
                ->allowEmptyString('payment_method_id')
                ->add('payment_method_id', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => ['inList', array_keys($this->getFieldValueOptions('paymentMethodId'))],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);

            $validator
                ->requirePresence('payment_status_id', false)
                ->allowEmptyString('payment_status_id')
                ->add('payment_status_id', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => ['inList', array_keys($this->getFieldValueOptions('paymentStatusId'))],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);

            $validator
                ->requirePresence('payment_tracking_id', false)
                ->allowEmptyString('payment_tracking_id')
                ->add('payment_tracking_id', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', static::PAYMENT_TRACKING_ID_MAX],
                        'last' => true,
                        'message' => __(Message::ERROR_MAX_LENGTH, static::PAYMENT_TRACKING_ID_MAX),
                    ],
                    'alnumSym' => [
                        'rule' => ['alnumSym'],
                        'last' => true,
                        'message' => __(Message::ERROR_ALNUM_SYM),
                    ],
                    'exists' => [
                        'rule' => function ($value, $context) {
                            /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
                            $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');
                            /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
                            $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

                            $reservationPayment = $reservationPaymentsTable->find()
                                ->where(['reservation_id' => $context['data']['id']])
                                ->order(['id' => 'DESC'])
                                ->first();

                            if (!($reservationPayment instanceof ReservationPayment)) {
                                return false;
                            }
                            // 値が変更されている場合のみ存在チェックを行う
                            if ($value === $reservationPayment->get('payment_tracking_id')) {
                                return true;
                            }

                            $paymentMethodType = $paymentMethodsTable->getPaymentMethodType(
                                $reservationPayment->get('payment_method_id')
                            );

                            if ($paymentMethodType === PaymentMethod::TYPE_AU_PAY) {
                                return true;
                            } elseif (
                                $paymentMethodType === PaymentMethod::TYPE_PAYPAY
                                || $paymentMethodType === PaymentMethod::TYPE_APPLE_PAY
                            ) {
                                $paymentData = $reservationPayment->getPaymentData(true, $value);
                            }

                            if (empty($paymentData)) {
                                return false;
                            }

                            // レスポンス確認
                            if ($paymentMethodType === PaymentMethod::TYPE_PAYPAY) {
                                if (Hash::get($paymentData, 'res_pay_method_info.tracking_id') !== $value) {
                                    return false;
                                }
                            } elseif ($paymentMethodType === PaymentMethod::TYPE_APPLE_PAY) {
                                if (empty(Hash::get($paymentData, 'res_sps_transaction_id'))) {
                                    return false;
                                }
                            }

                            return true;
                        },
                        'last' => true,
                        'message' => __(Message::ERROR_NOT_FOUND_TRACKING_ID),
                    ],
                ]);
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
        $adminFlg = $this->commonData()->existsAdminLoginData();

        $validator
            ->requirePresence('reservation_type', false)
            ->allowEmptyString('reservation_type')
            ->add('reservation_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('reservationType'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('user_id', false)
            ->allowEmptyString('user_id')
            ->add('user_id', [
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
            ]);

        $validator
            ->requirePresence('user_authority_id', false)
            ->allowEmptyString('user_authority_id')
            ->add('user_authority_id', [
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
            ]);

        $validator
            ->requirePresence('event_id', false)
            ->allowEmptyString('event_id')
            ->add('event_id', [
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
            ]);

        // 管理側ではカテゴリーが管理者に紐づくか確認する
        if ($adminFlg) {
            $validator->add('event_id', [
                'exists' => [
                    'rule' => function ($check) {
                        /** @var \App\Model\Table\EventsTable $eventsTable */
                        $eventsTable = $this->getTableLocator()->get('Events');

                        return $eventsTable->exists([
                            'id' => $check,
                        ]);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_EXISTS),
                ],
                'label' => [
                    'rule' => function ($value) {
                        /** @var \App\Model\Table\EventsTable $eventsTable */
                        $eventsTable = $this->getTableLocator()->get('Events');
                        /** @var \App\Model\Table\LabelsTable $labelsTable */
                        $labelsTable = $this->getTableLocator()->get('Labels');

                        $event = $eventsTable->get($value, [
                            'finder' => 'Reservation',
                        ]);

                        return $labelsTable->isAdminUsableLabel($event->get('label_id'));
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);
        }

        $validator
            ->requirePresence('usage_timestamp_from', false)
            ->allowEmptyString('usage_timestamp_from')
            ->add('usage_timestamp_from', [
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
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $isAdmin = false;
        if ($this->commonData()->existsAdminLoginData()) {
            $isAdmin = true;
        }

        // 利用日時チェック
        $rules->add(
            function ($entity) {
                if ($entity->hasErrors()) {
                    return true;
                }

                if (!($entity instanceof Reservation)) {
                    throw new CakeException();
                }
                $event = $entity->getEventEntity();
                if (!($event instanceof EventEntity)) {
                    throw new CakeException();
                }

                if (
                    !$event->existsUsageTimestamp(
                        $entity->get('usage_timestamp_from'),
                        $entity->get('usage_timestamp_to')
                    )
                ) {
                    return false;
                }

                return true;
            },
            'checkReservationTime',
            [
                'errorField' => 'event_error',
                'message' => __(Message::ERROR_RESERVATION_TIME),
            ]
        );

        if (!$isAdmin) {
            // 休日チェック
            $rules->add(
                function ($entity) {
                    if ($entity->hasErrors()) {
                        return true;
                    }

                    if (!($entity instanceof Reservation)) {
                        throw new CakeException();
                    }

                    // 編集時は予約枠、日時、予約数を変更した場合のみ
                    if (
                        !$entity->isNew() && !$entity->isDirty('event_id') && !$entity->isDirty('usage_timestamp_from')
                        && !$entity->isDirty('usage_timestamp_to') && !$entity->isNumberDirty()
                    ) {
                        return true;
                    }

                    $result = true;
                    foreach ($entity->getTimetable()->getTimetable() as $eventUnit) {
                        if ($eventUnit->isHoliday()) {
                            $result = false;
                            break;
                        }
                    }

                    return $result;
                },
                'checkHoliday',
                [
                    'errorField' => 'event_error',
                    'message' => __(Message::ERROR_RESERVE_HOLIDAY),
                ]
            );

            // 会員有効期間チェック
            $rules->add(
                function ($entity) {
                    if ($entity->hasErrors()) {
                        return true;
                    }

                    if (!($entity instanceof Reservation)) {
                        throw new CakeException();
                    }

                    $result = true;
                    if ($this->commonData()->existsUserLoginData()) {
                        /** @var \App\Model\Entity\User $userLoginData */
                        $userLoginData = $this->commonData()->getUserLoginData();
                        $dateTimeFrom = $entity->get('usage_timestamp_from');
                        $dateTimeTo = $entity->get('usage_timestamp_to');
                        if (!isset($dateTimeFrom) || !isset($dateTimeTo)) {
                            throw new CakeException();
                        }
                        // 利用日時(to) の時間部分が00:00:00 の場合、日付を1日前と判定
                        if ($dateTimeTo->format('H:i:s') === '00:00:00') {
                            $dateTimeTo = $dateTimeTo->subDays(1);
                        }
                        if (!$userLoginData->withinValidPeriod($dateTimeFrom, $dateTimeTo)) {
                            $result = false;
                        }
                    }

                    return $result;
                },
                'checkWithinValidPeriod',
                [
                    'errorField' => 'event_error',
                    'message' => __(Message::ERROR_USER_EXPIRATION_RESERVE),
                ]
            );

            // 登録締切チェック
            $rules->add(
                function ($entity) {
                    if ($entity->hasErrors()) {
                        return true;
                    }

                    if (!($entity instanceof Reservation)) {
                        throw new CakeException();
                    }

                    // 編集時は予約枠、開始日時、予約数を変更した場合のみ
                    if (
                        !$entity->isNew() && !$entity->isDirty('event_id')
                        && !$entity->isDirty('usage_timestamp_from') && !$entity->isNumberDirty()
                    ) {
                        return true;
                    }

                    $firstUnit = $entity->getTimetable()->getFirstUnit();
                    if (!isset($firstUnit)) {
                        throw new CakeException();
                    }

                    $result = true;
                    if (!$firstUnit->isWithinRegistrationDeadline()) {
                        $result = false;
                    }

                    return $result;
                },
                'checkRegistrationDeadline',
                [
                    'errorField' => 'event_error',
                    'message' => __(Message::ERROR_REGISTRATION_DEADLINE),
                ]
            );

            // キャンセル締切チェック
            $rules->add(
                function ($entity) {
                    if ($entity->hasErrors()) {
                        return true;
                    }

                    if (!($entity instanceof Reservation)) {
                        throw new CakeException();
                    }

                    // 編集時のみ
                    if ($entity->isNew()) {
                        return true;
                    }

                    // 予約枠、日時、予約数を変更した場合のみ
                    if (
                        !$entity->isDirty('event_id') && !$entity->isDirty('usage_timestamp_from')
                        && !$entity->isDirty('usage_timestamp_to') && !$entity->isNumberDirty()
                    ) {
                        return true;
                    }

                    $originalTimetable = $entity->getOriginalTimetable();
                    $eventUnit = $originalTimetable->getEvent()->isEditingDeadlineCriterionTo() ?
                        $originalTimetable->getLastUnit() : $originalTimetable->getFirstUnit();
                    if (!isset($eventUnit)) {
                        throw new CakeException();
                    }

                    $result = true;
                    if (!$eventUnit->isWithinCancellationDeadline()) {
                        $result = false;
                    }

                    return $result;
                },
                'checkCancellationDeadline',
                [
                    'errorField' => 'event_error',
                    'message' => __(Message::ERROR_CANCELLATION_DEADLINE),
                ]
            );

            // 予約時間利用済チェック
            $rules->add(
                function ($entity) {
                    if ($entity->hasErrors()) {
                        return true;
                    }

                    if (!($entity instanceof Reservation)) {
                        throw new CakeException();
                    }

                    // 編集時のみ
                    if ($entity->isNew()) {
                        return true;
                    }

                    // 終了日時を変更した場合のみ
                    if (!$entity->isDirty('usage_timestamp_to')) {
                        return true;
                    }

                    // 「予約変更締切タイミング」の判定基準が「終了時間」のときのみ
                    $originalTimetable = $entity->getOriginalTimetable();
                    if (!$originalTimetable->getEvent()->isEditingDeadlineCriterionTo()) {
                        return true;
                    }

                    $result = true;
                    if (
                        CustomValidation::compareDateTime(
                            $this->commonData()->getNowDateTime(),
                            CustomValidation::COMPARE_GREATER_OR_EQUAL,
                            $entity->get('usage_timestamp_to')
                        )
                    ) {
                        $result = false;
                    }

                    return $result;
                },
                'checkTimeUsed',
                [
                    'errorField' => 'event_error',
                    'message' => __(Message::ERROR_TIME_USED),
                ]
            );

            // 受付期間チェック
            $rules->add(
                function ($entity) {
                    if ($entity->hasErrors()) {
                        return true;
                    }

                    if (!($entity instanceof Reservation)) {
                        throw new CakeException();
                    }

                    // 編集時は予約枠、日時、予約数を変更した場合のみ
                    if (
                        !$entity->isNew() && !$entity->isDirty('event_id') && !$entity->isDirty('usage_timestamp_from')
                        && !$entity->isDirty('usage_timestamp_to') && !$entity->isNumberDirty()
                    ) {
                        return true;
                    }

                    $lastUnit = $entity->getTimetable()->getLastUnit();
                    if (!isset($lastUnit)) {
                        throw new CakeException();
                    }

                    $result = true;
                    if (!$lastUnit->isWithinReceptionPeriod()) {
                        $result = false;
                    }

                    return $result;
                },
                'checkReceptionPeriod',
                [
                    'errorField' => 'event_error',
                    'message' => __(Message::ERROR_RECEPTION_PERIOD),
                ]
            );

            // 重複予約チェック
            $rules->add(
                function ($entity) {
                    if ($entity->hasErrors()) {
                        return true;
                    }

                    if (!($entity instanceof Reservation)) {
                        throw new CakeException();
                    }
                    $event = $entity->getEventEntity();
                    if (!($event instanceof EventEntity)) {
                        throw new CakeException();
                    }

                    // 会員の予約のみ
                    $user = $entity->getUserEntity();
                    if (!($user instanceof User)) {
                        throw new CakeException();
                    }
                    if ($user->isNew()) {
                        return true;
                    }

                    // 編集時は予約枠、日時、予約数を変更した場合のみ
                    if (
                        !$entity->isNew() && !$entity->isDirty('event_id') && !$entity->isDirty('usage_timestamp_from')
                        && !$entity->isDirty('usage_timestamp_to') && !$entity->isNumberDirty()
                    ) {
                        return true;
                    }

                    $result = true;
                    if (
                        $this->existsDuplication(
                            $user->get('id'),
                            $event,
                            $entity->get('usage_timestamp_from'),
                            $entity->get('usage_timestamp_to'),
                            $entity->get('id'),
                            $entity->getContinuousData()
                        )
                    ) {
                        $result = false;
                    }

                    return $result;
                },
                'checkDuplicateReservation',
                [
                    'errorField' => 'event_error',
                    'message' => __(Message::ERROR_DUPLICATE_RESERVATION),
                ]
            );

            // 回数制限チェック
            $rules->add(
                function ($entity) {
                    /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
                    $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

                    if ($entity->hasErrors()) {
                        return true;
                    }

                    if (!($entity instanceof Reservation)) {
                        throw new CakeException();
                    }
                    $event = $entity->getEventEntity();
                    if (!($event instanceof EventEntity)) {
                        throw new CakeException();
                    }

                    // 会員の予約のみ
                    $user = $entity->getUserEntity();
                    if (!($user instanceof User)) {
                        throw new CakeException();
                    }
                    if ($user->isNew()) {
                        return true;
                    }

                    // 編集時は予約枠、日時、予約数を変更した場合のみ
                    if (
                        !$entity->isNew() && !$entity->isDirty('event_id') && !$entity->isDirty('usage_timestamp_from')
                        && !$entity->isDirty('usage_timestamp_to') && !$entity->isNumberDirty()
                    ) {
                        return true;
                    }

                    // 会員権限
                    $userAuthority = null;
                    try {
                        /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                        $userAuthority = $userAuthoritiesTable->get($user->get('user_authority_id'), [
                            'finder' => 'reservationLimit',
                        ]);
                    } catch (RecordNotFoundException $e) {
                        throw new CakeException();
                    }

                    $reservations = array_merge([$entity], (array)$entity->getContinuousData());

                    $checkAuthority = [
                        'all' => Message::ERROR_RESERVATION_LIMIT_AUTHORITY_ALL,
                        'future' => Message::ERROR_RESERVATION_LIMIT_AUTHORITY_FUTURE,
                        'month' => Message::ERROR_RESERVATION_LIMIT_AUTHORITY_MONTH,
                        'day' => Message::ERROR_RESERVATION_LIMIT_AUTHORITY_DAY,
                    ];
                    foreach ($checkAuthority as $periodType => $message) {
                        if ($userAuthority->has('reservation_limit_' . $periodType)) {
                            if (
                                $this->exceedsReservationLimit(
                                    $user->get('id'),
                                    $entity->get('usage_timestamp_from'),
                                    $userAuthority->get('reservation_limit_' . $periodType),
                                    $periodType,
                                    null,
                                    $entity->get('id'),
                                    $reservations
                                )
                            ) {
                                $entity->setError('event_error', (string)__($message));

                                return false;
                            }
                        }
                    }

                    $checkEvent = [
                        'all' => Message::ERROR_RESERVATION_LIMIT_EVENT_ALL,
                        'future' => Message::ERROR_RESERVATION_LIMIT_EVENT_FUTURE,
                        'month' => Message::ERROR_RESERVATION_LIMIT_EVENT_MONTH,
                        'day' => Message::ERROR_RESERVATION_LIMIT_EVENT_DAY,
                    ];
                    foreach ($checkEvent as $periodType => $message) {
                        if ($event->has('reservation_limit_' . $periodType)) {
                            if (
                                $this->exceedsReservationLimit(
                                    $user->get('id'),
                                    $entity->get('usage_timestamp_from'),
                                    $event->get('reservation_limit_' . $periodType),
                                    $periodType,
                                    $event->get('id'),
                                    $entity->get('id'),
                                    $reservations
                                )
                            ) {
                                $entity->setError('event_error', (string)__($message));

                                return false;
                            }
                        }
                    }

                    return true;
                },
                'checkDuplicateReservation'
            );
        }

        // 在庫チェック
        $rules->add(
            function ($entity, $options) {
                $checkStock = Hash::get($options, 'checkStock', true);
                if (!$checkStock) {
                    return true;
                }
                if ($entity->hasErrors()) {
                    return true;
                }

                if (!($entity instanceof Reservation)) {
                    throw new CakeException();
                }
                if (!$entity->isKeepStockStatus()) {
                    return true;
                }

                $event = $entity->getEventEntity();
                if (!($event instanceof EventEntity)) {
                    throw new CakeException();
                }

                // 予約中のデータ
                $reservations = [$entity];
                foreach ((array)$entity->getContinuousData() as $reservation) {
                    if ((string)$reservation->get('event_id') === (string)$entity->get('event_id')) {
                        $reservations[] = $reservation;
                    }
                }

                // インターバル時間を含めて在庫チェック
                $result = $event->checkRemainStock(
                    $event->subIntervalTime($entity->get('usage_timestamp_from')),
                    $event->addIntervalTime($entity->get('usage_timestamp_to')),
                    $entity->get('id'),
                    $reservations
                );

                return $result;
            },
            'checkStock',
            [
                'errorField' => 'event_error',
                'message' => __(Message::ERROR_OUT_OF_STOCK),
            ]
        );

        // オプション在庫チェック
        $rules->add(
            function ($entity) {
                /** @var \App\Model\Table\OptionsTable $optionsTable */
                $optionsTable = $this->getTableLocator()->get('Options');

                if ($entity->hasErrors()) {
                    return true;
                }

                if (!($entity instanceof Reservation)) {
                    throw new CakeException();
                }
                if (!$entity->isKeepStockStatus()) {
                    return true;
                }

                // 予約中のデータ
                $reservationOptions = [];
                foreach (array_merge([$entity], (array)$entity->getContinuousData()) as $reservation) {
                    foreach ((array)$reservation->get('reservation_options') as $reservationOption) {
                        $reservationOptions[$reservationOption->get('option_id')][] = [
                            'usage_timestamp_from' => $reservation->get('usage_timestamp_from'),
                            'usage_timestamp_to' => $reservation->get('usage_timestamp_to'),
                            'number' => $reservationOption->get('number'),
                        ];
                    }
                }
                if (empty($reservationOptions)) {
                    return true;
                }

                $options = $optionsTable->find('calculateStock', [
                    'inputs' => [
                        'id' => array_keys($reservationOptions),
                        'usage_timestamp_from' => $entity->get('usage_timestamp_from'),
                        'usage_timestamp_to' => $entity->get('usage_timestamp_to'),
                    ],
                ]);

                // 各オプションの在庫チェック
                $result = true;
                foreach ($options as $option) {
                    if (
                        !$option->checkRemainStock(
                            $entity->get('usage_timestamp_from'),
                            $entity->get('usage_timestamp_to'),
                            $entity->get('id'),
                            $reservationOptions[$option->get('id')]
                        )
                    ) {
                        $entity->setError(
                            'option_errors_' . $option->get('id'),
                            (string)__(Message::ERROR_OUT_OF_STOCK)
                        );
                        $result = false;
                    }
                }

                return $result;
            },
            'checkOptionStock'
        );

        // 料金上限チェック
        $rules->add(
            function ($entity) {
                if ($entity->hasErrors()) {
                    return true;
                }

                if ($entity->get('charge') > static::CHARGE_MAX) {
                    return false;
                }

                return true;
            },
            'checkChargeLimit',
            [
                'errorField' => 'event_error',
                'message' => __(Message::ERROR_CHARGE_LIMIT),
            ]
        );

        // ビデオ会議連携の制限チェック
        $rules->add(
            function ($entity) {
                if (!($entity instanceof Reservation)) {
                    throw new CakeException();
                }
                if ($entity->hasErrors()) {
                    return true;
                }

                /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
                $reservationVideoMeetingsTable = $this->getTableLocator()->get('ReservationVideoMeetings');

                if (
                    $reservationVideoMeetingsTable->shouldProcessOnReserve($entity)
                    && !$reservationVideoMeetingsTable->withinApiLimit()
                ) {
                    return false;
                }

                return true;
            },
            'checkVideoMeetingLimit',
            [
                'errorField' => 'event_error',
                'message' => __(Message::ERROR_VIDEO_MEETING_LIMIT),
            ]
        );

        return $rules;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $searchInputKey = Configure::readOrFail('Master.adminSearchItems.itemsSearchInputKey');

        $this->searchManager()
            ->value($searchInputKey[AdminSearchItem::ITEM_RESERVATION_ID], [
                'fields' => 'id',
            ])
            ->value($searchInputKey[AdminSearchItem::ITEM_EVENT_ID], [
                'fields' => 'event_id',
            ])
            ->value($searchInputKey[AdminSearchItem::ITEM_RESERVATION_STATUS_ID], [
                'fields' => 'reservation_status_id',
                'multiValue' => true,
            ])
            ->value($searchInputKey[AdminSearchItem::ITEM_RECEPTION_STATUS_ID], [
                'fields' => 'reception_status_id',
                'multiValue' => true,
            ])
            ->compare($searchInputKey[AdminSearchItem::ITEM_USAGE_TIMESTAMP] . '.from', [
                'fields' => 'usage_timestamp_to',
                'operator' => '>',
            ])
            ->compare($searchInputKey[AdminSearchItem::ITEM_USAGE_TIMESTAMP] . '.to', [
                'fields' => 'usage_timestamp_from',
                'operator' => '<',
            ])
            ->value($searchInputKey[AdminSearchItem::ITEM_PAYMENT_METHOD], [
                'fields' => 'payment_method_id',
                'multiValue' => true,
            ])
            ->value($searchInputKey[AdminSearchItem::ITEM_PAYMENT_STATUS], [
                'fields' => 'payment_status_id',
                'multiValue' => true,
            ])
            ->callback($searchInputKey[AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS], [
                'callback' => function ($query, $args, $filter) {
                    $values = Hash::get($args, 'reservation_payment_status');
                    $nowDateTime = $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s');
                    $orWhere = [];

                    /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
                    $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');
                    $reservationPaymentsQuery = $reservationPaymentsTable->find('all')->select(['reservation_id']);

                    foreach ($values as $value) {
                        switch ($value) {
                            case ReservationPayment::DISPLAY_STATUS_UNSETTLED:
                                $orWhere[] = [
                                    'OR' => [
                                        ['ReservationPayments.payment_limit >=' => $nowDateTime],
                                        ['ReservationPayments.payment_limit IS NULL'],
                                    ],
                                    'ReservationPayments.status IN' => ReservationPayment::STATUS_UNSETTLED,
                                ];
                                break;
                            case ReservationPayment::DISPLAY_STATUS_EXPIRED:
                                $orWhere[] = [
                                    'OR' => [
                                        [
                                            'ReservationPayments.payment_limit <' => $nowDateTime,
                                            'ReservationPayments.status IN' => ReservationPayment::STATUS_UNSETTLED,
                                        ],
                                        ['ReservationPayments.status IN' => ReservationPayment::STATUS_EXPIRED],
                                    ],
                                ];
                                break;
                            default:
                                $orWhere[] = [
                                    'ReservationPayments.status IN' => $value,
                                ];
                                break;
                        }
                    }

                    if (!empty($orWhere)) {
                        $reservationPaymentsQuery->where([
                            'OR' => $orWhere,
                        ]);

                        $query->where(['Reservations.id IN' => $reservationPaymentsQuery]);
                    }
                },
            ])
            ->callback($searchInputKey[AdminSearchItem:: ITEM_RESERVATION_SMARTLOCK_STATUS], [
                'callback' => function ($query, $args, $filter) {
                    $values = Hash::get($args, 'reservation_smartlock_status');
                    $orWhere = [];

                    foreach ($values as $value) {
                        switch ($value) {
                            case ReservationSmartLock::DISPLAY_STATUS_UNLINKED:
                                $orWhere[] = [
                                    'AND' => [
                                        'EventSmartLocks.smart_lock_device_key IS NOT NULL',
                                        'ReservationSmartLocks.smart_lock_key_id IS NULL',
                                    ],
                                ];
                                break;
                            default:
                                break;
                        }
                    }

                    if (!empty($orWhere)) {
                        $subQuery = $this->find('smartLockUnlinkedReservation');
                        $subQuery->where([
                            'OR' => $orWhere,
                        ]);

                        $query->where(['Reservations.id IN' => $subQuery]);
                    }
                },
            ])
            ->callback($searchInputKey[AdminSearchItem::ITEM_RESERVATION_INS_TIMESTAMP] . '.from', [
                'callback' => function ($query, $args, $filter) {
                    if (isset($args[$filter->name()])) {
                        $date = new FrozenDate($args[$filter->name()]);
                        $query->where([
                            'Reservations.created >=' => $date->format('Y-m-d H:i:s'),
                        ]);
                    }
                },
            ])
            ->callback($searchInputKey[AdminSearchItem::ITEM_RESERVATION_INS_TIMESTAMP] . '.to', [
                'callback' => function ($query, $args, $filter) {
                    if (isset($args[$filter->name()])) {
                        $date = new FrozenDate($args[$filter->name()]);
                        $date = $date->addDays(1);
                        $query->where([
                            'Reservations.created <' => $date->format('Y-m-d H:i:s'),
                        ]);
                    }
                },
            ])
            ->callback($searchInputKey[AdminSearchItem::ITEM_RESERVATION_VIDEO_METTING], [
                'callback' => function ($query, $args, $filter) {
                    if (isset($args[$filter->name()])) {
                        $on = ArrayUtility::inArray(
                            Configure::readOrFail('Master.common.flg.on'),
                            (array)$args[$filter->name()]
                        );
                        $off = ArrayUtility::inArray(
                            Configure::readOrFail('Master.common.flg.off'),
                            (array)$args[$filter->name()]
                        );
                        if ($on && $off) {
                            return;
                        }

                        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
                        $reservationVideoMeetingsTable = $this->getTableLocator()->get(
                            'ReservationVideoMeetings'
                        );

                        $idQuery = $reservationVideoMeetingsTable->find();
                        $idQuery->select(['ReservationVideoMeetings.reservation_id']);
                        if ($on) {
                            $query->where([
                                'Reservations.id IN' => $idQuery,
                            ]);
                        } elseif ($off) {
                            $query->where([
                                'Reservations.id NOT IN' => $idQuery,
                            ]);
                        }
                    }
                },
            ]);

        //管理側受付状況一覧用
        $this->searchManager()->useCollection('receptionStatuses');
        $this->searchManager()
            ->value('qr_code', [
                'fields' => 'qr_code',
            ])
            ->value('label_id', [
                'fields' => 'Events.label_id',
            ]);

        //公開側予約履歴用
        $this->searchManager()->useCollection('history');
        $this->searchManager()
            ->value('user_id', [
                'fields' => 'user_id',
            ])
            ->value('status', [
                'fields' => 'reservation_status_id',
                'multiValue' => true,
            ])
            ->compare('usage_timestamp.from', [
                'fields' => 'usage_timestamp_from',
                'operator' => '>=',
            ])
            ->compare('usage_timestamp.to', [
                'fields' => 'usage_timestamp_to',
                'operator' => '<=',
            ]);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        /** @var \App\Model\Table\ReceptionStatusesTable $receptionStatusesTable */
        $receptionStatusesTable = $this->getTableLocator()->get('ReceptionStatuses');
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');
        /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
        $paymentStatusesTable = $this->getTableLocator()->get('PaymentStatuses');

        $fieldValueOptions = [
            'reservationType' => Configure::readOrFail('Master.reservation.reservationType'),
            'reservationStatusId' => $reservationStatusesTable->getValueOptions(),
            'receptionStatusId' => $receptionStatusesTable->getValueOptions(),
            'paymentMethodId' => $paymentMethodsTable->getValueOptions(),
            'paymentStatusId' => $paymentStatusesTable->getValueOptions(),
            'repeatReservationType' => Configure::readOrFail('Master.reservation.repeatReservationType'),
            'week' => Configure::readOrFail('Master.common.week'),
        ];

        return $fieldValueOptions;
    }

    /**
     * 登録時の値リストを取得
     *
     * @param string|null $key キー
     * @return array
     */
    public function getFieldValueOptionsForRegister(?string $key = null)
    {
        if (!isset($this->fieldValueOptionsForRegister)) {
            /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
            $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

            $fieldValueOptions = $this->getFieldValueOptions();

            $removeTypes = [
                ReservationStatus::STATUS_TYPE_VISIT,
                ReservationStatus::STATUS_TYPE_CANCEL,
                ReservationStatus::STATUS_TYPE_ABSENCE,
            ];
            foreach ($reservationStatusesTable->getData() as $reservationStatus) {
                if (ArrayUtility::inArray($reservationStatus['status_type'], $removeTypes)) {
                    unset($fieldValueOptions['reservationStatusId'][$reservationStatus['id']]);
                }
            }

            $this->fieldValueOptionsForRegister = $fieldValueOptions;
        }

        if (!isset($key)) {
            return $this->fieldValueOptionsForRegister;
        }

        return Hash::get($this->fieldValueOptionsForRegister, $key, []);
    }

    /**
     * 編集時の値リストを取得
     *
     * @param string|null $key キー
     * @return array
     */
    public function getFieldValueOptionsForEdit(?string $key = null)
    {
        if (!isset($this->fieldValueOptionsForEdit)) {
            $this->fieldValueOptionsForEdit = $this->getFieldValueOptions();
        }

        if (!isset($key)) {
            return $this->fieldValueOptionsForEdit;
        }

        return Hash::get($this->fieldValueOptionsForEdit, $key, []);
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
            'user_id',
            'event_id',
            'usage_timestamp_from',
            'usage_timestamp_to',
            'usage_time',
            'usage_day',
            'number',
            'charge',
            'reservation_status_id',
            'reception_status_id',
            'payment_method_id',
            'payment_status_id',
            'created',
            'modified',
            'user_deleted_flg' => $this->createUserDeletedExpression($query),
            'qr_code',
            'reception_timestamp',
        ]);

        $query->join([
            'Users' => [
                'table' => 'users',
                'type' => 'LEFT',
                'conditions' => [
                    'Reservations.user_id = Users.id',
                ],
            ],
        ]);

        $query->contain([
            'PaymentMethods' => [
                'fields' => [
                    'id',
                    'type',
                    'name',
                ],
            ],
            'ReservationAdditions' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'form_item_id',
                    'value',
                ],
            ],
            'ReservationEventPlans' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'event_plan_id',
                ],
            ],
            'ReservationOptions' => function (Query $q) {
                /** @var \App\Model\Table\ReservationOptionsTable $reservationOptionsTable */
                $reservationOptionsTable = $this->fetchTable('ReservationOptions');

                return $reservationOptionsTable->findWithSortNo($q);
            },
            'ReservationVideoMeetings' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'organizer_id',
                    'video_meeting_url',
                    'video_meeting_id',
                    'video_meeting_password',
                    'video_meeting_type',
                ],
            ],
            'ReservationSmartLocks' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'smart_lock_pin',
                    'smart_lock_key_url',
                    'smart_lock_key_id',
                    'smart_lock_user_id',
                    'smart_lock_grant_id',
                ],
            ],
        ]);

        return $query;
    }

    /**
     * キャンセル時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCancel(Query $query, array $options)
    {
        return $this->callFinder('edit', $query, $options);
    }

    /**
     * 削除時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDelete(Query $query, array $options)
    {
        return $this->callFinder('edit', $query, $options);
    }

    /**
     * 予約履歴のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchHistory(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_id',
            'event_id',
            'usage_timestamp_from',
            'usage_timestamp_to',
            'usage_time',
            'usage_day',
            'reservation_status_id',
            'payment_method_id',
            'payment_status_id',
            'created',
            'modified',
        ])->contain([
            'Events' => [
                'fields' => [
                    'id',
                    'label_id',
                    'name',
                    'type',
                    'usage_time_notation',
                ],
            ],
            'ReservationStatuses' => [
                'fields' => [
                    'id',
                    'status_type',
                ],
            ],
            'ReservationEventPlans' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'event_plan_id',
                ],
            ],
            'ReservationEventPlans.EventPlans' => [
                'fields' => [
                    'id',
                    'name',
                ],
            ],
        ]);

        $query->order([
            'Reservations.usage_timestamp_from' => 'ASC',
            'Reservations.usage_timestamp_to' => 'ASC',
            'Events.sort_no' => 'ASC',
            'Events.id' => 'ASC',
            'Reservations.id' => 'ASC',
        ], true);

        return $this->callFinder('search', $query, [
            'search' => Hash::get($options, 'inputs', []),
            'collection' => 'history',
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
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');
        /** @var \App\Model\Table\UsersTable $reservationSmartLocksTable */
        $reservationSmartLocksTable = $this->getTableLocator()->get('ReservationSmartLocks');
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $query->select([
            'id',
            'user_id',
            'event_id',
            'usage_timestamp_from',
            'usage_timestamp_to',
            'usage_time',
            'usage_day',
            'number',
            'charge',
            'reservation_status_id',
            'reception_status_id',
            'payment_method_id',
            'payment_status_id',
            'created',
            'modified',
            'user_deleted_flg' => $this->createUserDeletedExpression($query),
        ]);

        $query->contain([
            'Events' => [
                'fields' => [
                    'id',
                    'label_id',
                    'name',
                    'type',
                    'time_plan',
                ],
            ],
            'Events.EventTags' => [
                'fields' => [
                    'id',
                    'event_id',
                    'tag_id',
                ],
            ],
            'Events.Labels' => [
                'fields' => [
                    'id',
                    'name',
                ],
            ],
            'ReservationAdditions' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'form_item_id',
                    'value',
                ],
            ],
            'ReservationEventPlans' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'event_plan_id',
                ],
            ],
            'ReservationEventPlans.EventPlans' => [
                'fields' => [
                    'id',
                    'event_id',
                    'name',
                ],
            ],
            'ReservationOptions' => function (Query $q) {
                /** @var \App\Model\Table\ReservationOptionsTable $reservationOptionsTable */
                $reservationOptionsTable = $this->fetchTable('ReservationOptions');

                return $reservationOptionsTable->findWithSortNo($q);
            },
            'ReservationOptions.Options' => [
                'fields' => [
                    'id',
                    'name',
                ],
            ],
            'ReservationVideoMeetings' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'organizer_id',
                    'video_meeting_url',
                    'video_meeting_id',
                    'video_meeting_password',
                    'video_meeting_type',
                ],
            ],
            'ReservationSmartLocks' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'smart_lock_user_id',
                    'smart_lock_grant_id',
                    'smart_lock_pin',
                    'smart_lock_key_url',
                    'smart_lock_key_id',
                ],
            ],
            'ReservationPayments' => [
                'fields' => [
                    'ReservationPayments.id',
                    'ReservationPayments.reservation_id',
                    'ReservationPayments.payment_limit',
                    'ReservationPayments.status',
                ],
            ],
            'Users' => [
                'fields' => [
                    'id',
                    'user_authority_id',
                    'login_id',
                    'mail',
                    'guest_flg',
                    'withdrawal_flg',
                    'expiration_date_from',
                    'expiration_date_to',
                    'created',
                    'modified',
                ],
                'joinType' => Query::JOIN_TYPE_LEFT,
            ],
            'Users.UserAdditions' => [
                'fields' => [
                    'id',
                    'user_id',
                    'form_item_id',
                    'value',
                ],
            ],
            'Users.UserAuthorities' => [
                'fields' => [
                    'id',
                    'name',
                ],
                'joinType' => Query::JOIN_TYPE_LEFT,
            ],
            'Users.UserSmartLocks' => [
                'fields' => [
                    'id',
                    'user_id',
                    'smart_lock_user_id',
                ],
            ],
        ]);

        $query->join([
            'Events' => [
                'table' => 'events',
                'type' => 'INNER',
                'conditions' => 'Events.id = Reservations.event_id',
            ],
            'Labels' => [
                'table' => 'labels',
                'type' => 'LEFT',
                'conditions' => 'Labels.id = Events.label_id',
            ],
        ]);
        $labelsTable->joinQuery($query, 'Labels');

        //カテゴリの検索が存在しない場合はデフォルト検索を実施
        if (
            isset($options['inputs']) && !array_key_exists('events_label_id', $options['inputs'])
            && isset($options['defaultLabelId'])
        ) {
            $options['inputs']['events_label_id'] = (string)Hash::get($options, 'defaultLabelId');
        }

        // 予約フォーム項目の検索
        foreach ($formItemsTable->getSearchableFormItems(FormGroup::FORM_TYPE_RESERVATION) as $formItems) {
            foreach ($formItems as $formItem) {
                $formItem->getInputTypeItem()->buildSearchQuery($query, Hash::get($options, 'inputs', []));
            }
        }

        $userIdKey = Configure::readOrFail(
            'Master.adminSearchItems.itemsSearchInputKey.' . AdminSearchItem::ITEM_USER_ID
        );
        $userId = Hash::get($options, 'inputs.' . $userIdKey);
        if (isset($userId) && $userId !== '') {
            $query->where([
                'Reservations.user_id' => $userId,
            ]);
            unset($options['inputs'][$userIdKey]);
        }

        // 会員項目の検索
        $userQuery = $usersTable->selectQuery();
        $userQuery->select(['Users.id']);
        $userQuery = $usersTable->callFinder('search', $userQuery, [
            'search' => Hash::get($options, 'inputs', []),
        ]);

        foreach ($formItemsTable->getSearchableFormItems(FormGroup::FORM_TYPE_USER) as $formItems) {
            foreach ($formItems as $formItem) {
                $formItem->getInputTypeItem()->buildSearchQuery($userQuery, Hash::get($options, 'inputs', []));
            }
        }

        $userWhere = $userQuery->clause('where');
        if (isset($userWhere)) {
            $query->where([
                'Reservations.user_id IN' => $userQuery,
            ]);
        }

        $checked = Hash::get($options, 'checked');
        if (is_array($checked) && !isset($checked['allCheck'])) {
            $query->where([
                'Reservations.id IN' => $checked,
            ]);
        }

        $sort = Hash::get($options, 'inputs.sort', 'Reservations.id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'desc'));
        if ($sort === 'Reservations.usage_timestamp_from') {
            $query->order([
                    $sort => $direction,
                ] + [
                    'Reservations.usage_timestamp_from' => $direction,
                    'Reservations.usage_timestamp_to' => $direction,
                    'Events.sort_no' => $direction,
                    'Events.id' => $direction,
                    'Reservations.id' => $direction,
                ], true);
        } elseif ($sort === 'ReservationPayments.status') {
            $query->join([
                'ReservationPayments' => [
                    'table' => 'reservation_payments',
                    'type' => 'LEFT',
                    'conditions' => 'ReservationPayments.reservation_id = Reservations.id',
                ],
            ]);

            $orWhere = [];
            $orWhere[] = function ($expression) {
                $reservationPaymentsQuery = $this->getAssociation('ReservationPayments')->find();
                $reservationPaymentsQuery->select([1]);
                $reservationPaymentsQuery->from(['ReservationPayments2' => 'reservation_payments']);
                $reservationPaymentsQuery->where([
                    'ReservationPayments2.id > ReservationPayments.id',
                    'ReservationPayments2.reservation_id = ReservationPayments.reservation_id',
                ]);
                $expression->notExists($reservationPaymentsQuery);

                return $expression;
            };
            $orWhere[] = [
                'ReservationPayments.id IS NULL',
            ];
            $query->where([
                'OR' => $orWhere,
            ]);

            $nowDateTime = $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s');
            $case = $query->newExpr()->case();
            $case
                ->when([
                    'ReservationPayments.payment_limit <' => $nowDateTime,
                    'ReservationPayments.status' => ReservationPayment::STATUS_UNSETTLED,
                ])
                ->then(ReservationPayment::DISPLAY_STATUS_EXPIRED)
                ->else(new IdentifierExpression('ReservationPayments.status'));

            if (strtolower($direction) === 'desc') {
                $query->orderDesc($case, true);
            } else {
                $query->orderAsc($case, true);
            }

            $query->order([
                'Events.sort_no' => $direction,
                'Events.id' => $direction,
                'Reservations.usage_timestamp_from' => $direction,
                'Reservations.usage_timestamp_to' => $direction,
                'Reservations.id' => $direction,
            ]);
        } else {
            $query->order([
                    $sort => $direction,
                ] + [
                    'Events.sort_no' => $direction,
                    'Events.id' => $direction,
                    'Reservations.usage_timestamp_from' => $direction,
                    'Reservations.usage_timestamp_to' => $direction,
                    'Reservations.id' => $direction,
                ], true);
        }
        if (Hash::get($options, 'bufferOff', false)) {
            $query->disableBufferedResults();
        }

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * 受付状況一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReceptionSearchList(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_id',
            'event_id',
            'usage_timestamp_from',
            'usage_timestamp_to',
            'usage_time',
            'usage_day',
            'number',
            'charge',
            'reservation_status_id',
            'reception_status_id',
            'payment_method_id',
            'payment_status_id',
            'created',
            'modified',
            'user_deleted_flg' => $this->createUserDeletedExpression($query),
        ]);

        $query->join([
            'Events' => [
                'table' => 'events',
                'type' => 'LEFT',
                'conditions' => [
                    'Reservations.event_id = Events.id',
                ],
            ],
            'Users' => [
                'table' => 'users',
                'type' => 'LEFT',
                'conditions' => [
                    'Reservations.user_id = Users.id',
                ],
            ],
            'ReservationStatuses' => [
                'table' => 'reservation_statuses',
                'type' => 'INNER',
                'conditions' => 'ReservationStatuses.id = Reservations.reservation_status_id',
            ],
            'ReceptionStatuses' => [
                'table' => 'reception_statuses',
                'type' => 'LEFT',
                'conditions' => 'ReceptionStatuses.id = Reservations.reception_status_id',
            ],
        ]);

        $query->contain([
            'ReservationAdditions' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'form_item_id',
                    'value',
                ],
            ],
            'ReservationEventPlans' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'event_plan_id',
                ],
            ],
            'ReservationOptions' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'option_id',
                    'form_item_id',
                    'number',
                ],
            ],
            'ReservationVideoMeetings' => [
                'fields' => [
                    'id',
                    'reservation_id',
                    'organizer_id',
                    'video_meeting_url',
                    'video_meeting_id',
                    'video_meeting_password',
                    'video_meeting_type',
                ],
            ],
        ]);

        $query->where([
            'ReservationStatuses.status_type IN' => [
                ReservationStatus::STATUS_TYPE_FIXED,
                ReservationStatus::STATUS_TYPE_VISIT,
                ReservationStatus::STATUS_TYPE_ABSENCE,
            ],
        ]);

        // 予約利用日の当日
        $query = $this->conditionToday($query);

        if (Hash::get($options, 'bufferOff', false)) {
            $query->disableBufferedResults();
        }

        return $this->callFinder('search', $query, [
            'search' => Hash::get($options, 'inputs', []),
            'collection' => 'receptionStatuses',
        ]);
    }

    /**
     * 件数取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCount(Query $query, array $options)
    {
        $query->select(['Reservations.id']);

        // 会員ID
        $userId = Hash::get($options, 'inputs.user_id');
        if (is_scalar($userId) && ((string)$userId !== '') || is_array($userId) && !empty($userId)) {
            $query->where([
                'Reservations.user_id IN' => (array)$userId,
            ]);
        }

        // 予約枠ID
        $eventId = Hash::get($options, 'inputs.event_id');
        if (is_scalar($eventId) && ((string)$eventId !== '') || is_array($eventId) && !empty($eventId)) {
            $query->where([
                'Reservations.event_id IN' => (array)$eventId,
            ]);
        }

        // タイプ
        $type = Hash::get($options, 'inputs.type');
        if (is_scalar($type) && ((string)$type !== '') || is_array($type) && !empty($type)) {
            $query->matching('Events', function ($eventsQuery) use ($type) {
                $eventsQuery->where([
                    'Events.type IN' => (array)$type,
                ]);

                return $eventsQuery;
            });
        }

        // 受付時間
        $usageTimeFrom = Hash::get($options, 'inputs.usageTimeFrom');
        $usageTimeTo = Hash::get($options, 'inputs.usageTimeTo');
        if (((string)$usageTimeFrom) !== '' && ((string)$usageTimeTo) !== '') {
            $query->where([
                'OR' => [
                    'Reservations.usage_time <' => $usageTimeFrom,
                    'Reservations.usage_time >' => $usageTimeTo,
                ],
            ]);
        }

        // 日付
        $dateFrom = Hash::get($options, 'inputs.date_from');
        if (((string)$dateFrom) !== '') {
            $dateTime = new FrozenTime($dateFrom);
            $query->where([
                'Reservations.usage_timestamp_from >' => $dateTime->format('Y-m-d H:i:s'),
            ]);
        }
        $dateTo = Hash::get($options, 'inputs.date_to');
        if (((string)$dateTo) !== '') {
            $dateTime = new FrozenTime($dateTo);
            $query->where([
                'Reservations.usage_timestamp_to <' => $dateTime->format('Y-m-d H:i:s'),
            ]);
        }

        // 時間に内包される
        $timeFrom = Hash::get($options, 'inputs.time_from');
        $timeTo = Hash::get($options, 'inputs.time_to');
        if (((string)$timeFrom) !== '' && ((string)$timeTo) !== '') {
            $timeFrom = new FrozenTime($timeFrom);
            $timeTo = new FrozenTime($timeTo);

            if ($timeFrom->format('His') !== $timeTo->format('His')) {
                $query->where([
                    'Reservations.usage_time <' => 1440,
                ]);

                $castFrom = $this->driverExpression()->cast(
                    'Reservations.usage_timestamp_from',
                    TableSchema::TYPE_TIME
                );
                $castTo = $this->driverExpression()->cast('Reservations.usage_timestamp_to', TableSchema::TYPE_TIME);

                if ($timeFrom->format('His') < $timeTo->format('His')) {
                    $query->where([
                        'AND' => [
                            function ($exp) use ($castFrom, $castTo) {
                                return $exp->lt($castFrom, $castTo);
                            },
                            [
                                function ($exp) use ($castFrom, $timeFrom) {
                                    return $exp->gte($castFrom, $timeFrom->format('H:i:s'));
                                },

                                function ($exp) use ($castTo, $timeTo) {
                                    return $exp->lte($castTo, $timeTo->format('H:i:s'));
                                },
                            ],
                        ],
                    ]);
                } else {
                    $query->where([
                        'OR' => [
                            [
                                function (QueryExpression $exp) use ($castFrom, $castTo) {
                                    return $exp->lt($castFrom, $castTo, 'time');
                                },
                                'OR' => [
                                    function (QueryExpression $exp) use ($castFrom, $timeFrom) {
                                        return $exp->gte($castFrom, $timeFrom->format('H:i:s'));
                                    },
                                    function (QueryExpression $exp) use ($castTo, $timeTo) {
                                        return $exp->lte($castTo, $timeTo->format('H:i:s'));
                                    },
                                ],
                            ],
                            [
                                function (QueryExpression $exp) use ($castFrom, $castTo) {
                                    return $exp->gt($castFrom, $castTo);
                                },
                                [
                                    function (QueryExpression $exp) use ($castFrom, $timeFrom) {
                                        return $exp->gte($castFrom, $timeFrom->format('H:i:s'));
                                    },
                                    function (QueryExpression $exp) use ($castTo, $timeTo) {
                                        return $exp->lte($castTo, $timeTo->format('H:i:s'));
                                    },
                                ],
                            ],
                        ],
                    ]);
                }
            }
        }

        // 未来の予約
        $usageTimestampFuture = Hash::get($options, 'inputs.usage_timestamp_future', false);
        if ($usageTimestampFuture) {
            $query->where([
                'Reservations.usage_timestamp_to >=' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
            ]);
        }

        // ステータス
        $status = Hash::get($options, 'inputs.status', false);
        if (is_scalar($status) && ((string)$status !== '') || is_array($status) && !empty($status)) {
            $query->where([
                'Reservations.reservation_status_id IN' => (array)$status,
            ]);
        }

        // キャンセル以外
        $notCancel = Hash::get($options, 'inputs.not_cancel', false);
        if ($notCancel) {
            /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
            $reservationStatusesTable = $this->getAssociation('ReservationStatuses')->getTarget();

            $notCancelReservationStatusId = [];
            foreach ($reservationStatusesTable->getNotCancelData() as $data) {
                $notCancelReservationStatusId[] = $data['id'];
            }
            $query->where([
                'Reservations.reservation_status_id IN' => $notCancelReservationStatusId,
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
            'usage_timestamp_from' => $query->func()->min('Reservations.usage_timestamp_from', ['datetime']),
            'usage_timestamp_to' => $query->func()->max('Reservations.usage_timestamp_to', ['datetime']),
        ]);

        // 予約枠ID
        $eventId = Hash::get($options, 'inputs.event_id');
        if (is_scalar($eventId) && ((string)$eventId !== '') || is_array($eventId) && !empty($eventId)) {
            $query->where([
                'Reservations.event_id IN' => (array)$eventId,
            ]);
        }

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
        if (Hash::get($options, 'allStatus', false)) {
            $reservationStatusId = Hash::combine($reservationStatusesTable->getData(), '{*}.id', '{n}.id');
        } else {
            foreach ($reservationStatusesTable->getKeepStockData() as $data) {
                $reservationStatusId[] = $data['id'];
            }
        }

        $query->where([
            'Reservations.reservation_status_id IN' => $reservationStatusId,
        ]);

        $query->enableHydration(false);
        $query->disableBufferedResults();

        $query->formatResults(function ($results) {
            $result = $results->map(function ($data) {
                if (!isset($data['usage_timestamp_from']) || !isset($data['usage_timestamp_to'])) {
                    return null;
                }

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
     * 予約数の判定用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReserveNum(Query $query, array $options)
    {
        $query->select(['id']);

        // 予約枠ID
        $eventId = Hash::get($options, 'event_id');
        if (is_scalar($eventId) && ((string)$eventId !== '') || is_array($eventId) && !empty($eventId)) {
            $query->where([
                'Reservations.event_id IN' => (array)$eventId,
            ]);
        }

        $from = Hash::get($options, 'stock_range_from');
        if (!empty($from)) {
            $query->where([
                'Reservations.number <' => $from,
            ]);
        }

        $to = Hash::get($options, 'stock_range_to');
        if (!empty($to)) {
            $query->where([
                'Reservations.number >' => $to,
            ]);
        }

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
            'event_id' => 'Reservations.event_id',
            'usage_timestamp_from' => 'Reservations.usage_timestamp_from',
            'usage_timestamp_to' => 'Reservations.usage_timestamp_to',
            'number' => $query->func()->sum('Reservations.number'),
        ]);

        $query->contain([
            'Events' => [
                'fields' => [],
            ],
        ]);

        $eventId = Hash::get($options, 'inputs.event_id');
        $reservationStatusId = [];
        foreach ($reservationStatusesTable->getKeepStockData() as $data) {
            $reservationStatusId[] = $data['id'];
        }
        $query->where([
            'Reservations.event_id IN' => (array)$eventId,
            'Reservations.reservation_status_id IN' => $reservationStatusId,
        ]);

        $usageTimestampFrom = Hash::get($options, 'inputs.usage_timestamp_from');
        $usageTimestampTo = Hash::get($options, 'inputs.usage_timestamp_to');
        if (isset($usageTimestampFrom) && isset($usageTimestampTo)) {
            $query->where(
                $this->createWhereForUsageTimestampWithInterval($query, $usageTimestampFrom, $usageTimestampTo)
            );
        }

        $excludeId = Hash::get($options, 'inputs.exclude_id');
        if (is_scalar($excludeId) && ((string)$excludeId !== '') || is_array($excludeId) && !empty($excludeId)) {
            $query->where([
                'Reservations.id NOT IN' => (array)$excludeId,
            ]);
        }

        $query->group([
            'Reservations.event_id',
            'Reservations.usage_timestamp_from',
            'Reservations.usage_timestamp_to',
        ]);

        $query->enableHydration(false);
        $query->disableBufferedResults();

        return $query;
    }

    /**
     * カレンダーの予約済み表示のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findUserCalendar(Query $query, array $options)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $query->select([
            'event_id' => 'Reservations.event_id',
            'usage_timestamp_from' => 'Reservations.usage_timestamp_from',
            'usage_timestamp_to' => 'Reservations.usage_timestamp_to',
        ]);

        $userId = Hash::get($options, 'inputs.user_id');
        $eventId = Hash::get($options, 'inputs.event_id');
        $reservationStatusId = [];
        foreach ($reservationStatusesTable->getNotCancelData() as $data) {
            $reservationStatusId[] = $data['id'];
        }

        $query->where([
            'Reservations.user_id IN' => (array)$userId,
            'Reservations.event_id IN' => (array)$eventId,
            'Reservations.reservation_status_id IN' => $reservationStatusId,
        ]);

        $usageTimestampFrom = Hash::get($options, 'inputs.usage_timestamp_from');
        if (isset($usageTimestampFrom)) {
            $query->where([
                'Reservations.usage_timestamp_to >' => $usageTimestampFrom,
            ]);
        }
        $usageTimestampTo = Hash::get($options, 'inputs.usage_timestamp_to');
        if (isset($usageTimestampTo)) {
            $query->where([
                'Reservations.usage_timestamp_from <' => $usageTimestampTo,
            ]);
        }

        $query->group([
            'Reservations.event_id',
            'Reservations.usage_timestamp_from',
            'Reservations.usage_timestamp_to',
        ]);

        $query->enableHydration(false);
        $query->disableBufferedResults();

        return $query;
    }

    /**
     * 台帳詳細のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCalendarDetail(Query $query, array $options)
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $query->select([
            'id',
            'user_id',
            'event_id',
            'usage_timestamp_from',
            'usage_timestamp_to',
            'number',
            'reservation_status_id',
            'user_deleted_flg' => $this->createUserDeletedExpression($query),
        ]);
        $query->contain([
            'Users' => [
                'fields' => [
                    'id',
                    'user_authority_id',
                    'login_id',
                    'mail',
                ],
                'joinType' => Query::JOIN_TYPE_LEFT,
            ],
            'Users.UserAuthorities' => [
                'fields' => [
                    'id',
                    'name',
                ],
                'joinType' => Query::JOIN_TYPE_LEFT,
            ],
        ]);

        $formItemId = Hash::get($options, 'inputs.display_item');
        if (((string)$formItemId) !== '') {
            $query->contain([
                'Users.UserAdditions' => [
                    'fields' => [
                        'user_id',
                        'form_item_id',
                        'value',
                    ],
                    'queryBuilder' => function ($userAdditionQuery) use ($formItemId) {
                        $userAdditionQuery->where([
                            'UserAdditions.form_item_id' => $formItemId,
                        ]);

                        return $userAdditionQuery;
                    },
                ],
                'ReservationAdditions' => [
                    'fields' => [
                        'reservation_id',
                        'form_item_id',
                        'value',
                    ],
                    'queryBuilder' => function ($reservationAdditionQuery) use ($formItemId) {
                        $reservationAdditionQuery->where([
                            'ReservationAdditions.form_item_id' => $formItemId,
                        ]);

                        return $reservationAdditionQuery;
                    },
                ],
            ]);

            $formItem = $formItemsTable->getFormItem((int)$formItemId);
            if (!isset($formItem)) {
                throw new CakeException();
            }
            if ($formItem->getInputTypeItem() instanceof OptionTypeInterface) {
                $query->contain([
                    'ReservationOptions' => [
                        'fields' => [
                            'id',
                            'reservation_id',
                            'option_id',
                            'form_item_id',
                            'number',
                        ],
                    ],
                ]);
            }
        }

        $eventId = Hash::get($options, 'inputs.event_id');
        $usageTimestampFrom = Hash::get($options, 'inputs.usage_timestamp_from');
        $usageTimestampTo = Hash::get($options, 'inputs.usage_timestamp_to');
        $reservationStatusId = [];
        foreach ($reservationStatusesTable->getNotCancelData() as $data) {
            $reservationStatusId[] = $data['id'];
        }

        $query->where([
            'Reservations.event_id IN' => (array)$eventId,
            'Reservations.usage_timestamp_from <' => $usageTimestampTo,
            'Reservations.usage_timestamp_to >' => $usageTimestampFrom,
            'Reservations.reservation_status_id IN' => $reservationStatusId,
        ]);

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'desc'));
        $query->order([
                'Reservations.' . $sort => $direction,
            ] + [
                'Reservations.usage_timestamp_from' => $direction,
                'Reservations.usage_timestamp_to' => $direction,
                'Reservations.id' => $direction,
            ], true);

        return $query;
    }

    /**
     * 台帳の件数表示のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCalendarReservationCount(Query $query, array $options)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $query->select([
            'event_id' => 'Reservations.event_id',
            'usage_timestamp_from' => 'Reservations.usage_timestamp_from',
            'usage_timestamp_to' => 'Reservations.usage_timestamp_to',
            'reservation_status_id' => 'Reservations.reservation_status_id',
            'count' => $query->func()->count('Reservations.id'),
        ]);

        $query->contain([
            'Events' => [
                'fields' => [],
            ],
        ]);

        $eventId = Hash::get($options, 'inputs.event_id');
        $reservationStatusId = [];
        foreach ($reservationStatusesTable->getNotCancelData() as $data) {
            $reservationStatusId[] = $data['id'];
        }
        $query->where([
            'Reservations.event_id IN' => (array)$eventId,
            'Reservations.reservation_status_id IN' => $reservationStatusId,
        ]);

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

        $query->group([
            'Reservations.event_id',
            'Reservations.usage_timestamp_from',
            'Reservations.usage_timestamp_to',
            'Reservations.reservation_status_id',
        ]);

        $query->enableHydration(false);
        $query->disableBufferedResults();

        return $query;
    }

    /**
     * 台帳表示のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCalendarDisplay(Query $query, array $options)
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $formItemId = Hash::get($options, 'inputs.display_item');

        $query->select([
            'id',
            'user_id',
            'event_id',
            'usage_timestamp_from',
            'usage_timestamp_to',
        ]);
        $query->contain([
            'Users' => [
                'fields' => [
                    'id',
                    'user_authority_id',
                    'login_id',
                    'mail',
                ],
                'joinType' => Query::JOIN_TYPE_LEFT,
            ],
            'Users.UserAdditions' => [
                'fields' => [
                    'user_id',
                    'form_item_id',
                    'value',
                ],
                'queryBuilder' => function ($userAdditionQuery) use ($formItemId) {
                    $userAdditionQuery->where([
                        'UserAdditions.form_item_id' => $formItemId,
                    ]);

                    return $userAdditionQuery;
                },
            ],
            'Users.UserAuthorities' => [
                'fields' => [
                    'id',
                    'name',
                ],
                'joinType' => Query::JOIN_TYPE_LEFT,
            ],
            'ReservationAdditions' => [
                'fields' => [
                    'reservation_id',
                    'form_item_id',
                    'value',
                ],
                'queryBuilder' => function ($reservationAdditionQuery) use ($formItemId) {
                    $reservationAdditionQuery->where([
                        'ReservationAdditions.form_item_id' => $formItemId,
                    ]);

                    return $reservationAdditionQuery;
                },
            ],
        ]);

        $formItem = $formItemsTable->getFormItem((int)$formItemId);
        if (!isset($formItem)) {
            throw new CakeException();
        }
        if ($formItem->getInputTypeItem() instanceof OptionTypeInterface) {
            $query->contain([
                'ReservationOptions' => [
                    'fields' => [
                        'id',
                        'reservation_id',
                        'option_id',
                        'form_item_id',
                        'number',
                    ],
                ],
            ]);
        }

        $eventId = Hash::get($options, 'inputs.event_id');
        $reservationStatusTypes = [
            ReservationStatus::STATUS_TYPE_FIXED,
            ReservationStatus::STATUS_TYPE_VISIT,
        ];
        $reservationStatusId = [];
        foreach ($reservationStatusesTable->getData() as $reservationStatus) {
            if (ArrayUtility::inArray($reservationStatus['status_type'], $reservationStatusTypes)) {
                $reservationStatusId[] = $reservationStatus['id'];
            }
        }
        $query->where([
            'Reservations.event_id IN' => (array)$eventId,
            'Reservations.reservation_status_id IN' => $reservationStatusId,
        ]);

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

        $notExistsQuery = $this->find();
        $notExistsQuery->select([1]);
        $notExistsQuery->from(['Reservations2' => 'Reservations']);
        $notExistsQuery->where([
            'Reservations2.id <> Reservations.id',
            'Reservations2.event_id = Reservations.event_id',
            'Reservations2.usage_timestamp_from < Reservations.usage_timestamp_to',
            'Reservations2.usage_timestamp_to > Reservations.usage_timestamp_from',
            'Reservations2.reservation_status_id IN' => $reservationStatusId,
        ]);
        $query->where($query->newExpr()->notExists($notExistsQuery));

        $query->order([
            'Reservations.event_id' => 'ASC',
            'Reservations.usage_timestamp_from' => 'ASC',
            'Reservations.usage_timestamp_to' => 'ASC',
            'Reservations.id' => 'ASC',
        ], true);

        return $query;
    }

    /**
     * 重複予約チェック時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDuplicationCheck(Query $query, array $options)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getAssociation('ReservationStatuses')->getTarget();

        $query->select(['Reservations.id']);
        $query->contain([
            'Events' => [
                'fields' => [],
            ],
        ]);

        // 重複予約チェック
        $query->where([
            'Events.duplication_check_flg' => EventEntity::COMMON_FLG_ON,
        ]);

        // 会員ID
        $userId = Hash::get($options, 'inputs.user_id');
        $query->where([
            'Reservations.user_id IN' => (array)$userId,
        ]);

        // 利用日時
        $usageTimestampFrom = Hash::get($options, 'inputs.usage_timestamp_from');
        $usageTimestampTo = Hash::get($options, 'inputs.usage_timestamp_to');
        $query->where([
            'Reservations.usage_timestamp_from <' => $usageTimestampTo,
            'Reservations.usage_timestamp_to >' => $usageTimestampFrom,
        ]);

        // キャンセル以外
        $notCancelReservationStatusId = [];
        foreach ($reservationStatusesTable->getNotCancelData() as $data) {
            $notCancelReservationStatusId[] = $data['id'];
        }
        $query->where([
            'Reservations.reservation_status_id IN' => $notCancelReservationStatusId,
        ]);

        // 除外ID
        $excludeId = Hash::get($options, 'inputs.exclude_id');
        if (is_scalar($excludeId) && ((string)$excludeId !== '') || is_array($excludeId) && !empty($excludeId)) {
            $query->where([
                'Reservations.id NOT IN' => (array)$excludeId,
            ]);
        }

        return $query;
    }

    /**
     * 回数制限チェック時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReservationLimit(Query $query, array $options)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getAssociation('ReservationStatuses')->getTarget();

        $query->select(['Reservations.id']);
        $query->contain([
            'Events' => [
                'fields' => [],
            ],
        ]);

        // 会員ID
        $userId = Hash::get($options, 'inputs.user_id');
        $query->where([
            'Reservations.user_id IN' => (array)$userId,
        ]);

        // 予約枠ID
        $eventId = Hash::get($options, 'inputs.event_id');
        if (is_scalar($eventId) && ((string)$eventId !== '') || is_array($eventId) && !empty($eventId)) {
            $query->where([
                'Reservations.event_id IN' => (array)$eventId,
            ]);
        }

        // 期間
        $periodType = Hash::get($options, 'inputs.period_type');
        $usageTimestampFrom = Hash::get($options, 'inputs.usage_timestamp_from');
        if ($periodType === 'future') {
            $query->where([
                'Reservations.usage_timestamp_from >' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
            ]);
        } elseif ($periodType === 'month') {
            $monthFrom = new FrozenTime($usageTimestampFrom->format('Y-m-01'));
            $monthTo = clone $monthFrom;
            $monthTo = $monthTo->addMonths(1);
            $query->where([
                'Reservations.usage_timestamp_from >=' => $monthFrom,
                'Reservations.usage_timestamp_from <' => $monthTo,
            ]);
        } elseif ($periodType === 'day') {
            $dayFrom = new FrozenTime($usageTimestampFrom->format('Y-m-d'));
            $dayTo = clone $dayFrom;
            $dayTo = $dayTo->addDays(1);
            $query->where([
                'Reservations.usage_timestamp_from >=' => $dayFrom,
                'Reservations.usage_timestamp_from <' => $dayTo,
            ]);
        }

        // キャンセル以外
        $notCancelReservationStatusId = [];
        foreach ($reservationStatusesTable->getNotCancelData() as $data) {
            $notCancelReservationStatusId[] = $data['id'];
        }
        $query->where([
            'Reservations.reservation_status_id IN' => $notCancelReservationStatusId,
        ]);

        // 除外ID
        $excludeId = Hash::get($options, 'inputs.exclude_id');
        if (is_scalar($excludeId) && ((string)$excludeId !== '') || is_array($excludeId) && !empty($excludeId)) {
            $query->where([
                'Reservations.id NOT IN' => (array)$excludeId,
            ]);
        }

        return $query;
    }

    /**
     * リマインダー送信時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReminder(Query $query, array $options)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $query = $this->callFinder('edit', $query, $options);

        $reminderInterval = Hash::get($options, 'inputs.reminder_interval');
        $targetDateTime = Hash::get($options, 'inputs.target_date_time');

        $reminderTimestamp = $siteSettingsTable->getData()->getReminderDateTime($reminderInterval, $targetDateTime);
        $reminderTimestamp = $reminderTimestamp->addMinutes($reminderInterval);

        $query->where([
            'Reservations.usage_timestamp_from <' => $reminderTimestamp,
            'Reservations.usage_timestamp_from >=' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
        ]);

        $notCancelReservationStatusId = [];
        foreach ($reservationStatusesTable->getNotCancelData() as $data) {
            $notCancelReservationStatusId[] = $data['id'];
        }
        $query->where([
            'Reservations.reservation_status_id IN' => $notCancelReservationStatusId,
        ]);

        // 決済不要の予約、あるいはreservation_payments.statusが決済済みの予約を送信対象とする
        // hasMany ReservationPaymentsでありReservationPayments.reservation_idもユニーク制約はないためサブクエリ
        // 直接LEFT JOINすると紐づくreservation_paymentsが複数の場合に同じ予約レコードが複数となり、リマインダーが複数回送信されてしまうため
        $query->where(['Reservations.id IN' =>
            $this
                ->selectQuery()
                ->select(['Reservations.id'])
                ->distinct(['Reservations.id'])
                ->join([
                    'ReservationPayments' => [
                        'table' => 'reservation_payments',
                        'type' => 'LEFT',
                        'conditions' => [
                            'ReservationPayments.reservation_id = Reservations.id',
                        ],
                    ],
                ])
                ->where([
                    'OR' => [
                        'ReservationPayments.status IS NULL',
                        'ReservationPayments.status' => ReservationPayment::STATUS_COMPLETED,
                    ],
                ]),
        ]);

        $query->innerJoinWith('Users', function ($usersQuery) {
            $usersQuery->where([
                'Users.mail IS NOT NULL',
            ]);

            return $usersQuery;
        });

        $notExistsQuery = $autoReplyMailHistoriesTable->find();
        $notExistsQuery->select([1]);
        $notExistsQuery->innerJoinWith('AutoReplyMails', function ($autoReplyMailsQuery) {
            $autoReplyMailsQuery->where([
                'AutoReplyMails.type' => AutoReplyMail::TYPE_RESERVE_REMINDER,
            ]);

            return $autoReplyMailsQuery;
        });
        $notExistsQuery->where([
            'AutoReplyMailHistories.reservation_id = Reservations.id',
            'AutoReplyMailHistories.reminder_setting_timestamp <=' => $query->func()->dateAdd(
                'Reservations.usage_timestamp_from',
                (string)(-1 * $siteSettingsTable->getData()->getReminderMinute()),
                'minute'
            ),
            'AutoReplyMailHistories.reminder_setting_timestamp >' => $query->func()->dateAdd(
                'Reservations.usage_timestamp_from',
                (string)(-1 * $siteSettingsTable->getData()->getReminderMinute() - $reminderInterval),
                'minute'
            ),
        ]);
        $query->where($query->newExpr()->notExists($notExistsQuery));

        return $query;
    }

    /**
     * 利用終了リマインダー送信時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCloseReminder(Query $query, array $options)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $query = $this->callFinder('edit', $query, $options);

        $reminderInterval = Hash::get($options, 'inputs.reminder_interval');
        $targetDateTime = Hash::get($options, 'inputs.target_date_time');

        $reminderTimestamp = $siteSettingsTable->getData()->getCloseReminderDateTime(
            $reminderInterval,
            $targetDateTime
        );
        $reminderTimestamp = $reminderTimestamp->addMinutes($reminderInterval);

        $query->where([
            'Reservations.usage_timestamp_to <' => $reminderTimestamp,
            'Reservations.usage_timestamp_to >=' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
        ]);

        $notCancelReservationStatusId = [];
        foreach ($reservationStatusesTable->getNotCancelData() as $data) {
            $notCancelReservationStatusId[] = $data['id'];
        }
        $query->where([
            'Reservations.reservation_status_id IN' => $notCancelReservationStatusId,
        ]);

        // 決済不要の予約、あるいはreservation_payments.statusが決済済みの予約を送信対象とする
        // hasMany ReservationPaymentsでありReservationPayments.reservation_idもユニーク制約はないためサブクエリ
        // 直接LEFT JOINすると紐づくreservation_paymentsが複数の場合に同じ予約レコードが複数となり、リマインダーが複数回送信されてしまうため
        $query->where(['Reservations.id IN' =>
            $this
                ->selectQuery()
                ->select(['Reservations.id'])
                ->distinct(['Reservations.id'])
                ->join([
                    'ReservationPayments' => [
                        'table' => 'reservation_payments',
                        'type' => 'LEFT',
                        'conditions' => [
                            'ReservationPayments.reservation_id = Reservations.id',
                        ],
                    ],
                ])
                ->where([
                    'OR' => [
                        'ReservationPayments.status IS NULL',
                        'ReservationPayments.status' => ReservationPayment::STATUS_COMPLETED,
                    ],
                ]),
        ]);

        $query->innerJoinWith('Users', function ($usersQuery) {
            $usersQuery->where([
                'Users.mail IS NOT NULL',
            ]);

            return $usersQuery;
        });

        $notExistsQuery = $autoReplyMailHistoriesTable->find();
        $notExistsQuery->select([1]);
        $notExistsQuery->innerJoinWith('AutoReplyMails', function ($autoReplyMailsQuery) {
            $autoReplyMailsQuery->where([
                'AutoReplyMails.type' => AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE,
            ]);

            return $autoReplyMailsQuery;
        });
        $notExistsQuery->where([
            'AutoReplyMailHistories.reservation_id = Reservations.id',
            'AutoReplyMailHistories.reminder_setting_timestamp <=' => $query->func()->dateAdd(
                'Reservations.usage_timestamp_to',
                (string)(-1 * $siteSettingsTable->getData()->getCloseReminderMinute()),
                'minute'
            ),
            'AutoReplyMailHistories.reminder_setting_timestamp >' => $query->func()->dateAdd(
                'Reservations.usage_timestamp_to',
                (string)(-1 * $siteSettingsTable->getData()->getCloseReminderMinute() - $reminderInterval),
                'minute'
            ),
        ]);
        $query->where($query->newExpr()->notExists($notExistsQuery));

        return $query;
    }

    /**
     * インターバル時間を含む利用日時の検索条件を生成
     *
     * @param \Cake\ORM\Query $query クエリ
     * @param string|\DateTimeInterface $usageTimestampFrom 開始日時
     * @param string|\DateTimeInterface $usageTimestampTo 終了日時
     * @return array
     */
    protected function createWhereForUsageTimestampWithInterval($query, $usageTimestampFrom, $usageTimestampTo)
    {
        $where = [
            [
                'OR' => [
                    [
                        'Events.interval_time IS NULL',
                        'Events.interval_day IS NULL',
                        'Reservations.usage_timestamp_from <' => $usageTimestampTo,
                        'Reservations.usage_timestamp_to >' => $usageTimestampFrom,
                    ],
                    [
                        'Events.interval_time IS NOT NULL',
                        $query->newExpr()->lt(
                            $this->driverExpression()->expressionDateAdd(
                                'Reservations.usage_timestamp_from',
                                '- Events.interval_time',
                                'minute'
                            ),
                            $usageTimestampTo
                        ),
                        $query->newExpr()->gt(
                            $this->driverExpression()->expressionDateAdd(
                                'Reservations.usage_timestamp_to',
                                'Events.interval_time',
                                'minute'
                            ),
                            $usageTimestampFrom
                        ),
                    ],
                    [
                        'Events.interval_day IS NOT NULL',
                        $query->newExpr()->lt(
                            $this->driverExpression()->expressionDateAdd(
                                'Reservations.usage_timestamp_from',
                                '- Events.interval_day',
                                'day'
                            ),
                            $usageTimestampTo
                        ),
                        $query->newExpr()->gt(
                            $this->driverExpression()->expressionDateAdd(
                                'Reservations.usage_timestamp_to',
                                'Events.interval_day',
                                'day'
                            ),
                            $usageTimestampFrom
                        ),
                    ],
                ],
            ],
        ];

        return $where;
    }

    /**
     * パラメータのデータを取得
     *
     * @param array $parameters パラメータ
     * @param array|null $continuousParameter 連続予約パラメータ
     * @param \App\Model\Entity\Reservation|null $reservation エンティティ
     * @param bool|null $isAdmin 管理者側フラグ
     * @return array
     */
    public function getReservationParametersData(
        array $parameters,
        ?array $continuousParameter = null,
        ?Reservation $reservation = null,
        ?bool $isAdmin = null
    ) {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        if (!isset($isAdmin)) {
            $isAdmin = $this->commonData()->existsAdminLoginData();
        }

        $returnErrors = function ($errors) {
            if (!is_array($errors)) {
                return ['errors' => ['error' => $errors]];
            }

            return ['errors' => ['reservations' => $errors]];
        };

        if (isset($continuousParameter['parameter'])) {
            $parameters = array_merge($parameters, $continuousParameter['parameter']);
        }

        $validator = $this->validationParameter(new KuchenValidator());
        $errors = $validator->validate($parameters);
        if (!empty($errors)) {
            return call_user_func($returnErrors, $errors);
        }

        // 予約
        if (isset($reservation) && !$reservation->canView($isAdmin)) {
            return call_user_func($returnErrors, [
                'id' => [__(Message::ERROR_NOT_EXISTS)],
            ]);
        }

        // 予約タイプ
        $reservationType = null;
        if (!isset($reservation)) {
            $reservationType = Hash::get($parameters, 'reservation_type');
            if ($isAdmin) {
                // 管理側は会員指定が初期値
                if (((string)$reservationType) === '') {
                    $reservationType = static::RESERVATION_TYPE_EXISTING_USER;
                }
            } else {
                // 公開側はログイン状態で制御
                if ($this->commonData()->existsUserLoginData()) {
                    $reservationType = static::RESERVATION_TYPE_EXISTING_USER;
                } else {
                    $userReservationTypes = [];
                    if ($siteSettingsTable->getData()->isUseFlgOn('reservation_add_user_flg')) {
                        $userReservationTypes[] = static::RESERVATION_TYPE_NEW_USER;
                    }
                    if ($siteSettingsTable->getData()->isUseFlgOn('reservation_add_not_user_flg')) {
                        $userReservationTypes[] = static::RESERVATION_TYPE_NON_USER;
                    }

                    // 選択した予約タイプが利用不可になっていた場合はエラー
                    if (isset($reservationType) && !ArrayUtility::inArray($reservationType, $userReservationTypes)) {
                        return call_user_func($returnErrors, [
                            'id' => [__(Message::ERROR_NOT_EXISTS)],
                        ]);
                    }

                    if (!empty($userReservationTypes)) {
                        if (!ArrayUtility::inArray($reservationType, $userReservationTypes)) {
                            $reservationType = reset($userReservationTypes);
                        }
                    } else {
                        $reservationType = null;
                    }
                }
            }
        }

        // 会員
        $user = null;
        $userId = null;
        if (((string)$reservationType) === ((string)static::RESERVATION_TYPE_EXISTING_USER) || isset($reservation)) {
            if (!isset($reservation)) {
                // 新規登録時
                if ($isAdmin) {
                    // 管理側は指定された会員
                    $userId = Hash::get($parameters, 'user_id');
                } else {
                    // 公開側はログイン中の会員
                    $userId = $this->commonData()->getUserLoginData()->get('id');
                }
            } else {
                // 編集時は会員固定
                $userId = $reservation->get('user_id');
            }
        }
        if (((string)$userId) !== '') {
            // 会員データ取得
            try {
                /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                $user = $usersTable->get($userId, [
                    'finder' => 'reservation',
                ]);
            } catch (RecordNotFoundException $e) {
                return call_user_func($returnErrors, [
                    'user_id' => [__(Message::ERROR_NOT_EXISTS)],
                ]);
            }

            // 新規登録時に非会員、退会済みの会員は指定不可
            if (!isset($reservation) && ($user->isGuest() || $user->withdrew())) {
                return call_user_func($returnErrors, [
                    'user_id' => [__(Message::ERROR_NOT_EXISTS)],
                ]);
            }
            if (isset($reservation)) {
                $reservation->setUserEntity($user);
            }
        }

        // 会員権限
        $userAuthorityId = null;
        if (isset($user)) {
            // 会員指定時は会員に設定された権限
            $userAuthorityId = $user->get('user_authority_id');
        } else {
            $guestAuthorityId = $userAuthoritiesTable->getGuestAuthority()->get('id');
            $defaultAuthorityId = $userAuthoritiesTable->getDefaultAuthority()->get('id');
            if (((string)$reservationType) === ((string)static::RESERVATION_TYPE_NEW_USER)) {
                // 会員登録時
                if ($isAdmin) {
                    // 管理側はデフォルト権限が初期値
                    $userAuthorityId = Hash::get($parameters, 'user_authority_id');
                    if (
                        ((string)$userAuthorityId) === ''
                        || ((string)$userAuthorityId) === ((string)$guestAuthorityId)
                    ) {
                        $userAuthorityId = $defaultAuthorityId;
                    }
                } else {
                    // 公開側はデフォルト権限固定
                    $userAuthorityId = $defaultAuthorityId;
                }
            } elseif (((string)$reservationType) === ((string)static::RESERVATION_TYPE_NON_USER)) {
                // 非会員の場合はゲスト権限
                $userAuthorityId = $guestAuthorityId;
            }
        }

        // 予約枠
        $eventId = Hash::get($parameters, 'event_id');
        if (((string)$eventId) === '' && isset($reservation)) {
            $eventId = $reservation->get('event_id');
        }
        if (((string)$eventId) === '') {
            return call_user_func($returnErrors, [
                'event_id' => [__(Message::ERROR_NOT_EMPTY)],
            ]);
        }
        $event = null;
        try {
            $event = $eventsTable->get($eventId, [
                'finder' => 'reservation',
            ]);
            if (isset($reservation)) {
                $reservation->setEventEntity($event);
            }
        } catch (RecordNotFoundException $e) {
            return call_user_func($returnErrors, [
                'event_id' => [__(Message::ERROR_NOT_EXISTS)],
            ]);
        }

        // 開始日時
        $usageTimestampFrom = Hash::get($parameters, 'usage_timestamp_from');
        if (((string)$usageTimestampFrom) === '' && isset($reservation)) {
            $usageTimestampFrom = $reservation->get('usage_timestamp_from');
        }
        $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($usageTimestampFrom);
        if (!isset($usageTimestampFrom)) {
            return call_user_func($returnErrors, [
                'usage_timestamp_from' => [__(Message::ERROR_NOT_EMPTY)],
            ]);
        }
        if (!$event->existsUsageTimestampFrom($usageTimestampFrom)) {
            return call_user_func($returnErrors, [
                'event_error' => [__(Message::ERROR_NOT_EXISTS_PERIOD)],

            ]);
        }

        // 公開側は非公開の枠に対して新規登録不可
        if (!$isAdmin && !$event->isPublic()) {
            $originalEventId = null;
            $originalUsageTimestampFrom = null;
            if (isset($reservation)) {
                $originalEventId = $reservation->get('event_id');
                $originalUsageTimestampFrom = DateTimeUtility::convertToDateTimeObject(
                    $reservation->get('usage_timestamp_from')
                );
            }

            if (
                !isset($originalEventId) || !isset($originalUsageTimestampFrom)
                || ((string)$event->get('id')) !== ((string)$originalEventId)
                || $usageTimestampFrom->format('Y-m-d H:i') !== $originalUsageTimestampFrom->format('Y-m-d H:i')
            ) {
                return call_user_func($returnErrors, [
                    'event_id' => [__(Message::ERROR_NOT_EXISTS)],
                ]);
            }
        }

        return [
            'parameters' => [
                'reservation_type' => $reservationType,
                'user_id' => $userId,
                'user_authority_id' => $userAuthorityId,
                'event_id' => $eventId,
                'usage_timestamp_from' => $usageTimestampFrom->format('Y/m/d H:i'),
            ],
            'event' => $event,
            'user' => $user,
        ];
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
        $parameters = Hash::get($options, 'otherOptions.parameters');
        if (!empty($parameters)) {
            $data->offsetSet('user_id', $parameters['user_id']);
            $data->offsetSet('event_id', $parameters['event_id']);
            $data->offsetSet('usage_timestamp_from', $parameters['usage_timestamp_from']);
        }

        if (Hash::get($options, 'validate', true) === true) {
            $formGroups = Hash::get($options, 'otherOptions.formGroups', []);

            $this->setValidator(
                'formItems',
                $this->createFormItemValidators($this->createValidator('default'), $formGroups)
            );
            $options->offsetSet('validate', 'formItems');
        }
    }

    /**
     * フォーム項目の入力値をフィルタリング
     *
     * @param array $inputs 入力値
     * @param array $formGroups フォームグループ
     * @return array
     */
    public function filterFormItemInputs($inputs, $formGroups)
    {
        $additionInputs = Hash::get($inputs, 'addition_values', []);
        unset($inputs['addition_values']);
        $optionInputs = Hash::get($inputs, 'option_values', []);
        unset($inputs['option_values']);

        foreach ($formGroups as $formGroup) {
            foreach ((array)$formGroup->get('form_items') as $formItem) {
                /** @var \App\Model\InputType\AbstractInputTypeItem $inputTypeItem */
                $inputTypeItem = $formItem->getInputTypeItem();

                if ($inputTypeItem instanceof InputInterface) {
                    if ($inputTypeItem instanceof OptionTypeInterface) {
                        $optionInputs = $inputTypeItem->filterInputs($optionInputs);
                    } elseif ($inputTypeItem instanceof AdditionTypeInterface) {
                        $additionInputs = $inputTypeItem->filterInputs($additionInputs);
                    } else {
                        $inputs = $inputTypeItem->filterInputs($inputs);
                    }
                }
            }
        }
        $inputs['addition_values'] = $additionInputs;
        $inputs['option_values'] = $optionInputs;

        return $inputs;
    }

    /**
     * フォーム項目のバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @param array $formGroups フォームグループ
     * @return \Cake\Validation\Validator
     */
    protected function createFormItemValidators($validator, $formGroups)
    {
        $additionValidator = new KuchenValidator();
        $optionValidator = new KuchenValidator();

        foreach ($formGroups as $formGroup) {
            foreach ((array)$formGroup->get('form_items') as $formItem) {
                /** @var \App\Model\InputType\AbstractInputTypeItem $inputTypeItem */
                $inputTypeItem = $formItem->getInputTypeItem();

                if ($inputTypeItem instanceof InputInterface && $inputTypeItem->canInput()) {
                    if ($inputTypeItem instanceof OptionTypeInterface) {
                        $optionValidator = $inputTypeItem->buildFieldsetValidator($optionValidator);
                    } elseif ($inputTypeItem instanceof AdditionTypeInterface) {
                        $additionValidator = $inputTypeItem->buildFieldsetValidator($additionValidator);
                    } else {
                        $validator = $inputTypeItem->buildFieldsetValidator($validator);
                    }
                }
            }
        }

        $validator->addNested('addition_values', $additionValidator);
        $validator->requirePresence('addition_values', false);
        $validator->allowEmptyString('addition_values');

        $validator->addNested('option_values', $optionValidator);
        $validator->requirePresence('option_values', false);
        $validator->allowEmptyString('option_values');

        return $validator;
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
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');
        /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
        $paymentStatusesTable = $this->getTableLocator()->get('PaymentStatuses');
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if ($entity->hasErrors()) {
            return;
        }
        if (!($entity instanceof Reservation)) {
            throw new CakeException();
        }

        $userEntity = Hash::get($options, 'otherOptions.user');
        if (isset($userEntity)) {
            $entity->setUserEntity($userEntity);
        }

        $eventEntity = Hash::get($options, 'otherOptions.event');
        if (!isset($eventEntity)) {
            $eventEntity = $entity->getEventEntity();
            if (!isset($eventEntity)) {
                return;
            }
        }
        $entity->setEventEntity($eventEntity);

        $isAdmin = Hash::get((array)$options, 'otherOptions.isAdmin', false);
        $isChangeForm = Hash::get((array)$options, 'otherOptions.isChangeForm', false);

        if (!$isChangeForm) {
            // 表示パターン設定で使用されていない値を削除する
            $entity->unsetUnusedValues('ReservationAdditions');
            $entity->unsetUnusedOptions();
        }

        // 利用時間
        if ((string)$eventEntity->get('time_plan') === ((string)EventEntity::PLAN_SINGLE)) {
            if ($eventEntity->isFixedUsageTimeValueOptions($isAdmin)) {
                $timeValueOptions = array_keys($eventEntity->getUsageTimeValueOptions($isAdmin));
                $entity->set('usage_time', reset($timeValueOptions));
            }
            if ($eventEntity->isFixedUsageDayValueOptions($isAdmin)) {
                $dayValueOptions = array_keys($eventEntity->getUsageDayValueOptions($isAdmin));
                $entity->set('usage_day', reset($dayValueOptions));
            }
        } elseif ((string)$eventEntity->get('time_plan') === ((string)EventEntity::PLAN_MULTIPLE)) {
            $eventPlans = [];
            foreach ($eventEntity->get('event_plans') as $eventPlan) {
                $eventPlans[$eventPlan->get('id')] = $eventPlan;
            }

            if ((string)$eventEntity->get('type') === ((string)EventEntity::TYPE_TIME)) {
                $usageTime = 0;
                foreach ((array)$entity->get('reservation_event_plans') as $reservationEventPlan) {
                    if (isset($eventPlans[$reservationEventPlan->get('event_plan_id')])) {
                        $usageTime += $eventPlans[$reservationEventPlan->get('event_plan_id')]->get('usage_time');
                    }
                }
                $entity->set('usage_time', $usageTime);
                $entity->set('usage_day', 1);
            } elseif ((string)$eventEntity->get('type') === ((string)EventEntity::TYPE_DAY)) {
                $usageDay = 0;
                foreach ((array)$entity->get('reservation_event_plans') as $reservationEventPlan) {
                    if (isset($eventPlans[$reservationEventPlan->get('event_plan_id')])) {
                        $usageDay += $eventPlans[$reservationEventPlan->get('event_plan_id')]->get('usage_day');
                    }
                }
                $entity->set('usage_time', $eventEntity->get('event_unit_time'));
                $entity->set('usage_day', $usageDay);
            }
        }

        // 利用日時
        if ($entity->has('usage_timestamp_from')) {
            $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($entity->get('usage_timestamp_from'));
            if (!isset($usageTimestampFrom)) {
                throw new CakeException();
            }
            $usageTimestampTo = clone $usageTimestampFrom;
            $usageTimestampTo = $usageTimestampTo->addDays($entity->get('usage_day') - 1);
            $usageTimestampTo = $usageTimestampTo->addMinutes((int)$entity->get('usage_time'));
            $entity->set('usage_timestamp_to', $usageTimestampTo);

            $originalUsageTimestampFrom = DateTimeUtility::convertToDateTimeObject(
                $entity->getOriginal('usage_timestamp_from')
            );
            $originalUsageTimestampTo = DateTimeUtility::convertToDateTimeObject(
                $entity->getOriginal('usage_timestamp_to')
            );
            if (!isset($originalUsageTimestampFrom) || !isset($originalUsageTimestampTo)) {
                throw new CakeException();
            }

            if (
                !$entity->isNew()
                && $usageTimestampFrom->format('Y-m-d H:i:s') === $originalUsageTimestampFrom->format('Y-m-d H:i:s')
            ) {
                $entity->setDirty('usage_timestamp_from', false);
            }
            if (
                !$entity->isNew()
                && $usageTimestampTo->format('Y-m-d H:i:s') === $originalUsageTimestampTo->format('Y-m-d H:i:s')
            ) {
                $entity->setDirty('usage_timestamp_to', false);
            }
        }

        // 複数プランの削除
        if ((string)$eventEntity->get('time_plan') === (string)EventEntity::PLAN_SINGLE) {
            $entity->set('plan_values', []);
        }

        // 予約数
        if ($eventEntity->isFixedNumber($isAdmin)) {
            $numberValueOptions = array_keys($eventEntity->getNumberValueOptions());
            $entity->set('number', reset($numberValueOptions));
        }

        // 料金
        if (!$isAdmin || ((string)$entity->get('calculate_charge')) === ((string)Reservation::CALCULATE_CHARGE_ON)) {
            if ($entity->has(['usage_time', 'usage_day', 'number'])) {
                $entity->set('charge', $entity->calculateCharge());
            } else {
                $entity->set('charge', null);
            }
        } else {
            if ((string)$entity->get('charge') === '') {
                $entity->set('charge', 0);
            }
        }

        // ステータス
        if ((string)$entity->get('reservation_status_id') === '') {
            $entity->set('reservation_status_id', $eventEntity->get('reservation_status_id'));
        }

        // 連続予約
        $continuousData = Hash::get($options, 'otherOptions.continuousData');
        $entity->setContinuousData((array)$continuousData);

        // 決済ステータス
        if (
            !$this->commonData()->existsAdminLoginData()
            && !$entity->isNew()
            && isset($entity->payment_method_id)
            && $systemSettingsTable->getData()->usePayment()
        ) {
            $paymentMethodType = $paymentMethodsTable->getPaymentMethodType((int)$entity->payment_method_id);
            if (
                ((string)$paymentMethodType) === ((string)PaymentMethod::TYPE_CARD)
                && (string)$entity->getOriginal('charge') !== (string)$entity->charge
            ) {
                $paymentStatus =
                    $paymentStatusesTable->getDataByDefaultType(PaymentStatus::TYPE_PAYMENT_COMPLETE_CHANGED);
                $entity->set('payment_status_id', $paymentStatus['id']);
            }
        }
    }

    /**
     * Model.beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');
        /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
        $paymentStatusesTable = $this->getTableLocator()->get('PaymentStatuses');
        /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
        $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

        if (!($entity instanceof Reservation)) {
            throw new CakeException();
        }

        // 会員のセット
        $userEntity = $entity->getUserEntity();
        if (!isset($userEntity)) {
            throw new CakeException();
        }
        if ($userEntity->isDirty()) {
            $entity->set('user', $userEntity);
        } else {
            $entity->set('user', null);
            $entity->setDirty('user', false);
        }

        // 決済の有無および決済連携の判定
        $paymentMethodId = Hash::get($options, 'paymentMethodId');
        if ((string)$paymentMethodId !== '') {
            $paymentMethodId = (int)$paymentMethodId;
        } else {
            $paymentMethodId = null;
        }
        $requiresPayments = isset($paymentMethodId) && $this->requiresPayments([$entity]);
        $isRequiredPaymentLinkage = isset($paymentMethodId)
            && $paymentMethodsTable->isRequiredPaymentLinkage($paymentMethodId);
        $isApiPayment = isset($paymentMethodId) && $paymentMethodsTable->isApiPayment($paymentMethodId);
        $isLinkPayment = isset($paymentMethodId) && $paymentMethodsTable->isLinkPayment($paymentMethodId);

        // 自動返信メール履歴の生成
        $autoReplyMailHistory = null;
        if (
            !$isLinkPayment
            && (string)Hash::get($options, 'mailSendFlg') === (string)Configure::readOrFail('Master.common.flg.on')
        ) {
            $mailOptions = Hash::get($options, 'forEdit', false) ? ['forEdit' => true] : [];
            $autoReplyMailHistory =
                $autoReplyMailHistoriesTable->generateDataForReservation($entity, null, $mailOptions);
        }
        if (isset($autoReplyMailHistory)) {
            $mailData = [
                'user' => $entity->getUserEntity(),
                'reservation' => $entity,
            ];
            if (!$entity->isNew()) {
                try {
                    /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                    $mailData['oldReservation'] = $this->get($entity->get('id'), [
                        'finder' => 'edit',
                    ]);
                } catch (RecordNotFoundException $e) {
                    throw new CakeException();
                }
            }
            $autoReplyMailHistory->setDataEntity($mailData);

            $entity->set('auto_reply_mail_histories', [$autoReplyMailHistory]);
        } else {
            $entity->set('auto_reply_mail_histories', null);
        }

        // 決済方法、決済ステータス
        if ($requiresPayments) {
            if ($isRequiredPaymentLinkage) {
                if ($isApiPayment) {
                    $paymentSetting = $paymentSettingsTable->getDataOrFail();
                    $paymentStatusType = $paymentSetting->getInitialPaymentStatus();
                } else {
                    $paymentStatusType = PaymentStatus::TYPE_PAYMENT_YET;
                }
            } else {
                $paymentStatusType = PaymentStatus::TYPE_RECEIVE_YET;
            }
            $paymentStatus = $paymentStatusesTable->getDataByDefaultType($paymentStatusType);

            $entity->set('payment_method_id', $paymentMethodId);
            $entity->set('payment_status_id', $paymentStatus['id']);
        }

        // 決済情報の生成
        if ($requiresPayments && $isRequiredPaymentLinkage) {
            $reservationPayment = $reservationPaymentsTable->generateData($entity);
            if (isset($options['paymentToken']) && $isApiPayment) {
                $reservationPayment->set('payment_token', $options['paymentToken']);

                if ($paymentSettingsTable->getDataOrFail()->requiresKycGmo() && isset($options['kycValues'])) {
                    $reservationPayment->set('kyc_values', $options['kycValues']);
                }
            }
            $entity->set('reservation_payments', [$reservationPayment]);
        } else {
            $entity->set('reservation_payments', null);
        }

        // キャンセル日時
        if (
            $entity->isDirty('reservation_status_id')
            && (string)$entity->getStatus() === ((string)ReservationStatus::STATUS_TYPE_CANCEL)
        ) {
            $entity->set('cancel_timestamp', $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'));
        }

        // 操作ログ
        $saveOperation = Hash::get($options, 'saveOperation');
        if (!empty($saveOperation)) {
            $this->getBehavior('AdminOperationLog')->setConfig('saveOperation', true);
        }
    }

    /**
     * Model.beforeSaveManyイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param array $entities エンティティ
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function beforeSaveMany(EventInterface $event, array $entities, ArrayObject $options)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $this->getBehavior('AdminOperationLog')->setConfig([
            'afterSave' => false,
        ]);

        $result = true;
        if (Hash::get($options, 'checkRules', true)) {
            foreach ($entities as $entity) {
                if (!($entity instanceof Reservation)) {
                    throw new CakeException();
                }

                $userEntity = $entity->get('user');
                if (isset($userEntity)) {
                    $mode = RulesChecker::CREATE;
                    if (!$userEntity->isNew()) {
                        $mode = RulesChecker::UPDATE;
                    }
                    if (!$usersTable->checkRules($userEntity, $mode, $options)) {
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Model.afterSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        /** @var \App\Model\Table\WaitingCancellationsTable $waitingCancellationsTable */
        $waitingCancellationsTable = $this->getTableLocator()->get('WaitingCancellations');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        if (!($entity instanceof Reservation)) {
            throw new CakeException();
        }

        $paymentSetting = $paymentSettingsTable->getData();

        $oldEntity = Hash::get($options, 'oldEntity', false);

        $reservationPayment =
            (string)$entity->getStatus() === ((string)ReservationStatus::STATUS_TYPE_CANCEL) &&
            (string)$oldEntity->getStatus() !== ((string)ReservationStatus::STATUS_TYPE_CANCEL) &&
            $entity->hasReservationPayment() ? $entity->getReservationPayment() : null;

        // SBペイメント利用の予約キャンセル
        if (
            isset($paymentSetting) && $paymentSetting->isPaymentServiceSb() &&
            isset($reservationPayment) && $reservationPayment->isReservationPaymentServiceSb() &&
            (string)$reservationPayment->get('status') === ((string)$reservationPayment::STATUS_COMPLETED)
        ) {
            $this->cancelApiPayment($entity, $reservationPayment, $paymentSetting);
        }

        $waitingCancellationsTable->updateNotifyFlg($entity);
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
        $this->getBehavior('AdminOperationLog')->setConfig('saveOperation', true);
    }

    /**
     * Model.afterDeleteイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        /** @var \App\Model\Table\WaitingCancellationsTable $waitingCancellationsTable */
        $waitingCancellationsTable = $this->getTableLocator()->get('WaitingCancellations');

        if (!($entity instanceof Reservation)) {
            throw new CakeException();
        }

        $usersTable->deleteNoReservationGuest($entity->get('user_id'));

        $waitingCancellation = Hash::get($options, 'waitingCancellation');
        if (((string)$waitingCancellation) === (string)Configure::readOrFail('Master.common.flg.on')) {
            $entity->cancel();
            $waitingCancellationsTable->updateNotifyFlg($entity);
        }
    }

    /**
     * Model.beforeDeleteDataイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Database\Query $query クエリ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeDeleteData(EventInterface $event, DatabaseQuery $query, ArrayObject $options)
    {
        /** @var \App\Model\Table\WaitingCancellationsTable $waitingCancellationsTable */
        $waitingCancellationsTable = $this->getTableLocator()->get('WaitingCancellations');

        $waitingCancellation = Hash::get($options, 'waitingCancellation');
        if (((string)$waitingCancellation) === (string)Configure::readOrFail('Master.common.flg.on')) {
            foreach ($query as $data) {
                $reservation = $this->get($data['id'], [
                    'finder' => 'edit',
                ]);
                $reservation->cancel();
                $waitingCancellationsTable->updateNotifyFlg($reservation);
            }
        }
    }

    /**
     * Model.afterDeleteDataイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Database\Query $query クエリ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterDeleteData(EventInterface $event, DatabaseQuery $query, ArrayObject $options)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $usersTable->deleteNoReservationGuest();
    }

    /**
     * @inheritDoc
     */
    public function save(EntityInterface $entity, $options = [])
    {
        $options['forReservation'] = true;

        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->getTableLocator()->get('ReservationVideoMeetings');

        if (!($entity instanceof Reservation)) {
            throw new CakeException();
        }

        /** @var \App\Utility\Payment\PaymentInterface|null $payment */
        $payment = null;
        $result = false;

        // 予約操作用にロック
        $lockCodes = null;
        if (!Hash::get($options, 'forSaveMany', false)) {
            $lockCodes = $this->getLockForReservation([$entity], Hash::get($options, 'checkRules', true));
        }
        $releaseLockForReservation = function () use (&$lockCodes) {
            if (isset($lockCodes)) {
                $this->releaseLockForReservation($lockCodes);
                $lockCodes = null;
            }
        };

        $oldEntity = null;
        $updateTrackingIdFlg = false;
        if (!$entity->isNew()) {
            // 管理側予約編集で決済トラッキングIDが変更された場合、決済情報の更新を行う
            $forEdit = Hash::get($options, 'forEdit', false);
            if ($entity->hasReservationPayment() && $this->commonData()->existsAdminLoginData() && $forEdit) {
                $reservationPayment = $entity->getReservationPayment();
                $updateTrackingIdFlg =
                    $entity->get('payment_tracking_id') !== $reservationPayment->get('payment_tracking_id');
            }

            try {
                /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                $oldEntity = $this->get($entity->get('id'));

                $options['oldEntity'] = $oldEntity;
            } catch (RecordNotFoundException $e) {
                throw new CakeException();
            }
        }

        $oldAdditions = $entity->getOriginal('addition_values');

        try {
            $result = $this->getConnection()->transactional(function () use (
                $entity,
                $options,
                $oldEntity,
                &$payment,
                $updateTrackingIdFlg
            ) {
                /** @var \App\Model\Table\EventsTable $eventsTable */
                $eventsTable = $this->getTableLocator()->get('Events');
                /** @var \App\Model\Table\OptionsTable $optionsTable */
                $optionsTable = $this->getTableLocator()->get('Options');
                /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
                $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');
                /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
                $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');
                /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
                $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');
                /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
                $reservationVideoMeetingsTable = $this->getTableLocator()->get('ReservationVideoMeetings');
                /** @var \App\Model\Table\ReservationSmartLocksTable $reservationSmartLocksTable */
                $reservationSmartLocksTable = $this->getTableLocator()->get('ReservationSmartLocks');
                /** @var \App\Model\Table\UsersTable $usersTable */
                $usersTable = $this->getTableLocator()->get('Users');

                // 排他制御
                $reservationLock = Hash::get($options, 'reservationLock', true);
                if ($reservationLock) {
                    // 予約枠排他制御
                    $eventsTable->find('reservationLock', [
                        'reservations' => [$entity],
                    ])->all();

                    // オプション排他制御
                    $reservationOptions = $entity->get('reservation_options');
                    if (!empty($reservationOptions)) {
                        $optionsTable->find('reservationLock', [
                            'reservationOptions' => $reservationOptions,
                        ])->all();
                    }
                }

                if (isset($options['paymentToken']) && !$this->commonData()->existsAdminLoginData()) {
                    // 決済エラー判定
                    $this->checkPaymentLock();
                }

                $originalUser = null;
                if ($entity->has('user_id')) {
                    /** @var \Cake\Datasource\EntityInterface|null $originalUser */
                    $originalUser = $usersTable->find('SmartLock')
                        ->where(['Users.id' => $entity->get('user_id')])
                        ->first();
                }

                $result = parent::save($entity, $options);

                if ($result && empty($oldEntity)) {
                    // QRコード文字列を生成
                    $qrCodeData = QrCodeUtility::createQrCodeData($entity->get('id'), $entity->get('created'));
                    $this->updateQrCode($entity->get('id'), $qrCodeData);
                    $entity->set('qr_code', $qrCodeData);

                    // 自動返信メール履歴の情報にQRコードをセット
                    $autoReplyMailHistories = $entity->get('auto_reply_mail_histories');
                    if (!empty($autoReplyMailHistories)) {
                        foreach ($autoReplyMailHistories as $autoReplyMailHistory) {
                            $mailData = $autoReplyMailHistory->get('data');
                            if (isset($mailData['reservation'])) {
                                $mailData['reservation']['qr_code'] = $qrCodeData;
                            }
                            $autoReplyMailHistory->set('data', $mailData);
                        }
                    }
                }

                if ($result) {
                    // ビデオ会議連携を行う
                    $reservationVideoMeetingsTable->videoMeetingProcess($entity, $oldEntity);
                }

                // 決済処理
                if ($entity->has('reservation_payments')) {
                    $reservationPayment = $entity->get('reservation_payments');
                    $reservationPayment = reset($reservationPayment);

                    if ($paymentMethodsTable->isApiPayment((int)$reservationPayment->get('payment_method_id'))) {
                        // 決済実行
                        $payment = clone $paymentSettingsTable->getDataOrFail()->getPaymentModule();
                        if (!$reservationPayment->executeApiPayment($entity, $payment)) {
                            $entity->setError('payment_error', (string)__(Message::ERROR_PAYMENT));

                            return false;
                        }

                        // 決済情報更新
                        $reservationPaymentsTable->saveOrFail($reservationPayment);

                        // 決済エラーリセット
                        $this->resetPaymentError();

                        // 決済後の3Dセキュア判定
                        if ($this->isThreeDSecure) {
                            $this->isThreeDSecure = $reservationPayment->isThreeDSecure();

                            // 決済成功時は決済ステータス更新
                            if (
                                (string)$reservationPayment->get('status')
                                    === (string)ReservationPayment::STATUS_COMPLETED
                            ) {
                                $this->updateAll([
                                    'payment_status_id'
                                        => $paymentSettingsTable->getDataOrFail()->getPaymentSuccessStatus(),
                                    'modified' => $this->commonData()->getNowDateTime(),
                                ], ['id' => $entity->get('id')]);
                            }
                        }
                    }
                }

                if ($updateTrackingIdFlg) {
                    $updateReservationPayment = $entity->getReservationPayment();
                    $reservationPaymentStatus = $reservationPaymentsTable->getReservationPaymentStatus(
                        $updateReservationPayment->get('status'),
                        $updateReservationPayment->get('payment_limit')
                    );

                    $updateReservationPayment->set('payment_tracking_id', $entity->get('payment_tracking_id'));
                    if ($reservationPaymentStatus === ReservationPayment::DISPLAY_STATUS_EXPIRED) {
                        $updateReservationPayment->set('status', ReservationPayment::DISPLAY_STATUS_COMPLETED);
                        $updateReservationPayment->set(
                            'payment_process_date',
                            $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s')
                        );
                    }

                    $reservationPaymentsTable->saveOrFail($updateReservationPayment);
                }

                // スマートロック連携（決済連携で外部サイトへ遷移しない場合）
                if (
                    $result
                    && !(Hash::get($options, 'isLinkPayment', false) || $this->isThreeDSecure)
                ) {
                    $this->smartLockLinkage($entity, $oldEntity, $originalUser);
                    // 自動返信メール履歴の情報に予約スマートロック情報をセット
                    $autoReplyMailHistories = $entity->get('auto_reply_mail_histories');
                    $reservationSmartLock = $reservationSmartLocksTable->find('reservation', [
                            'inputs' => [
                                'reservation_id' => $entity->get('id'),
                            ],
                        ])
                        ->first();
                    if (!empty($autoReplyMailHistories) && isset($reservationSmartLock)) {
                        foreach ($autoReplyMailHistories as $autoReplyMailHistory) {
                            $mailData = $autoReplyMailHistory->get('data');
                            if (isset($mailData['reservation'])) {
                                $mailData['reservation']['reservation_smart_lock'] = $reservationSmartLock;
                            }
                            $autoReplyMailHistory->set('data', $mailData);
                        }
                    }
                }

                return $result;
            });
        } catch (Throwable $e) {
            // 決済の取消
            if (isset($payment) && !($e instanceof PaymentRollbackException)) {
                try {
                    $payment->cancelLastPayment();
                } catch (Throwable $e2) {
                    call_user_func($releaseLockForReservation);
                    throw new PaymentRollbackException($e2->__toString() . "\n" . $e->__toString());
                }
            }
            call_user_func($releaseLockForReservation);

            // ビデオ会議連携の取消
            $reservationVideoMeetingsTable->videoMeetingProcessRollBack($e);

            throw $e;
        }
        call_user_func($releaseLockForReservation);

        if (!empty($entity->getError('payment_error'))) {
            // 決済エラー加算
            $this->addPaymentError();
        }

        // 予約編集で置き換わったファイルの削除
        if (isset($oldEntity) && $result) {
            $this->deleteFileFormUpload($entity, $oldAdditions);
        }

        // メール送信
        if ($result) {
            if (!Hash::get($options, 'notSendMail', false) && !$this->isThreeDSecure) {
                $this->sendReservationMail($entity);
            } elseif ((Hash::get($options, 'isLinkPayment', false) || $this->isThreeDSecure) && $entity->has('user')) {
                /** @var \App\Model\Table\UsersTable $usersTable */
                $usersTable = $this->getTableLocator()->get('Users');

                $usersTable->sendUserMail($entity->get('user'));
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function saveMany($entities, $options = [])
    {
        $options['forSaveMany'] = true;

        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->getTableLocator()->get('ReservationVideoMeetings');

        // 決済方法
        $paymentMethodId = Hash::get($options, 'paymentMethodId');
        if ((string)$paymentMethodId !== '') {
            $paymentMethodId = (int)$paymentMethodId;
        } else {
            $paymentMethodId = null;
        }

        // 予約操作用にロック
        $lockCodes = $this->getLockForReservation((array)$entities);
        $releaseLockForReservation = function () use (&$lockCodes) {
            if (isset($lockCodes)) {
                $this->releaseLockForReservation($lockCodes);
                $lockCodes = null;
            }
        };

        // ビデオ会議連携をする予約があるかをチェック
        $requiresVideoMeeting = false;
        foreach ($entities as $reservation) {
            if (!($reservation instanceof Reservation)) {
                throw new CakeException();
            }

            if ($reservationVideoMeetingsTable->shouldProcessOnReserve($reservation)) {
                $requiresVideoMeeting = true;
                break;
            }
        }

        $isLinkPayment = isset($paymentMethodId) && $paymentMethodsTable->isLinkPayment($paymentMethodId);
        $isApiPayment = isset($paymentMethodId) && $paymentMethodsTable->isApiPayment($paymentMethodId);
        $paymentTokens = Hash::get($options, 'paymentTokens', []);
        $smartLockLinkage = new SmartLockLinkage();

        $this->isThreeDSecure = $isApiPayment && $paymentSettingsTable->getDataOrFail()->is3DSecureFlgOn();

        // リンク型決済は決済完了時にメール送信(会員登録メールは送信)
        if ($isLinkPayment) {
            $options['notSendMail'] = true;
            $options['isLinkPayment'] = true;
        }

        $result = false;
        try {
            if ($isApiPayment || $requiresVideoMeeting || $smartLockLinkage->useSmartLock()) {
                // API連携用にタイムアウトを延長
                set_time_limit(90);

                $options = new ArrayObject($options);
                $beforeSaveManyEvent = $this->dispatchEvent('Model.beforeSaveMany', [
                    'entities' => $entities,
                    'options' => $options,
                ]);
                if ($beforeSaveManyEvent->isStopped()) {
                    return $beforeSaveManyEvent->getResult();
                }

                // API連携する場合は1予約で1トランザクション
                foreach ($entities as $entity) {
                    $options->offsetSet('paymentToken', null);
                    if ($isApiPayment && $this->requiresPayments([$entity])) {
                        $options->offsetSet('paymentToken', array_pop($paymentTokens));
                    }

                    if ($this->save($entity, $options)) {
                        $result = true;
                    } else {
                        $entity->setError('continous_error', (string)__(Message::ERROR_RESERVATION_CONTINUOUS_PARTIAL));
                    }
                }

                $afterSaveManyEvent = $this->dispatchEvent('Model.afterSaveMany', [
                    'entities' => $entities,
                    'options' => $options,
                ]);
                if ($afterSaveManyEvent->isStopped()) {
                    return $afterSaveManyEvent->getResult();
                }
            } else {
                // API連携しない場合は全体で1トランザクション
                $result = $this->getConnection()->transactional(function () use ($entities, $options) {
                    /** @var \App\Model\Table\EventsTable $eventsTable */
                    $eventsTable = $this->getTableLocator()->get('Events');
                    /** @var \App\Model\Table\OptionsTable $optionsTable */
                    $optionsTable = $this->getTableLocator()->get('Options');

                    // 予約枠排他制御
                    $eventsTable->find('reservationLock', [
                        'reservations' => $entities,
                    ])->all();

                    // オプション排他制御
                    $reservationOptions = [];
                    foreach ($entities as $reservation) {
                        $reservationOptions = array_merge(
                            $reservationOptions,
                            (array)$reservation->get('reservation_options')
                        );
                    }
                    if (!empty($reservationOptions)) {
                        $optionsTable->find('reservationLock', [
                            'reservationOptions' => $reservationOptions,
                        ])->all();
                    }

                    $options['reservationLock'] = false;
                    $options['notSendMail'] = true;
                    $result = parent::saveMany($entities, $options);

                    return $result;
                });

                // 予約操作用のロックを解放
                call_user_func($releaseLockForReservation);

                // メール送信
                if ($result) {
                    if ($isLinkPayment) {
                        /** @var \App\Model\Table\UsersTable $usersTable */
                        $usersTable = $this->getTableLocator()->get('Users');

                        foreach ($entities as $reservation) {
                            if ($reservation->has('user')) {
                                $usersTable->sendUserMail($reservation->get('user'));
                            }
                        }
                    } else {
                        foreach ($entities as $reservation) {
                            $this->sendReservationMail($reservation);
                        }
                    }
                }
            }
        } finally {
            call_user_func($releaseLockForReservation);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function delete(EntityInterface $entity, $options = []): bool
    {
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->getTableLocator()->get('ReservationVideoMeetings');

        if (!($entity instanceof Reservation)) {
            throw new CakeException();
        }

        $lockId = $reservationVideoMeetingsTable->getLockIdForZoom($entity);
        $lockCode = null;
        if (isset($lockId)) {
            $lockCode = $this->generateLockCode((string)$lockId);
            $this->getLock(static::LOCK_TYPE_ZOOM_CONNECT_USER, $lockCode);
        }

        try {
            $result = $this->getConnection()->transactional(function () use ($entity, $options) {
                /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
                $reservationVideoMeetingsTable = $this->getTableLocator()->get('ReservationVideoMeetings');

                /** @var \App\Model\Entity\Reservation $entity */
                $entity->setSmartLockInfo();
                $result = parent::delete($entity, $options);

                if ($result) {
                    // ビデオ会議連携を行う
                    $reservationVideoMeetingsTable->videoMeetingProcess($entity, null, true);
                }

                // スマートロック連携
                $smartLock = new SmartLockLinkage();
                /** @var \App\Model\Entity\Reservation $entity */
                if ($result && $smartLock->useSmartLock()) {
                    // スマートロック連携の場合スケジュール削除
                    $smartLock->deleteSchedule($entity);
                }

                return $result;
            });
        } catch (Throwable $e) {
            // ビデオ会議連携の取消
            $reservationVideoMeetingsTable->videoMeetingProcessRollBack($e);

            throw $e;
        } finally {
            if (isset($lockCode)) {
                $this->releaseLock(static::LOCK_TYPE_ZOOM_CONNECT_USER, $lockCode);
            }
        }

        return $result;
    }

    /**
     * 予約をキャンセル
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param mixed $mailSendFlg メール送信フラグ
     * @param array|null $saveOperation 操作ログ
     * @return void
     */
    public function cancelReservation(
        Reservation $reservation,
        $mailSendFlg = null,
        ?array $saveOperation = null
    ) {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $reservationStatus = $reservationStatusesTable->getCancelData();

        $reservation->clean();

        $reservation->set('reservation_status_id', $reservationStatus['id']);
        $this->saveOrFail($reservation, [
            'mailSendFlg' => $mailSendFlg,
            'saveOperation' => $saveOperation,
            'checkRules' => false,
        ]);
    }

    /**
     * ステータスを更新
     *
     * @param \Cake\Datasource\EntityInterface $reservation 予約
     * @param int $reservationStatusId ステータス
     * @param mixed $mailSendFlg メール送信フラグ
     * @param array|null $saveOperation 操作ログ
     * @return bool
     */
    public function updateStatus(
        EntityInterface $reservation,
        int $reservationStatusId,
        $mailSendFlg = null,
        ?array $saveOperation = null
    ) {
        $reservation->clean();
        $reservation->set('reservation_status_id', $reservationStatusId);
        if (
            !$this->save($reservation, [
            'mailSendFlg' => $mailSendFlg,
            'saveOperation' => $saveOperation,
            ])
        ) {
            return false;
        }

        return true;
    }

    /**
     * 予約のメールを送信
     *
     * @param \Cake\Datasource\EntityInterface $reservation 予約
     * @return void
     */
    protected function sendReservationMail($reservation)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        if ($reservation->has('user')) {
            $usersTable->sendUserMail($reservation->get('user'));
        }
        $this->executeSafe(function () use ($reservation) {
            if (!$reservation->has('auto_reply_mail_histories')) {
                return;
            }

            /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
            $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');

            foreach ($reservation->get('auto_reply_mail_histories') as $autoReplyMailHistory) {
                $autoReplyMailHistory->set('user_id', $reservation->get('user_id'));
                $autoReplyMailHistoriesTable->sendAutoReplyMail($autoReplyMailHistory, true, [], null, true);
            }
        });
    }

    /**
     * チェックした予約情報を取得
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCheckReservations(Query $query, array $options)
    {
        $checked = Hash::get($options, 'checked', []);
        $condition = Hash::get($options, 'condition', []);

        if (isset($checked['allCheck'])) {
            $query = $query->find('searchList', [
                'inputs' => $condition,
                'defaultLabelId' => $this->commonData()->getAdminLoginLabel(),
            ]);
        } else {
            $query = $query->find('searchList', [
                'checked' => $checked,
            ]);
        }

        return $query;
    }

    /**
     * ビデオ会議連携存在チェック用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findHasVideoMeeting(Query $query, array $options)
    {
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->getAssociation('ReservationVideoMeetings')->getTarget();

        $query = $this->callFinder('checkReservations', $query, $options);

        // 開始日時を過ぎていない
        $query->where([
            'Reservations.usage_timestamp_from >' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
        ]);

        // ビデオ会議連携が存在
        $idQuery = $reservationVideoMeetingsTable->find();
        $idQuery->select(['ReservationVideoMeetings.reservation_id']);
        $query->where([
            'Reservations.id IN' => $idQuery,
        ]);

        return $query;
    }

    /**
     * 複数予約を削除
     *
     * @param array $checked チェック情報
     * @param array $condition 検索条件
     * @param array|null $saveOperation 操作ログ
     * @param mixed $waitingCancellation キャンセル待ち通知
     * @param bool $processVideoMeeting ビデオ会議連携
     * @return bool
     */
    public function deleteReservations(
        array $checked,
        array $condition,
        $saveOperation = null,
        $waitingCancellation = null,
        bool $processVideoMeeting = false
    ) {
        $this->getBehavior('AdminOperationLog')->setConfig('saveOperation', true);

        $query = $this->find('checkReservations', [
            'checked' => $checked,
            'condition' => $condition,
        ]);

        $options = [
            'saveOperation' => $saveOperation,
            'waitingCancellation' => $waitingCancellation,
        ];

        $smartLockLinkage = new SmartLockLinkage();
        if ($processVideoMeeting || $smartLockLinkage->useSmartLock()) {
            // API連携用にタイムアウトを延長
            set_time_limit(90);

            // ビデオ会議連携、もしくはスマートロック連携の場合は1件ずつ削除
            foreach ($query as $reservation) {
                $this->deleteOrFail($reservation, $options);
            }
            $result = true;
        } else {
            $result = $this->deleteData($query, $options);
        }

        return $result;
    }

    /**
     * 予約フォームの項目一覧を取得
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @param int|null $userAuthorityId 会員権限ID
     * @param array $options オプション引数
     * @return array 入力項目一覧
     */
    public function getReservationFormGroups(EventEntity $event, ?int $userAuthorityId = null, array $options = [])
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
        /** @var \App\Model\Table\FormGroupsTable $formGroupsTable */
        $formGroupsTable = $this->getTableLocator()->get('FormGroups');
        /** @var \App\Model\Table\FormPatternDisplayTypesTable $formPatternDisplayTypesTable */
        $formPatternDisplayTypesTable = $this->getTableLocator()->get('FormPatternDisplayTypes');
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $reservationId = Hash::get($options, 'reservationId');
        $reservationType = Hash::get($options, 'reservationType');
        $userId = Hash::get($options, 'userId');
        $excludeUserData = Hash::get($options, 'excludeUserData', false);
        $isConfirm = Hash::get($options, 'isConfirm', false);
        $isApp = Hash::get($options, 'isApp', false);
        $reservation = Hash::get($options, 'reservation');

        // フォームパターン表示タイプ取得
        $formPatternDisplayTypes = $formPatternDisplayTypesTable->find('formCreating', [
            'inputs' => [
                'event_id' => $event->get('id'),
            ],
        ])->toArray();

        $reservationFormGroups = [];
        foreach (
            $formGroupsTable->getFormGroups(
                FormGroup::FORM_TYPE_RESERVATION,
                ['excludeUserData' => $excludeUserData, 'cloneItem' => true]
            ) as $formGroup
        ) {
            $formGroup = clone $formGroup;

            $formItems = $formGroup->get('form_items');
            if (!is_array($formItems)) {
                $formItems = [];
            }
            foreach ($formItems as $formItemIndex => $formItem) {
                // フォームパターン表示タイプ
                $formPatternDisplayType = Hash::get($formPatternDisplayTypes, $formItem->get('id'));
                if (!isset($formPatternDisplayType)) {
                    $formPatternDisplayType = $formPatternDisplayTypesTable->createDefaultData($formItem->get('id'));
                }

                // 表示項目の設定
                $formItem->getInputTypeItem()->setConfig([
                        'event' => $event,
                        'userAuthorityId' => $userAuthorityId,
                    ] + $options);
                $formItem->getInputTypeItem()->settingDisplayType($formPatternDisplayType);
                if ((int)$formItem->input_type === FormItem::INPUT_TYPE_FILE) {
                    $formItem->getInputTypeItem()->setReservationEntity($reservation);
                }
                if (!$formItem->getInputTypeItem()->canDisplay()) {
                    unset($formItems[$formItemIndex]);
                }
            }

            if (!empty($formItems)) {
                $formGroup->set('form_items', $formItems);
                $formGroup->clean();

                $reservationFormGroups[] = $formGroup;
            }
        }

        // 会員フォーム取得
        $userFormGroups = [];
        if (!is_null($userAuthorityId)) {
            $userFormGroups = $usersTable->getUserFormGroups($userAuthorityId, [
                'userId' => $userId,
                'reservationId' => $reservationId,
                'reservationType' => $reservationType,
                'excludeUserData' => $excludeUserData,
                'isConfirm' => $isConfirm,
                'isApp' => $isApp,
            ]);
        }

        $result = [
            FormGroup::FORM_TYPE_USER => $userFormGroups,
            FormGroup::FORM_TYPE_RESERVATION => $reservationFormGroups,
        ];

        // 基本設定の順番でソート
        $siteSetting = $siteSettingsTable->getData();
        uksort($result, function ($key1, $key2) use ($siteSetting) {
            if (((string)$key1) === (string)$siteSetting->get('reservation_form_type_first')) {
                return -1;
            }
            if (((string)$key2) === (string)$siteSetting->get('reservation_form_type_first')) {
                return 1;
            }

            return 0;
        });

        return $result;
    }

    /**
     * 決済の要否を判定
     *
     * @param array $reservations 予約
     * @return bool
     */
    public function requiresPayments(array $reservations)
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if (!$systemSettingsTable->getData()->usePayment()) {
            return false;
        }
        foreach ($reservations as $reservation) {
            if ($reservation->get('charge') > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * 会員の予約エラーをチェック
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return string|null
     */
    public function checkUserError(Reservation $reservation)
    {
        $reservation = clone $reservation;
        $event = $reservation->getEventEntity();
        if (!($event instanceof EventEntity)) {
            throw new CakeException();
        }

        $reservation->set('usage_timestamp_to', $event->getUsageTimestampTo($reservation->get('usage_timestamp_from')));

        $mode = RulesChecker::CREATE;
        if (!$reservation->isNew()) {
            $mode = RulesChecker::UPDATE;
        }
        $this->checkRules($reservation, $mode, [
            'checkStock' => false,
        ]);

        $errors = Hash::flatten($reservation->getErrors());
        if (!empty($errors)) {
            return reset($errors);
        }

        return null;
    }

    /**
     * 重複予約のチェック
     *
     * @param int $userId 会員ID
     * @param \App\Model\Entity\Event $event 予約枠
     * @param string|\DateTimeInterface $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface $dateTimeTo 終了日時
     * @param int|null $excludeId 除外する予約ID
     * @param array|null $reservations 予約中のデータ
     * @return bool
     */
    public function existsDuplication(
        int $userId,
        EventEntity $event,
        $dateTimeFrom,
        $dateTimeTo,
        ?int $excludeId = null,
        ?array $reservations = null
    ) {
        if ((string)$event->get('duplication_check_flg') !== ((string)EventEntity::COMMON_FLG_ON)) {
            return false;
        }

        foreach ((array)$reservations as $reservation) {
            $targetEvent = $reservation->getEventEntity();
            if ((string)$targetEvent->get('duplication_check_flg') === ((string)EventEntity::COMMON_FLG_ON)) {
                if (
                    DateTimeUtility::isOverlapDateTime(
                        $dateTimeFrom,
                        $dateTimeTo,
                        $reservation->get('usage_timestamp_from'),
                        $reservation->get('usage_timestamp_to')
                    )
                ) {
                    return true;
                }
            }
        }

        $query = $this->find('duplicationCheck', [
            'inputs' => [
                'user_id' => $userId,
                'usage_timestamp_from' => $dateTimeFrom,
                'usage_timestamp_to' => $dateTimeTo,
                'exclude_id' => $excludeId,
            ],
        ]);

        if ($query->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * 回数制限のチェック
     *
     * @param int $userId 会員ID
     * @param string|\DateTimeInterface $targetDateTime 対象日時
     * @param int $limit 制限値
     * @param string $periodType 期間種別
     * @param int|null $eventId 予約枠ID
     * @param int|null $excludeId 除外する予約ID
     * @param array|null $reservations 予約中のデータ
     * @return bool
     */
    public function exceedsReservationLimit(
        int $userId,
        $targetDateTime,
        int $limit,
        string $periodType,
        ?int $eventId = null,
        ?int $excludeId = null,
        ?array $reservations = null
    ) {
        // 期間のチェック処理
        $checkPeriod = [
            'all' => function ($usageTimestampFrom, $targetDateTime) {
                return true;
            },
            'future' => function ($usageTimestampFrom, $targetDateTime) {
                if ($usageTimestampFrom <= $this->commonData()->getNowDateTime()) {
                    return false;
                }

                return true;
            },
            'month' => function ($usageTimestampFrom, $targetDateTime) {
                if ($usageTimestampFrom->format('Y-m') !== $targetDateTime->format('Y-m')) {
                    return false;
                }

                return true;
            },
            'day' => function ($usageTimestampFrom, $targetDateTime) {
                if ($usageTimestampFrom->format('Y-m-d') !== $targetDateTime->format('Y-m-d')) {
                    return false;
                }

                return true;
            },
        ];

        $targetDateTime = DateTimeUtility::convertToDateTimeObject($targetDateTime);

        $count = 0;
        foreach ((array)$reservations as $reservation) {
            if (!isset($eventId) || ((string)$eventId) === (string)$reservation->get('event_id')) {
                $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject(
                    $reservation->get('usage_timestamp_from')
                );
                if (call_user_func($checkPeriod[$periodType], $usageTimestampFrom, $targetDateTime)) {
                    $count += 1;
                }
            }
        }

        $query = $this->find('reservationLimit', [
            'inputs' => [
                'period_type' => $periodType,
                'user_id' => $userId,
                'event_id' => $eventId,
                'usage_timestamp_from' => $targetDateTime,
                'exclude_id' => $excludeId,
            ],
        ]);
        $count += $query->count();

        if ($count > $limit) {
            return true;
        }

        return false;
    }

    /**
     * 未来の予約を取得
     *
     * @param mixed $eventId 予約枠ID
     * @return mixed
     */
    public function getReserveFutureAndAll($eventId)
    {
        $reserve['future'] = false;
        $reserve['all'] = false;

        $query = $this->find('all');

        $futureCase = $query->newExpr()->case();
        $futureCase
            ->when([
                'Reservations.usage_timestamp_to >=' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
            ])
            ->then(1);
        $pastCase = $query->newExpr()->case();
        $pastCase
            ->when([
                'Reservations.usage_timestamp_to <' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
            ])
            ->then(1);

        $query->select([
            'event_id',
            'event_plan_id' => 'ReservationEventPlans.event_plan_id',
            'future' => $query->func()->count($futureCase),
            'past' => $query->func()->count($pastCase),
        ])->where(['Reservations.event_id IN' => $eventId])
            ->leftJoinWith('ReservationEventPlans')
            ->group(['Reservations.event_id', 'ReservationEventPlans.event_plan_id'])
            ->enableHydration(false);

        $future = false;
        $all = false;
        foreach ($query as $v) {
            $future = is_array($v) && $v['future'] >= 1;
            $all = is_array($v) && ((int)($v['future'] + $v['past']) >= 1);

            $reserve[$v['event_id']]['future'] = is_array($v) && $v['future'] >= 1;
            $reserve[$v['event_id']]['all'] = is_array($v) && ((int)($v['future'] + $v['past']) >= 1);

            if ($v['event_plan_id'] !== null) {
                $reserve[$v['event_id']]['plan'][$v['event_plan_id']] = [
                    'future' => $reserve[$v['event_id']]['future'],
                    'all' => $reserve[$v['event_id']]['all'],
                ];
            } else {
                $reserve[$v['event_id']]['plan'] = [];
            }
        }

        $reserve['future'] = $future;
        $reserve['all'] = $all;

        return $reserve;
    }

    /**
     * 予約最小と最大のデータ整形
     *
     * @param array|\Cake\Datasource\EntityInterface $minMaxReserve 予約の最小最大
     * @param string $dateFrom 利用開始日
     * @param string $dateTo 利用終了日
     * @return mixed
     */
    public function filterMinMaxReserve($minMaxReserve, $dateFrom, $dateTo)
    {
        if ($minMaxReserve instanceof \Cake\Datasource\EntityInterface) {
            $minMaxReserve = $minMaxReserve->toArray();
        }

        $min = $minMaxReserve['min']->i18nFormat('yyyy-MM-dd');
        if (!Validation::notBlank($dateFrom) || $dateFrom <= $min) {
            $dateFrom = $min;
        }

        $max = $minMaxReserve['max']->i18nFormat('yyyy-MM-dd');
        if (!Validation::notBlank($dateTo) || $dateTo >= $max) {
            $dateTo = $max;
        }

        $filterData['from'] = $dateFrom;
        $filterData['to'] = $dateTo;

        return $filterData;
    }

    /**
     * 祝日に予約が存在するか
     *
     * @param array $holidays 検索日
     * @param array $eventIds 枠ID
     * @param bool $overDay 実施時間が日を跨いでいるかどうか
     * @return \Cake\ORM\Query
     */
    public function isReserveInHolidays(array $holidays, array $eventIds, bool $overDay = false)
    {
        $options['checkDate'] = $holidays;
        $options['overDay'] = $overDay;
        $options['event_id'] = $eventIds;
        $options['time_from'] = $eventIds;
        $options['time_to'] = $eventIds;

        return $this->find('isReserveIn', $options);
    }

    /**
     * 曜日設定内に予約が存在するか
     *
     * @param array $options オプション
     * @return bool
     */
    public function isReserveInWeeks(array $options)
    {
        $minMaxReserve = $this->find('minMaxDateTime', [
            'allStatus' => true,
            'inputs' => ['event_id' => Hash::get($options, 'event_id')],
        ])->first();
        if (is_null($minMaxReserve)) {
            return true;
        }

        $dateFrom = Hash::get($options, 'date_from');
        $dateTo = Hash::get($options, 'date_to');
        $dateFromTo = $this->filterMinMaxReserve($minMaxReserve, $dateFrom, $dateTo);

        $fromObj = new FrozenDate($dateFromTo['from']);
        $toObj = new FrozenDate($dateFromTo['to']);

        // 期間内の祝日情報を取得
        $holidayList = [];
        if (isset($options['weeks']) && ArrayUtility::arraySearch(EventEntity::WEEK_HOLIDAY, $options['weeks'])) {
            $holidaysTable = $this->getTableLocator()->get('Holidays');
            $holidayList = $holidaysTable->selectQuery()->select('date')
                ->enableHydration(false)
                ->formatResults(function (CollectionInterface $results) {
                    $data = [];
                    foreach ($results->toArray() as $row) {
                        $data[$row['date']->format('Y/m/d')] = $row['date']->format('Y/m/d');
                    }

                    return $data;
                });
            if ($holidayList instanceof Query) {
                $holidayList = $holidayList->toArray();
            }
        }

        $tempDates = [];
        while ($fromObj->lessThanOrEquals($toObj)) {
            if (
                (ArrayUtility::arraySearch($fromObj->dayOfWeek, $options['weeks']) !== false)
                || Hash::check($holidayList, $fromObj->toDateString())
            ) {
                $tempDates[] = $fromObj->toDateString();
            }
            $fromObj = $fromObj->modify('1 day');
        }

        if (count($tempDates) < 1) {
            return true;
        }

        $options['checkDate'] = $tempDates;
        $query = $this->find('isReserveIn', $options);

        if ($query->count() < 1) {
            return true;
        }

        return false;
    }

    /**
     * 予約存在チェックquery
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findIsReserveIn(Query $query, array $options)
    {
        $tempDates = Hash::get($options, 'checkDate', []);

        // テンポラリテーブルに挿入
        $tempDatesTable = $this->getTableLocator()->get('TempDates');
        $insertQuery = $tempDatesTable->insertQuery()->insert(['date']);
        foreach ($tempDates as $tempDate) {
            $insertQuery->values(['date' => $tempDate]);
        }
        $insertQuery->execute();

        $sqlFrom = $this->driverExpression()->cast(
            'Reservations.usage_timestamp_from',
            TableSchema::TYPE_DATE
        );

        //実施時間が日跨ぎ稼働かを判定
        $timeFrom = Hash::get($options, 'time_from');
        $timeTo = Hash::get($options, 'time_to');
        $overDay = Hash::get($options, 'overDay', false);
        if (is_null($timeFrom) || is_null($timeTo)) {
            if ($timeFrom >= $timeTo) {
                $overDay = true;
            }
        }

        if (!$overDay) {
            $sqlTo = $this->driverExpression()->cast(
                'Reservations.usage_timestamp_to',
                TableSchema::TYPE_DATE
            );
        } else {
            $sqlTo = $query->func()->dateAdd(
                $this->driverExpression()->cast(
                    'Reservations.usage_timestamp_to',
                    TableSchema::TYPE_DATE
                ),
                '-1',
                'day'
            );
        }

        $subQuery = $tempDatesTable->find()->where([
            function (QueryExpression $exp) use ($sqlFrom, $sqlTo) {
                return $exp->between('TempDates.date', $sqlFrom, $sqlTo);
            },
        ]);

        $query->where("exists ({$subQuery})");

        $eventIds = Hash::get($options, 'event_id', []);
        if (is_array($eventIds)) {
            if (count($eventIds) >= 1) {
                $query->where([
                    'Reservations.event_id IN' => $eventIds,
                ]);
            }
        } else {
            $query->where([
                'Reservations.event_id' => $eventIds,
            ]);
        }

        if (isset($options['status'])) {
            $query->where(
                [
                    'Reservations.reservation_status_id NOT IN' => $options['status'],
                ]
            );
        }

        $query->select([
            'Reservations.id',
            'Reservations.event_id',
            'Reservations.usage_timestamp_from',
            'Reservations.usage_timestamp_to',
        ]);

        return $query;
    }

    /**
     * 予約枠IDの予約されている日時を返却
     *
     * @param int $eventId 予約枠ID
     * @param bool $all 過去含める
     * @return array
     */
    public function getReserveDateList(int $eventId, $all = false)
    {
        $query = $this->find('all')->select(['usage_timestamp_from', 'usage_timestamp_to'])
            ->where([
                'event_id' => $eventId,
                'usage_timestamp_from >=' => $this->commonData()->getNowDateTime(),
            ])
            ->order('usage_timestamp_from');

        $query->formatResults(function (\Cake\Collection\CollectionInterface $results) {
            $date = [];
            foreach ($results as $row) {
                $to = $row['usage_timestamp_to'];
                $from = $row['usage_timestamp_from'];

                if ($from->diffInDays($to) === 0) {
                    $date[$from->format('Y/m/d')] = clone $from;
                } else {
                    while ($from->diffInDays($to) != 0) {
                        $date[$from->format('Y/m/d')] = clone $from;
                        $from = $from->addDays(1);
                    }
                }
            }

            return $date;
        });

        return $query->toArray();
    }

    /**
     * 会員削除判定用のExpressionを生成
     *
     * @param \Cake\ORM\Query $query Query
     * @return \Cake\Database\ExpressionInterface
     */
    protected function createUserDeletedExpression($query)
    {
        $expression = $query->newExpr()->case();
        $expression
            ->when([$query->newExpr()->isNull('Users.id')])
            ->then(Configure::readOrFail('Master.common.flg.on'))
            ->else(Configure::readOrFail('Master.common.flg.off'));

        return $expression;
    }

    /**
     * チェック情報からCSVファイルを生成
     *
     * @param array $checked チェック情報
     * @param array $condition 検索条件
     * @return callable
     */
    public function createCsvCheck(array $checked, array $condition = [])
    {
        return $this->createCsv([], 'checkReservations', [
            'checked' => $checked,
            'condition' => $condition,
        ]);
    }

    /**
     * CSVファイルを生成
     *
     * @param array $searchCondition 検索条件
     * @param string $finder ファインダー
     * @param array $options オプション
     * @return callable
     */
    public function createCsv(array $searchCondition, string $finder = 'searchList', array $options = [])
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $columns = Configure::readOrFail('Setting.csv.download.reservation.header');
        if (!$systemSettingsTable->getData()->usePayment()) {
            unset($columns[FormItemsTable::CSV_COLUMN_PAYMENT_METHOD]);
            unset($columns[FormItemsTable::CSV_COLUMN_PAYMENT_STATUS]);
        }
        $smatLock = new SmartLockLinkage();
        if ($smatLock->useRemoteLock()) {
            unset($columns[FormItemsTable::CSV_COLUMN_SMART_LOCK_KEY_URL]);
        } elseif ($smatLock->useAkerun()) {
            unset($columns[FormItemsTable::CSV_COLUMN_SMART_LOCK_PIN]);
            unset($columns[FormItemsTable::CSV_COLUMN_SMART_LOCK_UNIVERSAL_ACCESS_KEY]);
        } else {
            unset($columns[FormItemsTable::CSV_COLUMN_SMART_LOCK_PIN]);
            unset($columns[FormItemsTable::CSV_COLUMN_SMART_LOCK_KEY_URL]);
            unset($columns[FormItemsTable::CSV_COLUMN_SMART_LOCK_UNIVERSAL_ACCESS_KEY]);
        }

        $csvItems = $formItemsTable->generateCsvItems('output', array_keys($columns));
        $header = $formItemsTable->generateCsvHeader($csvItems, $columns);

        $callback = $this->getCsvStreamCallback($header, function () use (
            $csvItems,
            $searchCondition,
            $options,
            $finder
        ) {
            /** @var \App\Model\Table\FormItemsTable $formItemsTable */
            $formItemsTable = $this->getTableLocator()->get('FormItems');
            $page = 1;
            $lastPage = 1;

            // 取得時のデータを保持するためトランザクション開始
            //一度でデータを取得するとメモリオーバーとなるため数件に分けて取得
            $connection = $this->getConnection();
            $connection->begin();
            while (true) {
                $searchCondition['page'] = $page;
                $paginator = new NumericPaginator();

                unset($query);
                $query = $paginator->paginate($this, [
                    'page' => $page,
                    'limit' => static::CSV_PAGEVIEW,
                ], [
                    'finder' => [
                        $finder => [
                            'inputs' => $searchCondition,
                            'bufferOff' => true,
                        ] + $options,
                    ],
                    'maxLimit' => static::CSV_PAGEVIEW,
                ]);

                foreach ($query as $reservation) {
                    yield $formItemsTable->generateCsvData($csvItems, [$this, 'formatCsvData'], [
                        'event' => $reservation->get('event'),
                        'user' => $reservation->get('user'),
                        'reservation' => $reservation,
                    ]);
                }

                if ($page === 1) {
                    $pagiData = $paginator->getPagingParams();
                    $lastPage = $pagiData['Reservations']['pageCount'];
                }

                if ($page >= $lastPage || empty($lastPage)) {
                    break;
                }

                $page++;
            }
            // rollback
            $connection->rollback();
        });

        return $callback;
    }

    /**
     * CSVへ出力するデータを生成
     *
     * @param int $column カラム
     * @param array $options オプション
     * @return string データ
     */
    public function formatCsvData(int $column, array $options)
    {
        $event = $options['event'];
        $reservation = $options['reservation'];

        $value = '';
        if (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_RESERVATION_ID)) {
            $value = $reservation->get('id');
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_RESERVATION_STATUS_ID)) {
            /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
            $reservationStatusesTable = $this->getAssociation('ReservationStatuses')->getTarget();

            $value = $this->csvFormat()->csvForId(
                $reservation->get('reservation_status_id'),
                $reservationStatusesTable->getReservationStatusName($reservation->get('reservation_status_id'))
            );
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_EVENT_PLANS)) {
            if ((string)$event->get('time_plan') === ((string)EventEntity::PLAN_MULTIPLE)) {
                $planValues = [];
                foreach ((array)$reservation->get('reservation_event_plans') as $reservationEventPlan) {
                    $planValues[] = $this->csvFormat()->csvForId(
                        $reservationEventPlan->get('event_plan')->get('id'),
                        $reservationEventPlan->get('event_plan')->get('name')
                    );
                }
                $value = $this->csvFormat()->csvForMultiple($planValues);
            }
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_CHARGE)) {
            $value = $reservation->get('charge');
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_PAYMENT_METHOD)) {
            if ($reservation->has('payment_method_id')) {
                /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
                $paymentMethodsTable = $this->getAssociation('PaymentMethods')->getTarget();

                $value = $this->csvFormat()->csvForId(
                    $reservation->get('payment_method_id'),
                    $paymentMethodsTable->getPaymentMethodName($reservation->get('payment_method_id'))
                );
            }
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_PAYMENT_STATUS)) {
            if ($reservation->has('payment_status_id')) {
                /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
                $paymentStatusesTable = $this->getAssociation('PaymentStatuses')->getTarget();

                $value = $this->csvFormat()->csvForId(
                    $reservation->get('payment_status_id'),
                    $paymentStatusesTable->getPaymentStatusName($reservation->get('payment_status_id'))
                );
            }
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_RESERVATION_CREATED)) {
            $value = $this->csvFormat()->csvForTimestamp($reservation->get('created'));
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_RESERVATION_MODIFIED)) {
            $value = $this->csvFormat()->csvForTimestamp($reservation->get('modified'));
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_RECEPTION_STATUS_ID)) {
            /** @var \App\Model\Table\ReceptionStatusesTable $receptionStatusesTable */
            $receptionStatusesTable = $this->getAssociation('ReceptionStatuses')->getTarget();
            $receptionStatusId = $reservation->get('reception_status_id');
            if ($receptionStatusId === null) {
                $value = null;
            } else {
                $value = $this->csvFormat()->csvForId(
                    $receptionStatusId,
                    $receptionStatusesTable->getReceptionStatusName($reservation->get('reception_status_id'))
                );
            }
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_SMART_LOCK_PIN)) {
            if (!$reservation->getReservationSmartLockEntity()) {
                $value = null;
            } else {
                $value = $reservation->getReservationSmartLockEntity()->get('smart_lock_pin');
            }
        } elseif (
            ((string)$column) === ((string)FormItemsTable::CSV_COLUMN_SMART_LOCK_KEY_URL) ||
            ((string)$column) === ((string)FormItemsTable::CSV_COLUMN_SMART_LOCK_UNIVERSAL_ACCESS_KEY)
        ) {
            if (!$reservation->getReservationSmartLockEntity()) {
                $value = null;
            } else {
                $value = $reservation->getReservationSmartLockEntity()->get('smart_lock_key_url');
            }
        } else {
            if (isset($options['user'])) {
                /** @var \App\Model\Table\UsersTable $usersTable */
                $usersTable = $this->getTableLocator()->get('Users');

                $value = $usersTable->formatCsvData($column, $options);
            } else {
                if (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_USER_ID)) {
                    $value = $reservation->get('user_id');
                } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_WITHDRAWAL_FLG)) {
                    $value = Configure::readOrFail('Setting.csv.download.reservation.userDeleted');
                }
            }
        }

        return $value;
    }

    /**
     * サンプルCSVを生成
     *
     * @return string ファイルパス
     */
    public function createSampleCsv()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $columns = Configure::readOrFail('Setting.csv.import.reservation.header');
        if (!$systemSettingsTable->getData()->usePayment()) {
            unset($columns[FormItemsTable::CSV_COLUMN_PAYMENT_METHOD]);
            unset($columns[FormItemsTable::CSV_COLUMN_PAYMENT_STATUS]);
        }

        $csvItems = $formItemsTable->generateCsvItems('input', array_keys($columns));
        $header = $formItemsTable->generateCsvHeader($csvItems, $columns);

        $filePath = $this->createCsvFile($header, function () use ($csvItems) {
            /** @var \App\Model\Table\FormItemsTable $formItemsTable */
            $formItemsTable = $this->getTableLocator()->get('FormItems');

            yield $formItemsTable->generateCsvDescription($csvItems, [$this, 'formatCsvDescription']);
        });

        return $filePath;
    }

    /**
     * CSVへ出力する説明文を生成
     *
     * @param int $column カラム
     * @param array $options オプション
     * @return string 説明文
     */
    public function formatCsvDescription(int $column, array $options)
    {
        $description = '';
        if (
            ((string)$column) === ((string)FormItemsTable::CSV_COLUMN_USER_ID)
            || ((string)$column) === ((string)FormItemsTable::CSV_COLUMN_CHARGE)
        ) {
            $description = Configure::readOrFail('Setting.csv.import.sample.values');
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_RESERVATION_STATUS_ID)) {
            $valueOptions = $this->getFieldValueOptionsForRegister('reservationStatusId');

            $description = $this->csvFormat()->csvForHasManyId(array_keys($valueOptions), $valueOptions);
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_PAYMENT_METHOD)) {
            $valueOptions = $this->getFieldValueOptionsForRegister('paymentMethodId');

            $description = $this->csvFormat()->csvForHasManyId(array_keys($valueOptions), $valueOptions);
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_PAYMENT_STATUS)) {
            $valueOptions = $this->getFieldValueOptionsForRegister('paymentStatusId');

            $description = $this->csvFormat()->csvForHasManyId(array_keys($valueOptions), $valueOptions);
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_RECEPTION_STATUS_ID)) {
            $valueOptions = $this->getFieldValueOptionsForRegister('receptionStatusId');

            $description = $this->csvFormat()->csvForHasManyId(array_keys($valueOptions), $valueOptions);
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_EVENT_PLANS)) {
            /** @var \App\Model\Table\EventPlansTable $eventPlansTable */
            $eventPlansTable = $this->getTableLocator()->get('EventPlans');

            $plans = [];
            foreach ($eventPlansTable->find('reservationUploadFormat') as $eventPlan) {
                $event = $eventPlan->get('event');
                if (!isset($plans[$eventPlan->get('event_id')])) {
                    $eventValue = Configure::readOrFail('Setting.csv.import.sample.eventPlanData.event.value');
                    $replaceKeys = Configure::readOrFail('Setting.csv.import.sample.eventPlanData.event.replaceKey');
                    foreach ($replaceKeys as $replaceKey) {
                        $eventValue = preg_replace(
                            '/' . preg_quote('%' . strtoupper($replaceKey) . '%', '/') . '/',
                            StringUtility::pregReplaceQuote($event->get($replaceKey)),
                            $eventValue
                        );
                    }

                    $plans[$eventPlan->get('event_id')]['event'] = $eventValue;
                }

                if ((string)$event->get('type') === (string)EventEntity::TYPE_TIME) {
                    $eventPlan->set('usage_day', null);
                } elseif ((string)$event->get('type') === (string)EventEntity::TYPE_DAY) {
                    $eventPlan->set('usage_time', null);
                }

                $planValue = Configure::readOrFail('Setting.csv.import.sample.eventPlanData.plan.value');
                $replaceKeys = Configure::readOrFail('Setting.csv.import.sample.eventPlanData.plan.replaceKey');
                foreach ($replaceKeys as $replaceKey) {
                    $planValue = preg_replace(
                        '/' . preg_quote('%' . strtoupper($replaceKey) . '%', '/') . '/',
                        StringUtility::pregReplaceQuote($eventPlan->get($replaceKey)),
                        $planValue
                    );
                }

                $plans[$eventPlan->get('event_id')]['plan'][] = $planValue;
            }

            $description = Configure::readOrFail('Setting.csv.import.sample.values');
            if (!empty($plans)) {
                $eventDelimiter = Configure::readOrFail('Setting.csv.import.sample.eventPlanData.event.delimiter');
                $planDelimiter = Configure::readOrFail('Setting.csv.import.sample.eventPlanData.plan.delimiter');
                foreach ($plans as $plan) {
                    $description .= "\n" . $plan['event'] . $eventDelimiter . implode($planDelimiter, $plan['plan']);
                }
            }
        } else {
            /** @var \App\Model\Table\UsersTable $usersTable */
            $usersTable = $this->getTableLocator()->get('Users');

            $description = $usersTable->formatCsvDescription($column, $options);
        }

        return $description;
    }

    /**
     * 非会員予約が存在するか
     *
     * @param string $mail メールアドレス
     * @param int $id 予約ID
     * @return bool
     */
    public function hasGuestReserve($mail, $id)
    {
        $query = $this->find();
        $query->select(['id', 'user_id'])->contain([
            'Users' => [
                'fields' => ['id'],
            ],
        ]);

        $query->where([
            'Users.mail' => $mail,
            'Users.guest_flg' => User::GUEST_FLG_ON,
            'Reservations.id' => $id,
            'Reservations.usage_timestamp_to >' => $this->CommonData()->getNowDateTime(),
        ]);

        if ($query->count() >= 1) {
            return true;
        }

        return false;
    }

    /**
     * 予約カレンダーを取得
     *
     * @param mixed $calendarType カレンダータイプ
     * @param bool|null $adminFlg 管理者側フラグ
     * @return \App\Model\EventCalendar\AbstractCalendarType|null
     */
    public function getEventCalendar($calendarType = null, ?bool $adminFlg = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        if (!isset($adminFlg)) {
            $adminFlg = $this->commonData()->existsAdminLoginData();
        }
        if (!isset($calendarType) || $calendarType === '') {
            if ($adminFlg) {
                $calendarType = $siteSettingsTable->getData()->get('admin_calendar_type_default');
            } else {
                $calendarType = $this->commonData()->getUserAuthority()->get('calendar_type_default');
            }
        }

        $types = Configure::readOrFail('Master.event.calendarType');
        if (!is_scalar($calendarType) || !ArrayUtility::inArray($calendarType, array_keys($types))) {
            return null;
        }

        return CalendarTypeFactory::getInstance((int)$calendarType, $adminFlg);
    }

    /**
     * 予約カレンダーを生成
     *
     * @param \App\Model\EventCalendar\AbstractCalendarType $eventCalendar カレンダー
     * @param array $searchData 検索データ
     * @param \App\Controller\Component\PaginationComponent|null $paginator ページネーション
     * @param int|null $userId 会員ID
     * @param bool $selectCalendar カレンダー選択
     * @param array|null $continuousData 連続予約
     * @return void
     */
    public function createEventCalendar(
        AbstractCalendarType $eventCalendar,
        array $searchData,
        ?PaginationComponent $paginator = null,
        $userId = null,
        $selectCalendar = false,
        ?array $continuousData = null
    ) {
        // ページネーションの設定
        if (isset($paginator)) {
            $eventCalendar->setPaginator($paginator);
        }

        // 会員IDの設定
        $eventCalendar->setUserId($userId);

        // 予約IDの設定
        if ($selectCalendar) {
            $reservationId = Hash::get($searchData, 'edit_reservation_id');
            if (isset($reservationId)) {
                $reservationId = (int)$reservationId;
            } else {
                $reservationId = null;
            }
            $eventCalendar->setExcludeReservationId($reservationId);
        }

        // 表示時間の設定
        if ($eventCalendar->isAdmin()) {
            $displayAllTime = Hash::get($searchData, 'display_all_time');
            if (((string)$displayAllTime) !== (string)Configure::readOrFail('Master.common.flg.on')) {
                $eventCalendar->setLimitDisplayTime(true);
            }
        } else {
            $eventCalendar->setLimitDisplayTime(true);
        }

        // 続けて予約の設定
        $eventCalendar->setContinuousData($continuousData);

        // 台帳表示項目の設定
        if ($eventCalendar->isAdmin()) {
            $displayItem = Hash::get($searchData, 'display_item');
            if (isset($displayItem)) {
                $displayItem = (int)$displayItem;
            } else {
                $displayItem = null;
            }
            $eventCalendar->setCalendarDisplayItem($displayItem);
        }

        // 検索条件の設定
        $eventCalendar->setSearchData($searchData);

        // カレンダー生成
        $eventCalendar->setDate(Hash::get($searchData, 'date'));
        $eventCalendar->searchEvents();
        $eventCalendar->createCalendar();
    }

    /**
     * カレンダーポップアップを取得
     *
     * @param mixed $calendarPopupType ポップアップタイプ
     * @param bool|null $adminFlg 管理者側フラグ
     * @return \App\Model\EventCalendar\AbstractCalendarPopup|null
     */
    public function getEventCalendarPopup($calendarPopupType, ?bool $adminFlg = null)
    {
        if (!isset($adminFlg)) {
            $adminFlg = $this->commonData()->existsAdminLoginData();
        }
        $types = Configure::readOrFail('Master.reservation.calendarPopupType');
        if (!Validation::isScalar($calendarPopupType) || !Validation::inList($calendarPopupType, $types)) {
            return null;
        }

        return CalendarPopupFactory::getInstance((int)$calendarPopupType, $adminFlg);
    }

    /**
     * カレンダーポップアップを生成
     *
     * @param \App\Model\EventCalendar\AbstractCalendarPopup $eventCalendarPopup カレンダーポップアップ
     * @param array $searchData 検索データ
     * @param int|null $userId 会員ID
     * @param bool $selectCalendar カレンダー選択
     * @param array|null $continuousData 連続予約
     * @return void
     */
    public function createEventCalendarPopup(
        AbstractCalendarPopup $eventCalendarPopup,
        array $searchData,
        $userId = null,
        $selectCalendar = false,
        ?array $continuousData = null
    ) {
        // 会員IDの設定
        $eventCalendarPopup->setUserId($userId);

        // 予約IDの設定
        if ($selectCalendar) {
            $reservationId = Hash::get($searchData, 'edit_reservation_id');
            if (isset($reservationId)) {
                $reservationId = (int)$reservationId;
            } else {
                $reservationId = null;
            }
            $eventCalendarPopup->setExcludeReservationId($reservationId);
        }

        // 表示時間の設定
        if ($eventCalendarPopup->isAdmin()) {
            $displayAllTime = Hash::get($searchData, 'display_all_time');
            if (((string)$displayAllTime) !== (string)Configure::readOrFail('Master.common.flg.on')) {
                $eventCalendarPopup->setLimitDisplayTime(true);
            }
        } else {
            $eventCalendarPopup->setLimitDisplayTime(true);
        }

        // 続けて予約の設定
        $eventCalendarPopup->setContinuousData($continuousData);

        // 台帳表示項目の設定
        if ($eventCalendarPopup->isAdmin()) {
            $displayItem = Hash::get($searchData, 'display_item');
            if (isset($displayItem)) {
                $displayItem = (int)$displayItem;
            } else {
                $displayItem = null;
            }
            $eventCalendarPopup->setCalendarDisplayItem($displayItem);
        }

        // 検索条件の設定
        $eventCalendarPopup->setSearchData($searchData);

        // カレンダー生成
        $eventCalendarPopup->setDate($searchData['date']);
        $eventCalendarPopup->searchEvents();
        if (!empty($eventCalendarPopup->getEvents())) {
            $eventCalendarPopup->createTimetable();
        }
    }

    /**
     * 予約操作用のロックを取得
     *
     * @param array $reservations 予約エンティティ
     * @param bool $checkRules ルールチェックの有無
     * @return array ロックコード
     */
    protected function getLockForReservation($reservations, $checkRules = true)
    {
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->getAssociation('ReservationVideoMeetings')->getTarget();

        // 更新時の変更前データ
        $reservationIds = [];
        foreach ($reservations as $reservation) {
            if (!$reservation->isNew() && $reservation->has('id')) {
                $reservationIds[] = $reservation->get('id');
            }
        }
        $oldEntities = [];
        if (!empty($reservationIds)) {
            $oldEntities = $this->find()
                ->contain([
                    'Events' => [
                        'fields' => [
                            'id',
                            'organizer_id',
                        ],
                    ],
                ])
                ->where(['Reservations.id IN' => $reservationIds])
                ->toArray();
        }

        $lockIds = [];
        if ($checkRules) {
            foreach ($reservations as $reservation) {
                if ($reservation->has('user_id')) {
                    $userId = $reservation->get('user_id');
                    $lockIds[static::LOCK_TYPE_USER_RESERVATION][$userId] = $userId;
                }

                $eventId = $reservation->get('event_id');
                $lockIds[static::LOCK_TYPE_EVENT_RESERVATION][$eventId] = $eventId;

                foreach ((array)$reservation->get('reservation_options') as $reservationOption) {
                    $optionId = $reservationOption->get('option_id');
                    $lockIds[static::LOCK_TYPE_OPTION_RESERVATION][$optionId] = $optionId;
                }
            }
        }

        // Zoom連携ユーザー用
        foreach (array_merge($reservations, $oldEntities) as $entity) {
            $zoomLockId = $reservationVideoMeetingsTable->getLockIdForZoom($entity);
            if (isset($zoomLockId)) {
                $lockIds[static::LOCK_TYPE_ZOOM_CONNECT_USER][$zoomLockId] = $zoomLockId;
            }
        }

        $lockCodes = [];
        foreach ($lockIds as $type => $ids) {
            sort($ids, SORT_NUMERIC);
            foreach ($ids as $id) {
                $lockCode = $this->generateLockCode((string)$id);
                $lockCodes[$type][$id] = $lockCode;

                $this->getLock((int)$type, $lockCode);
            }
        }

        return $lockCodes;
    }

    /**
     * 予約操作用のロックを解放
     *
     * @param array $lockCodes ロックコード
     * @return void
     */
    protected function releaseLockForReservation($lockCodes)
    {
        foreach ($lockCodes as $type => $codes) {
            foreach ($codes as $code) {
                $this->releaseLock($type, $code);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function tryLockForImport()
    {
        return $this->tryLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_RESERVATION);
    }

    /**
     * @inheritDoc
     */
    public function getLockForImport()
    {
        $this->getLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_RESERVATION);
    }

    /**
     * @inheritDoc
     */
    public function releaseLockForImport()
    {
        $this->releaseLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_RESERVATION);
    }

    /**
     * 受付状況一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findUpdateReceptionStatus($query, $options)
    {
        $query->select([
            'id',
            'user_id',
            'event_id',
            'usage_timestamp_from',
            'usage_timestamp_to',
            'usage_time',
            'usage_day',
            'number',
            'charge',
            'reservation_status_id',
            'payment_method_id',
            'payment_status_id',
            'qr_code',
            'reception_status_id',
            'reception_timestamp',
            'created',
            'modified',
        ]);

        $query->contain([
            'Events' => [
                'fields' => [
                    'id',
                    'label_id',
                ],
            ],
        ]);

        $query->join([
            'ReservationStatuses' => [
                'table' => 'reservation_statuses',
                'type' => 'INNER',
                'conditions' => [
                    'ReservationStatuses.id = Reservations.reservation_status_id',
                ],
            ],
        ]);

        $query->where([
            'ReservationStatuses.status_type IN' => [
                ReservationStatus::STATUS_TYPE_FIXED,
                ReservationStatus::STATUS_TYPE_VISIT,
                ReservationStatus::STATUS_TYPE_ABSENCE,
            ],
        ]);

        return $query;
    }

    /**
     * 受付ステータスを更新する
     *
     * @param \Cake\Datasource\EntityInterface $reservation 予約情報
     * @param array $options オプション
     * @return array
     */
    public function updateReceptionStatus(EntityInterface $reservation, $options = [])
    {
        $result = [
            'save' => true,
            'status_type' => null,
            'message' => null,
        ];

        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        /** @var \App\Model\Table\ReceptionStatusesTable $receptionStatusesTable */
        $receptionStatusesTable = $this->getTableLocator()->get('ReceptionStatuses');

        $reservation->clean();

        $reservationStatusType = $reservationStatusesTable->getReservationStatusType(
            $reservation->get('reservation_status_id')
        );
        $statusType = $receptionStatusesTable->getReceptionStatusType((int)$reservation->get('reception_status_id'));
        if (!$this->checkReservedDate($reservation)) {
            // 予約利用日当日ではない場合
            $result['status_type'] = $statusType;
            $result['message'] = (string)__(Message::RECEPTION_STATUS_ERROR_NOT_RESERVED_DATE);
        } elseif (((string)$reservationStatusType) === ((string)ReservationStatus::STATUS_TYPE_FIXED)) {
            // 予約ステータスが「確定」の場合
            if ($reservation->get('reception_timestamp') === null) {
                // 受付日時が未設定なら更新
                $reservation->set('reception_timestamp', $this->commonData()->getNowDateTime());
            }

            // 予約ステータス
            $reservationStatuses = $reservationStatusesTable->getGroupingStatusType(
                [ReservationStatus::STATUS_TYPE_VISIT],
                false
            );
            foreach ($reservationStatuses as $reservationStatus) {
                $reservation->set('reservation_status_id', $reservationStatus->get('id'));
                break;
            }

            // 予約ステータスが「確定」なら入場中に更新する
            $result['status_type'] = ReceptionStatus::STATUS_TYPE_ADMISSION;
            $result['message'] = (string)__(Message::RECEPTION_STATUS_UPDATE_SUCCESS);

            // 受付ステータスを更新
            $reservation->set(
                'reception_status_id',
                $receptionStatusesTable->getReceptionStatusId($result['status_type'])
            );

            $result['save'] = $this->save($reservation);
        } elseif (((string)$reservationStatusType) === ((string)ReservationStatus::STATUS_TYPE_VISIT)) {
            // 予約ステータスが「来場済み」の場合
            if ($receptionStatusesTable->checkUpdateInterval($reservation->get('modified'))) {
                // 更新日時から1分以上経過している場合はステータスを更新する
                if (((string)$statusType) === ((string)ReceptionStatus::STATUS_TYPE_EXIT)) {
                    // 退場→入場中
                    $result['status_type'] = ReceptionStatus::STATUS_TYPE_ADMISSION;
                    $result['message'] = (string)__(Message::RECEPTION_STATUS_UPDATE_ADMISSION);
                } elseif (((string)$statusType) === ((string)ReceptionStatus::STATUS_TYPE_ADMISSION)) {
                    // 入場中→退場
                    $result['status_type'] = ReceptionStatus::STATUS_TYPE_EXIT;
                    $result['message'] = (string)__(Message::RECEPTION_STATUS_UPDATE_EXIT);
                }
                if (!is_null($result['status_type'])) {
                    // 受付ステータスを更新
                    $reservation->set(
                        'reception_status_id',
                        $receptionStatusesTable->getReceptionStatusId($result['status_type'])
                    );

                    $result['save'] = $this->save($reservation);
                }
            } else {
                // 更新日時から1分以内の場合はステータスを更新しない
                if (((string)$statusType) === ((string)ReceptionStatus::STATUS_TYPE_ADMISSION)) {
                    // 入場済み
                    $result['status_type'] = ReceptionStatus::STATUS_TYPE_ADMISSION;
                    $result['message'] = (string)__(Message::RECEPTION_STATUS_ALREADY_ADMISSION);
                } elseif (((string)$statusType) === ((string)ReceptionStatus::STATUS_TYPE_EXIT)) {
                    // 退場済み
                    $result['status_type'] = ReceptionStatus::STATUS_TYPE_EXIT;
                    $result['message'] = (string)__(Message::RECEPTION_STATUS_ALREADY_EXIT);
                }
            }
        } elseif (((string)$reservationStatusType) === ((string)ReservationStatus::STATUS_TYPE_ABSENCE)) {
            // 予約ステータスが「欠席」の場合は更新しない
            $result['status_type'] = $statusType;
            $result['message'] = (string)__(Message::RECEPTION_STATUS_ERROR_ABSENCE);
        }

        // 操作ログを出力する
        $this->saveOperationalLogs($reservation->get('id'), $options['saveOperation']);

        return $result;
    }

    /**
     * QRコード
     *
     * @param string|null $qrCode QRコードのデータ
     * @param bool $isView QrcodeControllerでの呼び出しの場合 ture
     * @return \App\Model\Entity\Reservation|null
     */
    public function getByQrCode($qrCode, $isView = false)
    {
        if (is_null($qrCode)) {
            return null;
        }

        $query = $this->find('edit');
        $query->contain([
            'ReservationStatuses' => [
                'fields' => [
                    'id',
                    'status_type',
                ],
            ],
        ]);
        $query->join([
            'ReservationStatuses' => [
                'table' => 'reservation_statuses',
                'type' => 'INNER',
                'conditions' => [
                    'ReservationStatuses.id = Reservations.reservation_status_id',
                ],
            ],
        ]);
        $query->where([
            'Reservations.qr_code' => $qrCode,
        ]);

        if (!$isView) {
            $query->where([
                'ReservationStatuses.status_type IN' => [
                    ReservationStatus::STATUS_TYPE_FIXED,
                    ReservationStatus::STATUS_TYPE_VISIT,
                    ReservationStatus::STATUS_TYPE_ABSENCE,
                ],
            ]);
        }

        $entity = $query->first();
        if (!($entity instanceof Reservation)) {
            return null;
        }

        return $entity;
    }

    /**
     * 予約情報を元にアプリに表示する項目を取得する
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return array
     */
    public function getAppDisplayFormGroups($reservation)
    {
        $result = [
            'form_groups' => [],
        ];

        $event = $reservation->getEventEntity();
        $user = $reservation->getUserEntity();

        if (empty($event) || empty($user)) {
            return $result;
        }

        $reservationFormGroups = $this->getReservationFormGroups(
            $event,
            $user->get('user_authority_id'),
            [
                'userId' => $user->get('id'),
                'reservationId' => $reservation->get('id'),
                'reservation' => $reservation,
                'usageTimestampFrom' => $reservation->get('usage_timestamp_from'),
                'isApp' => true,
            ]
        );

        foreach ($reservationFormGroups as $formGroups) {
            foreach ($formGroups as $formGroup) {
                $formGroupName = '';
                if ((string)$formGroup->get('name_display_flg') === ((string)FormGroup::NAME_DISPLAY_FLG_ON)) {
                    $formGroupName = $formGroup->get('name');
                }
                $formItems = [];
                foreach ($formGroup->get('form_items') as $formItem) {
                    array_push($formItems, [
                        'name' => $formItem->get('name'),
                        'value' => $formItem->getInputTypeItem()->getAppValue([
                            'event' => $event,
                            'user' => $user,
                            'reservation' => $reservation,
                        ]),
                    ]);
                }
                array_push($result['form_groups'], [
                    'form_group_name' => $formGroupName,
                    'form_items' => $formItems,
                ]);
            }
        }

        return $result;
    }

    /**
     * 予約データのQRコードを更新する
     *
     * @param string|int $id 予約ID
     * @param string $qrCode QRコード
     * @return void
     */
    public function updateQrCode($id, $qrCode)
    {
        $this->updateQuery()->update()->set(['qr_code' => $qrCode])->where(['id' => $id])->execute();
    }

    /**
     * 受付一覧での予約の取得条件
     * 予約利用日の当日(※日付のみを条件)
     * 複数日に渡る予約の場合、予約期間中のものを当日として扱う
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @return \Cake\ORM\Query
     */
    protected function conditionToday(Query $query)
    {
        $castFrom = $this->driverExpression()->cast(
            'Reservations.usage_timestamp_from',
            TableSchema::TYPE_DATE
        );
        $castTo = $this->driverExpression()->cast('Reservations.usage_timestamp_to', TableSchema::TYPE_DATE);

        $now = FrozenTime::now();
        $query->where([
            'AND' => [
                function ($exp) use ($castFrom, $now) {
                    return $exp->lte($castFrom, $now->format('Y-m-d'));
                },
                function ($exp) use ($castTo, $now) {
                    return $exp->gte($castTo, $now->format('Y-m-d'));
                },
            ],
        ]);

        return $query;
    }

    /**
     * 予約利用日当日か判定
     * 予約利用日当日の場合 true
     *
     * @param \Cake\Datasource\EntityInterface $reservation 予約
     * @return bool
     */
    public function checkReservedDate(EntityInterface $reservation)
    {
        $dateFrom = DateTimeUtility::convertToDateObject($reservation->get('usage_timestamp_from'));
        $dateTo = DateTimeUtility::convertToDateObject($reservation->get('usage_timestamp_to'));
        $today = DateTimeUtility::convertToDateObject(FrozenTime::now());
        if ($dateFrom <= $today && $dateTo >= $today) {
            return true;
        }

        return false;
    }

    /**
     * スマートロック連携
     *
     * @param \Cake\Datasource\EntityInterface $reservation 予約
     * @param \Cake\Datasource\EntityInterface|null $oldReservation 変更前予約
     * @param \Cake\Datasource\EntityInterface $originalUser save前の会員entity
     * @param bool $afterRedirectPaymentFlg 決済後の連携フラグ(通常はオフ)
     * @return void
     */
    protected function smartLockLinkage(
        EntityInterface $reservation,
        ?EntityInterface $oldReservation,
        ?EntityInterface $originalUser = null,
        bool $afterRedirectPaymentFlg = false
    ): void {
        $smartLockLinkage = new SmartLockLinkage();
        if (!$smartLockLinkage->useSmartLock()) {
            return;
        }

        if ($afterRedirectPaymentFlg) {
            $reservation->set('after_redirect_payment_flg', true);
        }

        if (isset($originalUser)) {
            $originalUser->set('forReservation', true);
        }

        /** @var \App\Model\Entity\Reservation $reservation */
        if (is_null($oldReservation)) {
            /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
            $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
            $statusType =
                $reservationStatusesTable->getReservationStatusType($reservation->get('reservation_status_id'));
            if ($statusType === ReservationStatus::STATUS_TYPE_FIXED) {
                // 予約登録時であることを示す目印をつける
                $reservation->set('smart_lock_add_reservation_flg', true);
                // 新規登録かつ確定ステータスならスケジュール登録
                $reservation->set('original_user', $originalUser);
                $smartLockLinkage->addSchedule($reservation);
                $reservation->set('original_user', null);
            }
        } else {
            /** @var \App\Model\Entity\Reservation $oldReservation */
            $reservation->set('original_user', $originalUser);
            $smartLockLinkage->changeSchedule($reservation, $oldReservation);
            $reservation->set('original_user', null);
        }
    }

    /**
     * リンク型決済成功時の処理
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param array $paymentResult 決済結果
     * @return void
     */
    public function onLinkPaymentSuccess(Reservation $reservation, array $paymentResult)
    {
        if (!$reservation->getReservationPayment()->isPaymentProcessing()) {
            return;
        }

        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
        /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
        $paymentStatusesTable = $this->getTableLocator()->get('PaymentStatuses');

        $paymentStatus = $paymentStatusesTable->getDataByDefaultType(
            PaymentStatus::TYPE_PAYMENT_COMPLETE
        );

        // 自動返信メール送信データの生成
        $autoReplyMailHistory = null;
        if (!$reservation->isCanceled()) {
            $reservation->set('payment_status_id', reset($paymentStatus));
            $autoReplyMailHistory = $autoReplyMailHistoriesTable->generateDataForReservation(
                $reservation,
                AutoReplyMail::TYPE_RESERVE_ADD
            );
        }
        if (isset($autoReplyMailHistory)) {
            $autoReplyMailHistory->setDataEntity([
                'user' => $reservation->getUserEntity(),
                'reservation' => $reservation,
            ]);
            $reservation->set('auto_reply_mail_histories', [$autoReplyMailHistory]);
        }

        $result = $this->getConnection()->transactional(
            function () use ($reservation, $paymentResult, $autoReplyMailHistory, $paymentStatus) {
                /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
                $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');
                /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
                $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
                /** @var \App\Model\Table\ReservationSmartLocksTable $reservationSmartLocksTable */
                $reservationSmartLocksTable = $this->fetchTable('ReservationSmartLocks');
                /** @var \App\Model\Table\UsersTable $usersTable */
                $usersTable = $this->fetchTable('Users');
                /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
                $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

                $this->updateAll([
                    'payment_status_id' => reset($paymentStatus),
                    'modified' => $this->commonData()->getNowDateTime(),
                ], ['id' => $reservation->get('id')]);

                $reservationPaymentsTable->updateAll([
                    'status' => ReservationPayment::STATUS_COMPLETED,
                    'payment_tracking_id' => Hash::get($paymentResult, 'payment_tracking_id'),
                    'payment_process_date' => Hash::get($paymentResult, 'payment_process_date'),
                    'receive_result_flg' => ReservationPayment::RECEIVE_RESULT_FLG_ON,
                    'modified' => $this->commonData()->getNowDateTime(),
                ], ['id' => $reservation->getReservationPayment()->get('id')]);

                // スマートロック連携
                /** @var \App\Model\Entity\User|null $user */
                $user = $usersTable
                    ->find('SmartLock')
                    ->where(['Users.id' => $reservation->get('user_id')])
                    ->first();

                $paymentSetting = $paymentSettingsTable->getData();

                try {
                    // 決済後の連携用の処理を実施
                    $this->smartLockLinkage($reservation, null, $user, true);
                } catch (Throwable $e) {
                    // スマートロック連携が失敗しても止まらないようにする
                }

                try {
                    if (isset($autoReplyMailHistory)) {
                        $reservationSmartLock = $reservationSmartLocksTable
                            ->find('reservation', [
                                'inputs' => [
                                    'reservation_id' => $reservation->get('id'),
                                ],
                            ])
                            ->first();
                        if ($reservationSmartLock instanceof ReservationSmartLock) {
                            /** @var array|null $mailData */
                            $mailData = $autoReplyMailHistory->get('data');
                            if (isset($mailData['reservation'])) {
                                // 自動返信メール履歴の情報に予約スマートロック情報をセット
                                $mailData['reservation']['reservation_smart_lock'] = $reservationSmartLock;
                            }
                            $autoReplyMailHistory->set('data', $mailData);
                        }

                        $autoReplyMailHistoriesTable->saveOrFail($autoReplyMailHistory);
                    }
                } catch (Throwable $e) {
                    // スマートロック連携の取消
                    $smartLockLinkage = new SmartLockLinkage();
                    if ($smartLockLinkage->useSmartLock()) {
                        $smartLockLinkage->deleteSchedule($reservation);
                    }

                    throw $e;
                }

                return true;
            }
        );
        if (!$result) {
            throw new CakeException();
        }

        $this->sendReservationMail($reservation);

        // キャンセル済みの場合は管理者へ通知
        if ($reservation->isCanceled()) {
            /** @var \App\Mailer\AdminMailer $mailer */
            $mailer = $this->getMailer('Admin');

            $mailer->sendPayCanceledReservation($reservation);
        }
    }

    /**
     * 3Dセキュア決済成功時の処理
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param array $paymentResult 決済結果
     * @return void
     */
    public function onThreeDSecureSuccess(Reservation $reservation, array $paymentResult)
    {
        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();

        // 自動返信メール送信データの生成
        $autoReplyMailHistory = null;
        if (!$reservation->isCanceled()) {
            $reservation->set('payment_status_id', $paymentSetting->getPaymentSuccessStatus());
            $autoReplyMailHistory = $autoReplyMailHistoriesTable->generateDataForReservation(
                $reservation,
                AutoReplyMail::TYPE_RESERVE_ADD
            );
        }
        if (isset($autoReplyMailHistory)) {
            $autoReplyMailHistory->setDataEntity([
                'user' => $reservation->getUserEntity(),
                'reservation' => $reservation,
            ]);
            $reservation->set('auto_reply_mail_histories', [$autoReplyMailHistory]);
        }

        $result = $this->getConnection()->transactional(
            function () use ($reservation, $paymentResult, $autoReplyMailHistory, $paymentSetting) {
                /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
                $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');
                /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
                $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
                /** @var \App\Model\Table\ReservationSmartLocksTable $reservationSmartLocksTable */
                $reservationSmartLocksTable = $this->fetchTable('ReservationSmartLocks');
                /** @var \App\Model\Table\UsersTable $usersTable */
                $usersTable = $this->fetchTable('Users');

                $this->updateAll([
                    'payment_status_id' => $paymentSetting->getPaymentSuccessStatus(),
                    'modified' => $this->commonData()->getNowDateTime(),
                ], ['id' => $reservation->get('id')]);

                $updates = [
                    'status' => ReservationPayment::STATUS_COMPLETED,
                    'payment_tran_id' => Hash::get($paymentResult, 'payment_tran_id'),
                    'payment_process_date' => Hash::get($paymentResult, 'payment_process_date'),
                    'modified' => $this->commonData()->getNowDateTime(),
                ];
                if ($paymentSetting->isPaymentServiceSb()) {
                    $updates['payment_tracking_id'] = Hash::get($paymentResult, 'payment_tracking_id');
                }

                $reservationPaymentsTable->updateAll($updates, [
                    'id' => $reservation->getReservationPayment()->get('id'),
                ]);

                // スマートロック連携
                /** @var \App\Model\Entity\User|null $user */
                $user = $usersTable
                    ->find('SmartLock')
                    ->where(['Users.id' => $reservation->get('user_id')])
                    ->first();

                try {
                    // 決済後の連携用の処理を実施
                    $this->smartLockLinkage($reservation, null, $user, true);
                } catch (Throwable $e) {
                    // スマートロック連携が失敗しても止まらないようにする
                }

                try {
                    if (isset($autoReplyMailHistory)) {
                        $reservationSmartLock = $reservationSmartLocksTable
                            ->find('reservation', [
                                'inputs' => [
                                    'reservation_id' => $reservation->get('id'),
                                ],
                            ])
                            ->first();
                        if ($reservationSmartLock instanceof ReservationSmartLock) {
                            /** @var array|null $mailData */
                            $mailData = $autoReplyMailHistory->get('data');
                            if (isset($mailData['reservation'])) {
                                // 自動返信メール履歴の情報に予約スマートロック情報をセット
                                $mailData['reservation']['reservation_smart_lock'] = $reservationSmartLock;
                            }
                            $autoReplyMailHistory->set('data', $mailData);
                        }

                        $autoReplyMailHistoriesTable->saveOrFail($autoReplyMailHistory);
                    }
                } catch (Throwable $e) {
                    // スマートロック連携の取消
                    $smartLockLinkage = new SmartLockLinkage();
                    if ($smartLockLinkage->useSmartLock()) {
                        $smartLockLinkage->deleteSchedule($reservation);
                    }

                    throw $e;
                }

                return true;
            }
        );
        if (!$result) {
            throw new CakeException();
        }

        $this->sendReservationMail($reservation);

        // キャンセル済みの場合は管理者へ通知
        if ($reservation->isCanceled()) {
            /** @var \App\Mailer\AdminMailer $mailer */
            $mailer = $this->getMailer('Admin');

            $mailer->sendPayCanceledReservation($reservation);
        }
    }

    /**
     * 3Dセキュア決済失敗時の処理
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return void
     */
    public function onThreeDSecureFail(Reservation $reservation)
    {
        $result = $this->getConnection()->transactional(function () use ($reservation) {
            /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
            $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
            /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
            $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

            $reservationPaymentsTable->updateAll([
                'status' => ReservationPayment::STATUS_ERROR,
                'modified' => $this->commonData()->getNowDateTime(),
            ], ['id' => $reservation->getReservationPayment()->get('id')]);

            $this->cancelReservation($reservation);

            return true;
        });
        if (!$result) {
            throw new CakeException();
        }
    }

    /**
     * 未決済予約の自動キャンセル
     *
     * @param \Cake\I18n\FrozenTime $dateTime 対象日時（現在日時）
     * @return void
     */
    public function cancelNoPayment(FrozenTime $dateTime)
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getData();
        if (!isset($paymentSetting) || !$paymentSetting->isPaymentServiceSb()) {
            return;
        }

        $cancelIds = [];
        $errorIds = [];
        $query = $this->find('NoPaymentReservations', [
            'paymentService' => PaymentSetting::PAYMENT_SERVICE_SB,
            'paymentMethodTypes' => [
                PaymentMethod::TYPE_PAYPAY,
            ],
            'paymentLimit' => $dateTime,
        ]);
        foreach ($query as $data) {
            $result = $this->executeSafe(function () use ($data, &$cancelIds) {
                if ($data->getReservationPayment()->isPaymentCompleted()) {
                    try {
                        $reservation = $this->get($data->get('id'), [
                            'finder' => 'edit',
                        ]);
                    } catch (RecordNotFoundException $e) {
                        throw new CakeException();
                    }

                    // 決済済みの場合は結果通知受取時と同様
                    $paymentData = $reservation->getReservationPayment()->getPaymentData();
                    $trackingId = Hash::get($paymentData, 'res_pay_method_info.tracking_id');
                    if (!is_string($trackingId) || $trackingId === '') {
                        $trackingId = null;
                    }
                    $orderDate = Hash::get($paymentData, 'res_pay_method_info.order_date');
                    if (is_string($orderDate) && $orderDate !== '') {
                        $orderDate = FrozenTime::createFromFormat('YmdHis', $orderDate);
                    } else {
                        $orderDate = null;
                    }
                    $this->onLinkPaymentSuccess($reservation, [
                        'payment_tracking_id' => $trackingId,
                        'payment_process_date' => $orderDate,
                    ]);
                } else {
                    try {
                        $reservation = $this->get($data->get('id'), [
                            'finder' => 'cancel',
                        ]);
                    } catch (RecordNotFoundException $e) {
                        throw new CakeException();
                    }

                    // 未決済の場合はキャンセル
                    $this->getConnection()->transactional(function () use ($reservation) {
                        /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
                        $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

                        $reservationPaymentsTable->updateAll([
                            'status' => ReservationPayment::STATUS_EXPIRED,
                            'modified' => $this->commonData()->getNowDateTime(),
                        ], ['id' => $reservation->getReservationPayment()->get('id')]);

                        $this->cancelReservation($reservation);
                    });

                    $cancelIds[$data->get('label_id')][] = $data->get('id');
                }
            });

            if (!$result) {
                $errorIds[$data->get('label_id')][] = $data->get('id');
            }
        }

        if (!empty($cancelIds)) {
            $this->executeSafe(function () use ($cancelIds) {
                /** @var \App\Mailer\AdminMailer $mailer */
                $mailer = $this->getMailer('Admin');

                $mailer->sendCancelNoPaymentReservation($cancelIds);
            });
        }

        if (!empty($errorIds)) {
            $this->executeSafe(function () use ($errorIds) {
                /** @var \App\Mailer\AdminMailer $mailer */
                $mailer = $this->getMailer('Admin');

                $mailer->sendFailedToCancelNoPaymentReservation($errorIds);
            });
        }
    }

    /**
     * 未決済予約の通知
     *
     * @param \Cake\I18n\FrozenTime $dateTime 対象日時（現在日時）
     * @return void
     */
    public function noticeNoPayment(FrozenTime $dateTime)
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getData();
        if (!isset($paymentSetting) || !$paymentSetting->isPaymentServiceSb()) {
            return;
        }

        $query = $this->find('NoPaymentReservations', [
            'paymentService' => PaymentSetting::PAYMENT_SERVICE_SB,
            'paymentMethodTypes' => [
                PaymentMethod::TYPE_APPLE_PAY,
                PaymentMethod::TYPE_AU_PAY,
            ],
            'paymentLimit' => $dateTime,
        ]);
        $query->disableHydration();

        $reservationIdsByLabel = [];
        $reservationPaymentIds = [];
        foreach ($query as $data) {
            $reservationIdsByLabel[$data['label_id']][] = $data['id'];
            $reservationPaymentIds[] = $data['reservation_payment_id'];
        }

        if (empty($reservationIdsByLabel)) {
            return;
        }

        $this->getConnection()->transactional(function () use ($reservationPaymentIds) {
            /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
            $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

            $reservationPaymentsTable->updateAll([
                'status' => ReservationPayment::STATUS_EXPIRED,
                'modified' => $this->commonData()->getNowDateTime(),
            ], ['id IN' => $reservationPaymentIds]);
        });

        $this->executeSafe(function () use ($reservationIdsByLabel) {
            /** @var \App\Mailer\AdminMailer $mailer */
            $mailer = $this->getMailer('Admin');

            $mailer->sendNoticeNoPaymentReservation($reservationIdsByLabel);
        });
    }

    /**
     * 3Dセキュア未決済予約の自動キャンセル
     *
     * @param \Cake\I18n\FrozenTime $dateTime 対象日時（現在日時）
     * @return void
     */
    public function cancelNotFinishThreeDSecure(FrozenTime $dateTime)
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getData();
        if (!isset($paymentSetting) || !$paymentSetting->isPaymentServiceGmo()) {
            return;
        }

        $cancelIds = [];
        $errorIds = [];
        $errorCodes = [];
        $query = $this->find('NoPaymentReservations', [
            'paymentService' => PaymentSetting::PAYMENT_SERVICE_GMO,
            'paymentMethodTypes' => [
                PaymentMethod::TYPE_CARD,
            ],
            'paymentLimit' => $dateTime,
        ]);
        foreach ($query as $data) {
            $result = $this->executeSafe(function () use ($data, &$cancelIds) {
                if ($data->getReservationPayment()->isPaymentCompleted()) {
                    try {
                        $reservation = $this->get($data->get('id'), [
                            'finder' => 'edit',
                        ]);
                    } catch (RecordNotFoundException $e) {
                        throw new CakeException();
                    }

                    // 決済済みの場合は3Dセキュア成功時と同様
                    $paymentData = $reservation->getReservationPayment()->getPaymentData();
                    $this->onThreeDSecureSuccess($reservation, [
                        'payment_tran_id' => Hash::get($paymentData, 'payment_tran_id'),
                        'payment_process_date' => Hash::get($paymentData, 'payment_process_date'),
                    ]);
                } elseif (!$data->getReservationPayment()->getPaymentError()) {
                    try {
                        $reservation = $this->get($data->get('id'), [
                            'finder' => 'cancel',
                        ]);
                    } catch (RecordNotFoundException $e) {
                        throw new CakeException();
                    }

                    // 未決済の場合はキャンセル
                    $this->getConnection()->transactional(function () use ($reservation) {
                        /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
                        $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

                        $reservationPaymentsTable->updateAll([
                            'status' => ReservationPayment::STATUS_EXPIRED,
                            'modified' => $this->commonData()->getNowDateTime(),
                        ], ['id' => $reservation->getReservationPayment()->get('id')]);

                        $this->cancelReservation($reservation);
                    });

                    $cancelIds[$data->get('label_id')][] = $data->get('id');
                } else {
                    // エラー時は決済エラーステータスへ更新
                    $this->getConnection()->transactional(function () use ($data) {
                        /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
                        $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

                        $reservationPaymentsTable->updateAll([
                            'status' => ReservationPayment::STATUS_ERROR,
                            'modified' => $this->commonData()->getNowDateTime(),
                        ], ['id' => $data->getReservationPayment()->get('id')]);
                    });

                    throw new CakeException(sprintf('決済自動キャンセルエラー（ID：%s）', $data->get('id')));
                }
            });

            if (!$result) {
                $errorIds[$data->get('label_id')][] = $data->get('id');
                $paymentError = $data->getReservationPayment()->getPaymentError();
                if (is_array($paymentError)) {
                    $errorCodes[$data->get('id')] = Hash::get($paymentError, 'ErrInfo');
                }
            }
        }

        if (!empty($cancelIds)) {
            $this->executeSafe(function () use ($cancelIds) {
                /** @var \App\Mailer\AdminMailer $mailer */
                $mailer = $this->getMailer('Admin');

                $mailer->sendCancelNoPaymentReservation($cancelIds);
            });
        }

        if (!empty($errorIds)) {
            $this->executeSafe(function () use ($errorIds, $errorCodes) {
                /** @var \App\Mailer\AdminMailer $mailer */
                $mailer = $this->getMailer('Admin');

                $mailer->sendFailedToCancelNoPaymentReservation($errorIds, $errorCodes);
            });
        }
    }

    /**
     * SBペイメント3Dセキュア未決済予約の自動キャンセル
     *
     * @param \Cake\I18n\FrozenTime $dateTime 対象日時（現在日時）
     * @return void
     */
    public function cancelNotFinishThreeDSecureOnSb(FrozenTime $dateTime): void
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getData();
        if (!(isset($paymentSetting) && $paymentSetting->isPaymentServiceSb())) {
            return;
        }

        $cancelIds = [];
        $query = $this->find('NoPaymentReservations', [
            'paymentService' => PaymentSetting::PAYMENT_SERVICE_SB,
            'paymentMethodTypes' => [
                PaymentMethod::TYPE_CARD,
            ],
            'paymentLimit' => $dateTime,
        ]);
        /** @var \App\Model\Entity\Reservation $data */
        foreach ($query as $data) {
            $this->executeSafe(function () use ($data, &$cancelIds) {
                try {
                    /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                    $reservation = $this->get($data->get('id'), [
                        'finder' => 'cancel',
                    ]);
                } catch (RecordNotFoundException $e) {
                    throw new CakeException();
                }

                // キャンセル処理
                $this->getConnection()->transactional(function () use ($reservation) {
                    /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
                    $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

                    $reservationPaymentsTable->updateAll([
                        'status' => ReservationPayment::STATUS_EXPIRED,
                        'modified' => $this->commonData()->getNowDateTime(),
                    ], ['id' => $reservation->getReservationPayment()->get('id')]);

                    $this->cancelReservation($reservation);
                });

                $cancelIds[$data->get('label_id')][] = $data->get('id');
            });
        }

        if (!empty($cancelIds)) {
            $this->executeSafe(function () use ($cancelIds) {
                /** @var \App\Mailer\AdminMailer $mailer */
                $mailer = $this->getMailer('Admin');

                $mailer->sendCancelNoPaymentReservation($cancelIds);
            });
        }
    }

    /**
     * 未決済予約のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findNoPaymentReservations(Query $query, array $options)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $query->select([
            'id' => 'Reservations.id',
            'reservation_payment_id' => 'ReservationPayments.id',
            'label_id' => 'Events.label_id',
        ]);

        $query->innerJoinWith('Events');

        $query->innerJoinWith('ReservationPayments', function ($paymentQuery) use ($options) {
            /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
            $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

            $paymentMethodIds = [];
            foreach ($options['paymentMethodTypes'] as $paymentMethodType) {
                $paymentMethodIds = array_merge(
                    $paymentMethodIds,
                    $paymentMethodsTable->getPaymentMethodIds($paymentMethodType)
                );
            }

            $paymentQuery->where([
                'ReservationPayments.payment_service' => $options['paymentService'],
                'ReservationPayments.payment_method_id IN' => $paymentMethodIds,
                'ReservationPayments.status' => ReservationPayment::STATUS_UNSETTLED,
                'ReservationPayments.payment_limit <' => $options['paymentLimit']->format('Y-m-d H:i:s'),
            ]);

            return $paymentQuery;
        });

        $notCancelReservationStatusId = [];
        foreach ($reservationStatusesTable->getNotCancelData() as $data) {
            $notCancelReservationStatusId[] = $data['id'];
        }
        $query->where([
            'Reservations.reservation_status_id IN' => $notCancelReservationStatusId,
        ]);

        $query->order([
            'Reservations.id' => 'ASC',
        ]);

        $query->disableBufferedResults();

        return $query;
    }

    /**
     * 決済期限切れ予約のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findExpiredReservations(Query $query, array $options)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $query->select([
            'id' => 'Reservations.id',
            'reservation_payment_id' => 'ReservationPayments.id',
            'label_id' => 'Events.label_id',
        ]);

        $query->join([
            'Events' => [
                'table' => 'events',
                'type' => 'INNER',
                'conditions' => 'Events.id = Reservations.event_id',
            ],
            'Labels' => [
                'table' => 'labels',
                'type' => 'LEFT',
                'conditions' => 'Labels.id = Events.label_id',
            ],
        ]);
        $labelsTable->joinQuery($query, 'Labels');

        $labelId = Hash::get($options, 'labelId');
        if (isset($labelId) && $labelId !== '') {
            $labelsTable->addNestWhere($query, (int)$labelId);
        }

        $query->innerJoinWith('ReservationPayments', function ($paymentQuery) {
            $nowDateTime = $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s');

            $paymentQuery->where([
                'OR' => [
                    [
                        'ReservationPayments.payment_limit <' => $nowDateTime,
                        'ReservationPayments.status IN' => ReservationPayment::STATUS_UNSETTLED,
                    ],
                    ['ReservationPayments.status IN' => ReservationPayment::STATUS_EXPIRED],
                ],
            ]);

            return $paymentQuery;
        });

        $notCancelReservationStatusId = [];
        foreach ($reservationStatusesTable->getNotCancelData() as $data) {
            $notCancelReservationStatusId[] = $data['id'];
        }
        $query->where([
            'Reservations.reservation_status_id IN' => $notCancelReservationStatusId,
        ]);

        $query->disableBufferedResults();

        return $query;
    }

    /**
     * 決済期限切れの予約が存在するかどうか
     *
     * @return bool
     */
    public function existsPaymentExpiredReservation()
    {
        $labelId = $this->commonData()->getAdminLoginLabel();
        $reservations = $this->find('ExpiredReservations', [
            'labelId' => $labelId,
        ]);

        if ($reservations->count() < 1) {
            return false;
        }

        return true;
    }

    /**
     * キャンセル時の決済取り消し処理
     *
     * @param \App\Model\Entity\Reservation $entity 対象予約
     * @param \App\Model\Entity\ReservationPayment $reservationPayment 決済情報
     * @param \App\Model\Entity\PaymentSetting $paymentSetting 決済設定
     * @return void
     */
    public function cancelApiPayment(
        Reservation $entity,
        ReservationPayment $reservationPayment,
        PaymentSetting $paymentSetting
    ) {
        $errors = $reservationPayment->cancelApiPayment($entity, $paymentSetting);

        if (!isset($errors)) {
            /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
            $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

            $reservationPaymentsTable->updateAll([
                'status' => ReservationPayment::STATUS_CANCEL,
                'payment_cancel_date' => $this->commonData()->getNowDateTime(),
                'modified' => $this->commonData()->getNowDateTime(),
            ], ['id' => $entity->getReservationPayment()->get('id')]);

            $newPaymentStatus = PaymentStatus::TYPE_PAYMENT_CANCEL;
        } else {
            /** @var \App\Mailer\AdminMailer $mailer */
            $mailer = $this->getMailer('Admin');

            $mailer->sendFailedRefundPaymentReservation($entity, (string)$errors['err_code']);

            $newPaymentStatus = PaymentStatus::TYPE_REFUND_PROCESS;
        }

        $this->updateAll([
            'payment_status_id' => $newPaymentStatus,
            'modified' => $this->commonData()->getNowDateTime(),
        ], ['id' => $entity->get('id')]);
    }

    /**
     * スマートロック未連携の予約が存在するかどうか
     *
     * @return bool
     */
    public function existsSmartLockUnlinkedReservation()
    {
        $labelId = $this->commonData()->getAdminLoginLabel();
        $smartLockLinkage = new SmartLockLinkage();
        if (!$smartLockLinkage->useSmartLock()) {
            return false;
        }

        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        // 予約ステータス
        $reservationStatusIds = array_merge(
            $reservationStatusesTable->getReservationStatusIds(ReservationStatus::STATUS_TYPE_FIXED),
            $reservationStatusesTable->getReservationStatusIds(ReservationStatus::STATUS_TYPE_VISIT),
        );

        // 利用日時To用に現在日時を取得(指定した単位で分を切り捨て)
        $nowDateTime = DateTimeUtility::truncateMinute(
            $this->commonData()->getNowDateTime()->Format('Y-m-d H:i'),
            Configure::read('Setting.smartLock.truncateMinute')
        );

        $reservations = $this->find('searchList', [
            'inputs' => [
                'reservation_smartlock_status' => [
                    (string)ReservationSmartLock::DISPLAY_STATUS_UNLINKED,
                ],
                'usage_timestamp' => [
                    'from' => (string)$nowDateTime,
                ],
                'reservation_status_id' => $reservationStatusIds,
            ],
        ]);

        if ($reservations->count() >= 1) {
            return true;
        }

        return false;
    }

    /**
     * スマートロック未連携予約取得用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSmartLockUnlinkedReservation(Query $query, array $options)
    {
        $query->select(['reservations.id']);

        // eventsテーブル、event_smart_locksテーブル、reservation_smart_locksテーブルのjoin
        $query->join([
            'ReservationSmartLocks' => [
                'table' => 'reservation_smart_locks',
                'type' => 'LEFT',
                'conditions' => 'Reservations.id = ReservationSmartLocks.reservation_id',
            ],
            'Events' => [
                'table' => 'events',
                'type' => 'INNER',
                'conditions' => 'Reservations.event_id = Events.id',
            ],
            'EventSmartLocks' => [
                'table' => 'event_smart_locks',
                'type' => 'LEFT',
                'conditions' => 'Events.id = EventSmartLocks.event_id',
            ],
        ]);

        return $query;
    }

    /**
     * ファイルアップロード項目更新でのファイル削除
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param array $oldAdditions 変更前の追加項目登録値
     * @return void
     */
    public function deleteFileFormUpload(EntityInterface $entity, array $oldAdditions)
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $uploadItems = [];
        foreach ($formItemsTable->getFormItems(FormGroup::FORM_TYPE_RESERVATION) as $index => $formItem) {
            if ($formItem->getInputTypeItem() instanceof FileUploadInterface) {
                $uploadItems[$index] = $formItem;
            }
        }

        $oldFiles = [];
        foreach (array_keys($uploadItems) as $itemId) {
            $value = Hash::get($oldAdditions, 'item_' . $itemId);
            if (isset($value)) {
                $oldFiles[$itemId] = $value;
            }
        }

        $newFiles = [];
        foreach ((array)$entity->get('reservation_additions') as $addition) {
            if (isset($uploadItems[$addition->get('form_item_id')])) {
                $newFiles[$addition->form_item_id] = $addition->get('value');
            }
        }

        $deletingFiles = [];
        foreach ($oldFiles as $formItemId => $file) {
            $oldExtension = substr($file, strrpos($file, '.') + 1);
            $fileName = '';
            if (isset($newFiles[$formItemId]) && $file !== $newFiles[$formItemId]) {
                if (isset($newFiles[$formItemId])) {
                    $newFile = $newFiles[$formItemId];
                    $newExtension = substr($newFile, strrpos($newFile, '.') + 1);
                    if ($oldExtension === $newExtension) {
                        continue;
                    }
                }
                $fileName = Configure::read('Setting.file.uploadFileName') . $oldExtension;
            }
            if (!isset($newFiles[$formItemId])) {
                $fileName = Configure::read('Setting.file.uploadFileName') . $oldExtension;
            }
            if ($fileName) {
                $directory = implode(DS, $this->getFormUploadDirectory($entity->get('id'), (string)$formItemId));
                $deletingFiles[] = $directory . DS . $fileName;
            }
        }

        foreach ($deletingFiles as $file) {
            FileUtility::deleteFile(UPLOAD_RESERVATION_FILE . DS . $file);
        }
    }
}
