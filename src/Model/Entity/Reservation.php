<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Exception\SmartLockException;
use App\Model\AppEntity;
use App\Model\Entity\Traits\AdditionValuesTrait;
use App\Model\EventCalendar\EventTimetable;
use App\Utility\DateTimeUtility;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Hash;

/**
 * Reservation Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $event_id
 * @property \Cake\I18n\FrozenTime $usage_timestamp_from
 * @property \Cake\I18n\FrozenTime $usage_timestamp_to
 * @property int $usage_time
 * @property int $usage_day
 * @property int $number
 * @property int $charge
 * @property int $reservation_status_id
 * @property int|null $payment_method_id
 * @property int|null $payment_status_id
 * @property string $qr_code
 * @property \Cake\I18n\FrozenTime|null $cancel_timestamp
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Event $event
 * @property \App\Model\Entity\ReservationStatus $reservation_status
 * @property \App\Model\Entity\PaymentMethod $payment_method
 * @property \App\Model\Entity\PaymentStatus $payment_status
 * @property \App\Model\Entity\AutoReplyMailHistory[] $auto_reply_mail_histories
 * @property \App\Model\Entity\ReservationAddition[] $reservation_additions
 * @property \App\Model\Entity\ReservationEventPlan[] $reservation_event_plans
 * @property \App\Model\Entity\ReservationGuestCode[] $reservation_guest_codes
 * @property \App\Model\Entity\ReservationOption[] $reservation_options
 * @property \App\Model\Entity\ReservationPayment[] $reservation_payments
 * @property \App\Model\Entity\ReservationVideoMeeting[] $reservation_video_meetings
 * @property \App\Model\Entity\ReservationSmartLock $reservation_smart_lock
 */
class Reservation extends AppEntity
{
    use AdditionValuesTrait;

    public const CALCULATE_CHARGE_OFF = 0;
    public const CALCULATE_CHARGE_ON = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'user_id' => true,
        'event_id' => true,
        'usage_timestamp_from' => true,
        'usage_timestamp_to' => true,
        'usage_time' => true,
        'usage_day' => true,
        'number' => true,
        'charge' => true,
        'reservation_status_id' => true,
        'reception_status_id' => true,
        'payment_method_id' => true,
        'payment_status_id' => true,
        'cancel_timestamp' => false,
        'created' => false,
        'modified' => false,
        'user' => false,
        'event' => false,
        'reservation_status' => false,
        'payment_method' => false,
        'payment_status' => false,
        'auto_reply_mail_histories' => false,
        'reservation_additions' => false,
        'reservation_event_plans' => false,
        'reservation_guest_codes' => false,
        'reservation_options' => false,
        'reservation_payments' => false,
        'reservation_video_meetings' => false,
        'addition_values' => true,
        'option_values' => true,
        'plan_values' => true,
        'calculate_charge' => true,
        'user_deleted_flg' => false,
        'qr_code' => true,
        'reservation_smart_lock' => false,
        'payment_tracking_id' => true,
        'after_redirect_payment_flg' => false,
    ];

    /**
     * @inheritDoc
     */
    protected $_virtual = [
        'addition_values',
        'option_values',
        'plan_values',
        'calculate_charge',
        'user_deleted_flg',
        'payment_tracking_id',
        'after_redirect_payment_flg',
    ];

    /**
     * @var \App\Model\Entity\User|null
     */
    protected $userEntity = null;

    /**
     * @var \App\Model\Entity\Event|null
     */
    protected $eventEntity = null;

    /**
     * @var \App\Model\EventCalendar\EventTimetable|null
     */
    protected $timetable = null;

    /**
     * @var \App\Model\EventCalendar\EventTimetable|null
     */
    protected $originalTimetable = null;

    /**
     * @var array|null
     */
    protected $continuousData = null;

    /**
     * @var array|null
     */
    protected $reservedOptions = null;

    /**
     * @var \App\Model\Entity\ReservationSmartLock|null
     */
    protected $reservationSmartLockEntity = null;

    /**
     * @var array|null
     */
    protected $chargeBreakdown;

    /**
     * @var \App\Model\Entity\ReservationPayment|null
     */
    protected $reservationPayment;

    /**
     * @var bool|null
     */
    protected $hasReservationPayment;

    /**
     * 会員を取得
     *
     * @return \App\Model\Entity\User|null
     */
    public function getUserEntity()
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        if (!isset($this->userEntity) && $this->has('user_id')) {
            try {
                /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                $this->userEntity = $usersTable->get($this->get('user_id'), [
                    'finder' => 'reservation',
                ]);
            } catch (RecordNotFoundException $e) {
                return null;
            }
        }

        return $this->userEntity;
    }

    /**
     * 会員を設定
     *
     * @param \App\Model\Entity\User $user 会員
     * @return void
     */
    public function setUserEntity(User $user)
    {
        $this->userEntity = $user;
        if ($user->isNew()) {
            $this->set('user', $user);
        }
    }

    /**
     * 予約枠を取得
     *
     * @return \App\Model\Entity\Event|null
     */
    public function getEventEntity()
    {
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');

        if (!isset($this->eventEntity) && $this->has('event_id')) {
            try {
                /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                $this->eventEntity = $eventsTable->get($this->get('event_id'), [
                    'finder' => 'reservation',
                ]);
            } catch (RecordNotFoundException $e) {
                return null;
            }
        }

        return $this->eventEntity;
    }

    /**
     * 予約枠を設定
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @return void
     */
    public function setEventEntity(Event $event)
    {
        $this->eventEntity = $event;
    }

    /**
     * 料金を計算
     *
     * @return int 料金
     */
    public function calculateCharge()
    {
        return Hash::get($this->getChargeBreakdown(), 'charge');
    }

    /**
     * 料金内訳を取得
     *
     * @return array 料金
     */
    public function getChargeBreakdown()
    {
        if (!isset($this->chargeBreakdown)) {
            $event = $this->getEventEntity();
            if (!isset($event)) {
                throw new CakeException();
            }

            $breakdown = [];

            $usageTime = $this->get('usage_time');
            $usageDay = $this->get('usage_day');
            $reservationNumber = $this->get('number');

            // 予約枠の料金
            $eventTotalCharge = 0;
            $timePlan = $event->get('time_plan');
            if (((string)$timePlan) === ((string)Event::PLAN_SINGLE)) {
                $unitNumber = (int)ceil($usageTime / $event->get('event_unit_time'));
                $eventDayCharge = $event->get('charge') * $unitNumber * $usageDay;
                $eventCharge = $eventDayCharge * $reservationNumber;
                $eventTotalCharge += $eventCharge;

                if ($eventCharge > 0) {
                    $breakdown[] = [
                        'name' => $event->get('name'),
                        'charges' => [
                            [
                                'name' => null,
                                'charge' => $eventDayCharge,
                            ],
                        ],
                        'number' => $reservationNumber,
                        'unit' => $event->get('stock_unit'),
                        'totalCharge' => $eventCharge,
                    ];
                }
            } elseif (((string)$timePlan) === ((string)Event::PLAN_MULTIPLE)) {
                $eventPlanIds = [];
                if (is_array($this->get('reservation_event_plans'))) {
                    foreach ($this->get('reservation_event_plans') as $reservationEventPlan) {
                        $eventPlanIds[$reservationEventPlan->get('event_plan_id')] = true;
                    }
                }

                $planBreakdown = null;
                foreach ($event->get('event_plans') as $eventPlan) {
                    if (isset($eventPlanIds[$eventPlan->get('id')])) {
                        $planUnitCharge = $eventPlan->get('charge');
                        $planCharge = $planUnitCharge * $reservationNumber;
                        $eventTotalCharge += $planCharge;

                        if ($planCharge > 0) {
                            if (!isset($planBreakdown)) {
                                $planBreakdown = [
                                    'name' => $event->get('name'),
                                    'charges' => [],
                                    'number' => $reservationNumber,
                                    'unit' => $event->get('stock_unit'),
                                    'totalCharge' => 0,
                                ];
                            }

                            $planBreakdown['charges'][] = [
                                'name' => $eventPlan->get('name'),
                                'charge' => $planUnitCharge,
                            ];
                            $planBreakdown['totalCharge'] += $planCharge;
                        }
                    }
                }

                if (isset($planBreakdown)) {
                    $breakdown[] = $planBreakdown;
                }
            }

            // オプション予約の料金
            $optionTotalCharge = 0;
            $optionNumbers = [];
            foreach ((array)$this->get('reservation_options') as $reservationOption) {
                $optionNumbers[$reservationOption->get('option_id')] = $reservationOption->get('number');
            }
            if (count($optionNumbers) > 0) {
                $optionsTable = $this->getTableLocator()->get('Options');
                $options = $optionsTable->find('charge', [
                    'inputs' => [
                        'id' => array_keys($optionNumbers),
                    ],
                ]);
                foreach ($options as $option) {
                    $optionDayCharge = $option->get('charge') * $usageDay;
                    $optionCharge = $optionDayCharge * $optionNumbers[$option->get('id')];
                    $optionTotalCharge += $optionCharge;

                    if ($optionCharge > 0) {
                        $breakdown[] = [
                            'name' => $option->get('name'),
                            'charges' => [
                                [
                                    'name' => null,
                                    'charge' => $optionDayCharge,
                                ],
                            ],
                            'number' => $optionNumbers[$option->get('id')],
                            'unit' => $option->get('stock_unit'),
                            'totalCharge' => $optionCharge,
                        ];
                    }
                }
            }

            $this->chargeBreakdown = [
                'breakdown' => $breakdown,
                'charge' => (int)($eventTotalCharge + $optionTotalCharge),
            ];
        }

        return $this->chargeBreakdown;
    }

    /**
     * タイムテーブルを取得
     *
     * @param bool $noCache オブジェクトを保持しない
     * @return \App\Model\EventCalendar\EventTimetable
     */
    public function getTimetable($noCache = false)
    {
        if (!isset($this->timetable)) {
            $isAdmin = false;
            if ($this->commonData()->existsAdminLoginData()) {
                $isAdmin = true;
            }

            $event = $this->getEventEntity();
            if (!isset($event)) {
                throw new CakeException();
            }

            $eventTimetable = new EventTimetable(
                $event,
                $this->get('usage_timestamp_from'),
                $this->get('usage_timestamp_to'),
                $isAdmin
            );
            if ($noCache) {
                return $eventTimetable;
            }

            $this->timetable = $eventTimetable;
        }

        return $this->timetable;
    }

    /**
     * 変更前のタイムテーブルを取得
     *
     * @return \App\Model\EventCalendar\EventTimetable
     */
    public function getOriginalTimetable()
    {
        if (!isset($this->originalTimetable)) {
            /** @var \App\Model\Table\EventsTable $eventsTable */
            $eventsTable = $this->getTableLocator()->get('Events');

            $isAdmin = false;
            if ($this->commonData()->existsAdminLoginData()) {
                $isAdmin = true;
            }

            $eventTimetable = null;
            if (
                (string)$this->getOriginal('event_id') === (string)$this->get('event_id')
                && ((string)$this->getOriginal('usage_timestamp_from')) === (string)$this->get('usage_timestamp_from')
                && ((string)$this->getOriginal('usage_timestamp_to')) === (string)$this->get('usage_timestamp_to')
            ) {
                $eventTimetable = $this->getTimetable();
            } else {
                $event = null;
                if ((string)$this->getOriginal('event_id') === (string)$this->get('event_id')) {
                    $event = $this->getEventEntity();
                    if (!isset($event)) {
                        throw new CakeException();
                    }
                } else {
                    try {
                        /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                        $event = $eventsTable->get($this->getOriginal('event_id'), [
                            'finder' => 'reservation',
                        ]);
                    } catch (RecordNotFoundException $e) {
                        throw new CakeException();
                    }
                }

                $eventTimetable = new EventTimetable(
                    $event,
                    $this->getOriginal('usage_timestamp_from'),
                    $this->getOriginal('usage_timestamp_to'),
                    $isAdmin
                );
            }

            $this->originalTimetable = $eventTimetable;
        }

        return $this->originalTimetable;
    }

    /**
     * 連続予約を取得
     *
     * @return array|null
     */
    public function getContinuousData()
    {
        return $this->continuousData;
    }

    /**
     * 連続予約を設定
     *
     * @param array $continuousData 連続予約
     * @return void
     */
    public function setContinuousData(array $continuousData)
    {
        $this->continuousData = $continuousData;
    }

    /**
     * 予約情報からプラン名を取得
     *
     * @return array
     */
    public function getReservationPlans()
    {
        $events = $this->getEventEntity();
        if (is_null($events)) {
            return [];
        }

        $reservePlans = [];
        if ((string)$events->get('time_plan') === ((string)Event::PLAN_MULTIPLE)) {
            $eventPlanIds = $this->get('reservation_event_plans');
            $eventPlanIds = Hash::combine($eventPlanIds, '{*}.event_plan_id', '{*}.event_plan_id');
            foreach ($events->get('event_plans') as $eventPlan) {
                if (isset($eventPlanIds[$eventPlan->get('id')])) {
                    $reservePlans[] = $eventPlan->get('name');
                }
            }
        }

        return $reservePlans;
    }

    /**
     * ステータスタイプを取得
     *
     * @return int ステータス
     */
    public function getStatus()
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        $statusType = $reservationStatusesTable->getReservationStatusType($this->get('reservation_status_id'));

        return $statusType;
    }

    /**
     * 在庫確保の有無をチェック
     *
     * @return bool 在庫確保の有無
     */
    public function isKeepStockStatus()
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $keepStockFlg = $reservationStatusesTable->getKeepStockFlg($this->get('reservation_status_id'));
        if (((string)$keepStockFlg) !== ((string)ReservationStatus::KEEP_STOCK_FLG_ON)) {
            return false;
        }

        return true;
    }

    /**
     * 予約の開始日時を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getUsageTimestampFrom()
    {
        $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($this->get('usage_timestamp_from'));
        if (!isset($usageTimestampFrom)) {
            throw new CakeException();
        }

        return $usageTimestampFrom;
    }

    /**
     * 予約の終了日時を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getUsageTimestampTo()
    {
        $usageTimestampTo = DateTimeUtility::convertToDateTimeObject($this->get('usage_timestamp_to'));
        if (!isset($usageTimestampTo)) {
            throw new CakeException();
        }

        return $usageTimestampTo;
    }

    /**
     * 予約の開始日時が過ぎているか判定
     *
     * @return bool
     */
    public function isReservationStarted()
    {
        if ($this->getUsageTimestampFrom() > $this->commonData()->getNowDateTime()) {
            return false;
        }

        return true;
    }

    /**
     * 予約の終了日時が過ぎているか判定
     *
     * @return bool
     */
    public function isReservationEnded()
    {
        if ($this->getUsageTimestampTo() > $this->commonData()->getNowDateTime()) {
            return false;
        }

        return true;
    }

    /**
     * 予約済みのオプションを取得
     *
     * @return array
     */
    public function getReservedOptions()
    {
        if (!isset($this->reservedOptions)) {
            /** @var \App\Model\Table\ReservationOptionsTable $reservationOptionsTable */
            $reservationOptionsTable = $this->getTableLocator()->get('ReservationOptions');

            $this->reservedOptions = $reservationOptionsTable->find('reserved', [
                'inputs' => [
                    'reservation_id' => $this->get('id'),
                ],
            ])->toArray();
        }

        return $this->reservedOptions;
    }

    /**
     * 表示の可否を判定
     *
     * @param bool $adminFlg 管理側フラグ
     * @return bool
     */
    public function canView($adminFlg = false)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');

        if ($this->isUserDedeted()) {
            return false;
        }
        if (!$adminFlg) {
            if ($this->commonData()->existsUserLoginData()) {
                if ((string)$this->get('user_id') !== ((string)$this->commonData()->getUserLoginData()->get('id'))) {
                    return false;
                }
            }
        } else {
            // 管理側では管理者の担当カテゴリに紐づかない枠の場合はエラー
            /** @var \App\Model\Entity\Event $event */
            $event = $this->getEventEntity();
            if (!$labelsTable->isAdminUsableLabel($event->get('label_id'))) {
                return false;
            }
        }

        return true;
    }

    /**
     * 編集の可否を判定
     *
     * @param bool|null $isAdmin 管理側フラグ
     * @return bool
     */
    public function canEdit(?bool $isAdmin = null)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
        $paymentStatusesTable = $this->getTableLocator()->get('PaymentStatuses');
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        if (!isset($isAdmin)) {
            $isAdmin = $this->commonData()->existsAdminLoginData();
        }

        if (!$this->canView($isAdmin)) {
            return false;
        }

        if (!$isAdmin) {
            if (
                !$reservationStatusesTable->canUserEditOrCancel($this->get('reservation_status_id'))
                || !$paymentStatusesTable->canUserEdit($this->get('payment_method_id'), $this->get('payment_status_id'))
            ) {
                return false;
            }

            $paymentSetting = $paymentSettingsTable->getData();
            if (
                $this->hasReservationPayment()
                && (
                    isset($paymentSetting) && $paymentSetting->isPaymentServiceSb()
                    || $this->getReservationPayment()->isReservationPaymentServiceSb()
                )
            ) {
                return false;
            }

            if ($reservationStatusesTable->canUserEditOrCancelNoDeadline($this->get('reservation_status_id'))) {
                if ($this->isReservationStarted()) {
                    return false;
                }
            } else {
                $eventTimetable = $this->getTimetable(true);
                $eventUnit = $eventTimetable->getEvent()->isEditingDeadlineCriterionTo() ?
                    $eventTimetable->getLastUnit() : $eventTimetable->getFirstUnit();
                if (!isset($eventUnit)) {
                    return false;
                }
                if (!$eventUnit->isWithinEditingDeadline()) {
                    return false;
                }
            }

            if (!$this->commonData()->existsUserLoginData()) {
                if (
                    $siteSettingsTable->getData()->get('reservation_edit_not_user_flg')
                    !== SiteSetting::COMMON_USE_FLG_ON
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * キャンセルの可否を判定
     *
     * @param bool|null $isAdmin 管理側フラグ
     * @return bool
     */
    public function canCancel(?bool $isAdmin = null)
    {
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        if (!isset($isAdmin)) {
            $isAdmin = $this->commonData()->existsAdminLoginData();
        }

        if (!$this->canView($isAdmin)) {
            return false;
        }

        $statusType = $this->getStatus();
        if (
            ((string)$statusType) === ((string)ReservationStatus::STATUS_TYPE_CANCEL)
            || ((string)$statusType) === ((string)ReservationStatus::STATUS_TYPE_ABSENCE)
        ) {
            return false;
        }

        if (!$isAdmin) {
            if (!$reservationStatusesTable->canUserEditOrCancel($this->get('reservation_status_id'))) {
                return false;
            }

            if (
                $this->hasReservationPayment()
                && !$this->getReservationPayment()->isPaid()
                && $paymentMethodsTable->getPaymentMethodType(
                    (int)$this->getReservationPayment()->get('payment_method_id')
                ) !== PaymentMethod::TYPE_PAYPAY
            ) {
                return false;
            }

            if ($reservationStatusesTable->canUserEditOrCancelNoDeadline($this->get('reservation_status_id'))) {
                if ($this->isReservationStarted()) {
                    return false;
                }
            } else {
                $eventTimetable = $this->getTimetable(true);
                $eventUnit = $eventTimetable->getEvent()->isCancellationDeadlineCriterionTo() ?
                    $eventTimetable->getLastUnit() : $eventTimetable->getFirstUnit();
                if (!isset($eventUnit)) {
                    return false;
                }
                if (!$eventUnit->isWithinCancellationDeadline()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * キャンセルステータスを設定
     *
     * @return void
     */
    public function cancel()
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $reservationStatus = $reservationStatusesTable->getCancelData();
        $this->set('reservation_status_id', $reservationStatus['id']);
    }

    /**
     * キャンセル済み判定
     *
     * @return bool
     */
    public function isCanceled()
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $cancelStatus = $reservationStatusesTable->getCancelData();

        return (string)$this->get('reservation_status_id') === (string)$cancelStatus['id'];
    }

    /**
     * 会員の削除済み判定
     *
     * @return bool
     */
    public function isUserDedeted()
    {
        if ((string)$this->get('user_deleted_flg') !== (string)Configure::readOrFail('Master.common.flg.on')) {
            return false;
        }

        return true;
    }

    /**
     * 予約数の変更チェック
     *
     * @return bool
     */
    public function isNumberDirty()
    {
        if ((string)$this->get('number') !== (string)$this->getOriginal('number')) {
            return true;
        }

        $newOptions = [];
        foreach ((array)$this->get('reservation_options') as $reservationOption) {
            if ($reservationOption->get('number') > 0) {
                $newOptions[$reservationOption->get('option_id')] = (int)$reservationOption->get('number');
            }
        }
        ksort($newOptions, SORT_NUMERIC);

        $originalOptions = [];
        foreach ((array)$this->getReservedOptions() as $reservationOption) {
            if ($reservationOption->get('number') > 0) {
                $originalOptions[$reservationOption->get('option_id')] = (int)$reservationOption->get('number');
            }
        }
        ksort($originalOptions, SORT_NUMERIC);

        if ($newOptions !== $originalOptions) {
            return true;
        }

        return false;
    }

    /**
     * 追加情報取得
     *
     * @param array|string|null $data データ
     * @return array
     */
    protected function _getAdditionValues($data)
    {
        if (is_array($data)) {
            return $data;
        }
        $this->_fields['addition_values'] = $this->createAdditionValues('ReservationAdditions', $data);

        return $this->_fields['addition_values'];
    }

    /**
     * 追加情報設定
     *
     * @param array|null $data データ
     * @return array|null
     */
    protected function _setAdditionValues($data)
    {
        // 入力値にキーとして存在しないデータをマージ
        $data = (array)$data + (array)$this->createAdditionValues('ReservationAdditions');

        $this->set('reservation_additions', $this->createAdditionEntity('ReservationAdditions', $data));

        return $data;
    }

    /**
     * オプション情報取得
     *
     * @param array|null $data データ
     * @return array
     */
    protected function _getOptionValues($data)
    {
        if (is_array($data)) {
            return $data;
        }

        $optionValues = [];
        if ($this->has('reservation_options')) {
            /** @var \App\Model\Table\FormItemsTable $formItemsTable */
            $formItemsTable = $this->getTableLocator()->get('FormItems');

            foreach ($this->get('reservation_options') as $reservationOption) {
                $formItemId = $reservationOption->get('form_item_id');
                $formItemKey = 'item_' . $formItemId;
                $optionId = $reservationOption->get('option_id');
                $optionKey = 'value_' . $optionId;
                $optionValues[$formItemKey]['reservation_options'][$optionKey] = [
                    'number' => $reservationOption->get('number'),
                ];

                $formItem = $formItemsTable->getFormItem($formItemId);
                if (isset($formItem) && !$formItem->get('form_item_option_group')->isMultipleType()) {
                    $optionValues[$formItemKey]['option_id'] = $optionId;
                }
            }
        }
        $this->_fields['option_values'] = $optionValues;

        return $this->_fields['option_values'];
    }

    /**
     * オプション情報設定
     *
     * @param array|null $data データ
     * @return array|null
     */
    protected function _setOptionValues($data)
    {
        /** @var \App\Model\Table\ReservationOptionsTable $reservationOptionsTable */
        $reservationOptionsTable = $this->getTableLocator()->get('ReservationOptions');
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');
        /** @var \App\Model\Table\OptionsTable $optionsTable */
        $optionsTable = $this->getTableLocator()->get('Options');

        $oldReservationOptions = [];
        foreach ((array)$this->get('reservation_options') as $reservationOption) {
            $formItemId = $reservationOption->get('form_item_id');
            $optionId = $reservationOption->get('option_id');
            $oldReservationOptions[$formItemId][$optionId] = $reservationOption;
        }

        // 入力値にキーとして存在しないデータをマージ
        $data = (array)$data + (array)$this->get('option_values');

        $entities = [];
        foreach ($data as $formItemKey => $optionValue) {
            $formItemId = preg_replace('/^item_/', '', $formItemKey);
            if (
                isset($optionValue['reservation_options']) && is_array($optionValue['reservation_options'])
                && $formItemsTable->validatePrimaryKey($formItemId)
            ) {
                foreach ($optionValue['reservation_options'] as $optionKey => $reservationOption) {
                    $optionId = preg_replace('/^value_/', '', $optionKey);
                    if (
                        isset($reservationOption['number']) && $reservationOption['number'] > 0
                        && $optionsTable->validatePrimaryKey($optionId)
                    ) {
                        $optionData = [
                            'option_id' => $optionId,
                            'form_item_id' => $formItemId,
                            'number' => $optionValue['reservation_options'][$optionKey]['number'],
                        ];
                        if (!isset($oldReservationOptions[$formItemId][$optionId])) {
                            $entities[] = $reservationOptionsTable->newEntity($optionData, ['validate' => false]);
                        } else {
                            $entities[] = $reservationOptionsTable->patchEntity(
                                $oldReservationOptions[$formItemId][$optionId],
                                $optionData,
                                ['validate' => false]
                            );
                        }
                    }
                }
            }
        }
        $this->set('reservation_options', $entities);

        return $data;
    }

    /**
     * プラン情報取得
     *
     * @param array|null $data データ
     * @return array
     */
    protected function _getPlanValues($data)
    {
        if (is_array($data)) {
            return $data;
        }

        $planValues = [];
        if ($this->has('reservation_event_plans')) {
            $event = $this->getEventEntity();
            if (
                isset($event)
                && (string)$event->get('multiple_time_plan_type') === (string)Event::MULTIPLE_TIME_PLAN_TYPE_SINGLE
            ) {
                foreach ($this->get('reservation_event_plans') as $reservationEventPlan) {
                    $planValues['single'] = $reservationEventPlan->get('event_plan_id');
                    break;
                }
            } else {
                foreach ($this->get('reservation_event_plans') as $reservationEventPlan) {
                    $planValues[] = $reservationEventPlan->get('event_plan_id');
                }
            }
        }
        $this->_fields['plan_values'] = $planValues;

        return $this->_fields['plan_values'];
    }

    /**
     * プラン情報設定
     *
     * @param array|null $data データ
     * @return array|null
     */
    protected function _setPlanValues($data)
    {
        $reservationEventPlansTable = $this->getTableLocator()->get('ReservationEventPlans');

        $oldReservationEventPlans = [];
        foreach ((array)$this->get('reservation_event_plans') as $reservationEventPlan) {
            $oldReservationEventPlans[$reservationEventPlan->get('event_plan_id')] = $reservationEventPlan;
        }

        $entities = [];
        if (is_array($data)) {
            foreach ($data as $eventPlanId) {
                if (is_scalar($eventPlanId) && $eventPlanId !== '') {
                    $planData = [
                        'event_plan_id' => $eventPlanId,
                    ];
                    if (!isset($oldReservationEventPlans[$eventPlanId])) {
                        $entities[] = $reservationEventPlansTable->newEntity($planData, ['validate' => false]);
                    } else {
                        $entities[] = $reservationEventPlansTable->patchEntity(
                            $oldReservationEventPlans[$eventPlanId],
                            $planData,
                            ['validate' => false]
                        );
                    }
                }
            }
        }
        $this->set('reservation_event_plans', $entities);

        return $data;
    }

    /**
     * 予約枠のビデオ会議主催者を取得
     *
     * @return \App\Model\Entity\Organizer|null
     */
    public function getEventOrganizer()
    {
        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->getTableLocator()->get('Organizers');

        $event = $this->getEventEntity();
        if (!isset($event)) {
            throw new CakeException();
        }

        if ((string)$event->get('organizer_id') === '') {
            return null;
        }

        return $organizersTable->getOrganizerForApi($event->get('organizer_id'));
    }

    /**
     * 予約済みのビデオ会議情報を取得
     *
     * @return \App\Model\Entity\ReservationVideoMeeting|null
     */
    public function getReservedVideoMeeting()
    {
        $reservationVideoMeetings = $this->get('reservation_video_meetings');
        if (empty($reservationVideoMeetings)) {
            return null;
        }
        $reservationVideoMeeting = reset($reservationVideoMeetings);

        return $reservationVideoMeeting;
    }

    /**
     * ビデオ会議連携の登録可否
     *
     * @return bool
     */
    public function canAddVideoMeeting()
    {
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->getTableLocator()->get('ReservationVideoMeetings');

        $checkEnded = false;
        if (
            !is_null($this->getReservedVideoMeeting())
            || !$reservationVideoMeetingsTable->shouldProcessOnReserve($this, $checkEnded)
        ) {
            return false;
        }

        return true;
    }

    /**
     * ビデオ会議連携の削除可否
     *
     * @return bool
     */
    public function canDeleteVideoMeeting()
    {
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->getTableLocator()->get('ReservationVideoMeetings');

        $checkEnded = false;
        if (!$reservationVideoMeetingsTable->shouldProcessOnDelete($this, $checkEnded)) {
            return false;
        }

        return true;
    }

    /**
     * 受付ステータスタイプを取得
     *
     * @return int 受付ステータス
     */
    public function getReceptionStatus()
    {
        /** @var \App\Model\Table\ReceptionStatusesTable $receptionStatusesTable */
        $receptionStatusesTable = $this->getTableLocator()->get('ReceptionStatuses');
        $statusType = $receptionStatusesTable->getReceptionStatusType($this->get('reception_status_id'));

        return $statusType;
    }

    /**
     * スマートロック連携に必要な情報をセットする
     *
     * reservation_smart_lock, event.event_smart_lock
     *
     * @return void
     */
    public function setSmartLockInfo()
    {
        if (!$this->get('reservation_smart_lock')) {
            /** @var \App\Model\Table\ReservationSmartLocksTable $reservationSmartLocksTable */
            $reservationSmartLocksTable = $this->getTableLocator()->get('ReservationSmartLocks');
            /** @var \App\Model\Entity\ReservationSmartLock $reservationSmartLock */
            $reservationSmartLock = $reservationSmartLocksTable->find()
                ->where(['reservation_id' => $this->id])
                ->first();
            $this->reservation_smart_lock = $reservationSmartLock;
        }

        if (!$this->get('event') || !$this->event->get('event_smart_lock')) {
            /** @var \App\Model\Table\EventsTable $eventsTable */
            $eventsTable = $this->getTableLocator()->get('Events');
            /** @var \App\Model\Entity\Event $event */
            $event = $eventsTable->find()
                ->select([
                    'id',
                    'name',
                    'label_id',
                ])
                ->contain([
                    'EventSmartLocks' => [
                        'fields' => [
                            'id',
                            'event_id',
                            'smart_lock_device_key',
                            'smart_lock_key_url_flg',
                        ],
                    ],
                ])
                ->where(['Events.id' => $this->event_id])
                ->first();
            $this->event = $event;
        }
    }

    /**
     * 予約スマートロックを取得
     *
     * @return \App\Model\Entity\ReservationSmartLock|null
     */
    public function getReservationSmartLockEntity()
    {
        /** @var \App\Model\Table\ReservationSmartLocksTable $reservationSmartLocksTable */
        $reservationSmartLocksTable = $this->getTableLocator()->get('ReservationSmartLocks');

        if (!isset($this->reservationSmartLockEntity) && $this->has('id')) {
            try {
                /** @var \App\Model\Entity\ReservationSmartLock $reservationSmartLockEntity */
                $reservationSmartLockEntity = $reservationSmartLocksTable->find()
                    ->where(['reservation_id' => $this->get('id')])
                    ->firstOrFail();
                $this->reservationSmartLockEntity = $reservationSmartLockEntity;
            } catch (RecordNotFoundException $e) {
                return null;
            }
        }

        return $this->reservationSmartLockEntity;
    }

    /**
     * QRコードを表示するか
     *
     * @return bool
     */
    public function displayQrCode()
    {
        $event = $this->getEventEntity();
        if (!($event instanceof Event)) {
            throw new CakeException();
        }

        return !empty($this->get('qr_code'))
            && $event->canDisplayQrCode()
            && (
                $this->commonData()->existsAdminLoginData()
                || !$this->hasReservationPayment()
                || $this->getReservationPayment()->isPaid()
            );
    }

    /**
     * PIN番号を表示するか
     *
     * @return bool
     */
    public function displayReservationSmartLockPin()
    {
        $smartLock = new SmartLockLinkage();

        return $smartLock->useRemoteLock()
            && $this->getReservationSmartLockEntity() !== null
            && $this->getReservationSmartLockEntity()->get('smart_lock_pin') !== null
            && $this->getReservationSmartLockEntity()->get('smart_lock_pin') !== ''
            && (
                $this->commonData()->existsAdminLoginData()
                || !$this->hasReservationPayment()
                || $this->getReservationPayment()->isPaid()
            );
    }

    /**
     * カギ情報URL（ユニバーサルアクセスキー）を表示するか
     *
     * @return bool
     */
    public function displayReservationUniversalAccessKey()
    {
        $smartLock = new SmartLockLinkage();

        return $smartLock->useRemoteLock()
            && $this->getReservationSmartLockEntity() !== null
            && $this->getReservationSmartLockEntity()->get('smart_lock_key_url') !== null
            && $this->getReservationSmartLockEntity()->get('smart_lock_key_url') !== ''
            && (
                $this->commonData()->existsAdminLoginData()
                || !$this->hasReservationPayment()
                || $this->getReservationPayment()->isPaid()
            );
    }

    /**
     * ロック解除URLを表示するか
     *
     * @return bool
     */
    public function displayReservationSmartLockKeyUrl()
    {
        $smartLock = new SmartLockLinkage();

        return $smartLock->useAkerun()
            && $this->getReservationSmartLockEntity() !== null
            && $this->getReservationSmartLockEntity()->get('smart_lock_key_url') !== null
            && $this->getReservationSmartLockEntity()->get('smart_lock_key_url') !== ''
            && (
                $this->commonData()->existsAdminLoginData()
                || !$this->hasReservationPayment()
                || $this->getReservationPayment()->isPaid()
            );
    }

    /**
     * 表示パターン設定で使用されていないオプションの値を削除する
     *
     * @return void
     */
    public function unsetUnusedOptions()
    {
        /** @var \App\Model\Table\FormPatternOptionsTable $formPatternOptionsTable */
        $formPatternOptionsTable = $this->getTableLocator()->get('FormPatternOptions');

        $reservationOptions = $this->get('reservation_options');
        /** @var \App\Model\Entity\Event $event */
        $event = $this->getEventEntity();

        foreach ((array)$reservationOptions as $index => $reservationOption) {
            // 使用されていない項目の値は使用しない
            if (!$this->isUsedFormItem($reservationOption->get('form_item_id'))) {
                unset($reservationOptions[$index]);
                continue;
            }
            // この項目に対して表示パターン設定でチェックがつけられていないオプションの値は使用しない
            if (
                !$formPatternOptionsTable->isCheckedOptionForEvent(
                    $event->get('id'),
                    (int)$reservationOption->get('form_item_id'),
                    (int)$reservationOption->get('option_id')
                )
            ) {
                unset($reservationOptions[$index]);
                continue;
            }
        }

        $this->set('reservation_options', $reservationOptions);
    }

    /**
     * 決済情報を取得
     *
     * @return \App\Model\Entity\ReservationPayment
     */
    public function getReservationPayment()
    {
        if (!isset($this->reservationPayment)) {
            /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
            $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

            $reservationPayment = $reservationPaymentsTable->find('edit', [
                'reservationId' => $this->get('id'),
            ])->first();
            if (!($reservationPayment instanceof ReservationPayment)) {
                throw new CakeException();
            }

            $this->reservationPayment = $reservationPayment;
        }

        return $this->reservationPayment;
    }

    /**
     * 決済情報の有無を判定
     *
     * @return bool
     */
    public function hasReservationPayment()
    {
        if (!isset($this->hasReservationPayment)) {
            /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
            $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

            $this->hasReservationPayment = $reservationPaymentsTable->find('edit', [
                'reservationId' => $this->get('id'),
            ])->count() > 0;
        }

        return $this->hasReservationPayment;
    }

    /**
     * スマートロック未連携の予約を取得
     *
     * @return \App\Model\Entity\Reservation|null
     */
    public function getSmartLockUnLinkReservation()
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        if ($this->has('id')) {
            try {
                // 予約ステータス
                $reservationStatusIds = array_merge(
                    $reservationStatusesTable->getReservationStatusIds(ReservationStatus::STATUS_TYPE_FIXED),
                    $reservationStatusesTable->getReservationStatusIds(ReservationStatus::STATUS_TYPE_VISIT),
                );

                // 利用日時To用に現在日時を取得
                $nowDateTime = $this->commonData()->getNowDateTime()->Format('Y-m-d H:i');

                /** @var \App\Model\Entity\Reservation $reservationSmartLockUnlinkData */
                $reservationSmartLockUnlinkData = $reservationsTable->find('searchList', [
                    'inputs' => [
                        'reservation_id' => $this->get('id'),
                        'reservation_smartlock_status' => [
                            (string)ReservationSmartLock::DISPLAY_STATUS_UNLINKED,
                        ],
                        'usage_timestamp' => [
                            'from' => (string)$nowDateTime,
                        ],
                        'reservation_status_id' => $reservationStatusIds,
                    ],
                ])
                    ->first();
            } catch (SmartLockException $e) {
                return null;
            }
        } else {
            $reservationSmartLockUnlinkData = null;
        }

        return $reservationSmartLockUnlinkData;
    }

    /**
     * スマートロック再連携可否
     *
     * @return bool
     */
    public function canSmartLockReLink()
    {
        /** @var \App\Model\Table\ReservationSmartLocksTable $reservationSmartLocksTable */
        $reservationSmartLocksTable = $this->getTableLocator()->get('ReservationSmartLocks');
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $statusType =
            $reservationStatusesTable->getReservationStatusType($this->get('reservation_status_id'));

        if (is_null($this->getSmartLockUnLinkReservation())) {
            return false;
        }

        return true;
    }
}
