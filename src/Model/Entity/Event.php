<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Model\EventCalendar\EventTimetable;
use App\Model\EventCalendar\EventUnit;
use App\Utility\ArrayUtility;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;

/**
 * Event Entity
 *
 * @property int $id
 * @property int|null $label_id
 * @property string $name
 * @property int|null $sort_no
 * @property int $type
 * @property \Cake\I18n\FrozenDate $date_from
 * @property \Cake\I18n\FrozenDate|null $date_to
 * @property \Cake\I18n\FrozenTime $time_from
 * @property \Cake\I18n\FrozenTime $time_to
 * @property int $event_unit_time
 * @property int $time_plan
 * @property int|null $multiple_time_plan_type
 * @property int|null $usage_unit_time
 * @property int|null $usage_time_from
 * @property int|null $usage_time_to
 * @property int|null $usage_unit_day
 * @property int|null $usage_day_from
 * @property int|null $usage_day_to
 * @property int $usage_time_notation
 * @property int|null $interval_time
 * @property int|null $interval_day
 * @property int|null $charge
 * @property int $stock
 * @property int $stock_range_from
 * @property int $stock_range_to
 * @property string|null $stock_unit
 * @property int $stock_display_type
 * @property int $public_flg
 * @property \Cake\I18n\FrozenTime|null $public_from
 * @property \Cake\I18n\FrozenTime|null $public_to
 * @property int|null $reception_period_number
 * @property \Cake\I18n\FrozenTime|null $reception_period_time
 * @property int $registration_deadline_type
 * @property int $registration_deadline_number
 * @property \Cake\I18n\FrozenTime|null $registration_deadline_time
 * @property int $editing_deadline_type
 * @property int $editing_deadline_number
 * @property \Cake\I18n\FrozenTime|null $editing_deadline_time
 * @property int $cancellation_deadline_type
 * @property int $cancellation_deadline_number
 * @property \Cake\I18n\FrozenTime|null $cancellation_deadline_time
 * @property int $reservation_status_id
 * @property int $waiting_cancellation_flg
 * @property int|null $reservation_limit_all
 * @property int|null $reservation_limit_future
 * @property int|null $reservation_limit_month
 * @property int|null $reservation_limit_day
 * @property int $duplication_check_flg
 * @property int $background_color_type
 * @property int|null $color_chip_id
 * @property string|null $background_color_replace_front
 * @property string|null $background_color_replace_admin
 * @property string|null $format_type_display
 * @property int $form_pattern_id
 * @property string|null $description
 * @property int|null $organizer_id
 * @property int|null $qr_code_flg
 * @property int $registration_deadline_criterion
 * @property int $editing_deadline_criterion
 * @property int $cancellation_deadline_criterion
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Label $label
 * @property \App\Model\Entity\ReservationStatus $reservation_status
 * @property \App\Model\Entity\ColorChip $color_chip
 * @property \App\Model\Entity\FormPattern $form_pattern
 * @property \App\Model\Entity\Organizer $organizer
 * @property \App\Model\Entity\EventHoliday[] $event_holidays
 * @property \App\Model\Entity\EventImage[] $event_images
 * @property \App\Model\Entity\EventPlan[] $event_plans
 * @property \App\Model\Entity\EventRemark[] $event_remarks
 * @property \App\Model\Entity\EventStockMark[] $event_stock_marks
 * @property \App\Model\Entity\EventStockSetting[] $event_stock_settings
 * @property \App\Model\Entity\EventTag[] $event_tags
 * @property \App\Model\Entity\EventWeek[] $event_weeks
 * @property \App\Model\Entity\EventSmartLock $event_smart_lock
 * @property \App\Model\Entity\Reservation[] $reservations
 * @property \App\Model\Entity\WaitingCancellation[] $waiting_cancellations
 */
class Event extends AppEntity
{
    /**
     * 共通フラグON
     */
    public const COMMON_FLG_ON = 1;

    /**
     * 共通フラグOFF
     */
    public const COMMON_FLG_OFF = 0;

    /**
     * 最小予約分数
     */
    public const USETIME_INTERVAL = 5;

    /**
     * カレンダー時間表示（1日）
     */
    public const CALENDAR_TYPE_TIME_1DAY = 1;

    /**
     * カレンダー時間表示（1週間）
     */
    public const CALENDAR_TYPE_TIME_1WEEK = 2;

    /**
     * カレンダー日にち表示（1日）
     */
    public const CALENDAR_TYPE_DAY_1DAY = 3;

    /**
     * カレンダー日にち表示（1週間）
     */
    public const CALENDAR_TYPE_DAY_1WEEK = 4;

    /**
     * カレンダー1ヶ月表示
     */
    public const CALENDAR_TYPE_MONTH = 5;

    /**
     * カレンダー時間割表示（1日）
     */
    public const CALENDAR_TYPE_SUBJECT_1DAY = 6;

    /**
     * カレンダー時間割表示（1週間）
     */
    public const CALENDAR_TYPE_SUBJECT_1WEEK = 7;

    /**
     * カレンダー一覧表示
     */
    public const CALENDAR_TYPE_LIST = 8;

    /**
     * タイプ：時間単位での予約
     */
    public const TYPE_TIME = 1;

    /**
     * タイプ：日にち単位での予約
     */
    public const TYPE_DAY = 2;

    /**
     * 公開フラグON
     */
    public const PUBLIC_FLG_ON = 1;

    /**
     * 公開フラグOFF
     */
    public const PUBLIC_FLG_OFF = 0;

    /**
     * 背景色デフォルト
     */
    public const BACKGROUND_COLOR_TYPE_DEFAULT = 1;

    /**
     * 背景色カラーコード
     */
    public const BACKGROUND_COLOR_TYPE_COLOR_CODE = 2;

    /**
     * 料金プラン：一律料金
     */
    public const PLAN_SINGLE = 1;

    /**
     * 料金プラン：複数プラン
     */
    public const PLAN_MULTIPLE = 2;

    /**
     * 締め切り設定：時間
     */
    public const DEADLINE_TYPE_TIME = 1;

    /**
     * 締め切り設定：日
     */
    public const DEADLINE_TYPE_DAY = 2;

    /**
     * 在庫表示設定：数字
     */
    public const STOCK_DISPLAY_TYPE_NUMBER = 1;

    /**
     * 在庫表示設定：アイコン
     */
    public const STOCK_DISPLAY_TYPE_ICON = 2;

    /**
     * アイコン設定：表示なし
     */
    public const SYMBOLIC_DISP_HIDE = 1;

    /**
     * アイコン設定：2重丸
     */
    public const SYMBOLIC_DISP_DOUBLECIRCLE = 2;

    /**
     * アイコン設定：〇
     */
    public const SYMBOLIC_DISP_CIRCLE = 3;

    /**
     * アイコン設定：△
     */
    public const SYMBOLIC_DISP_TRIANGLE = 4;

    /**
     * アイコン設定：×
     */
    public const SYMBOLIC_DISP_CROSS = 5;

    /**
     * アイコン設定：空
     */
    public const SYMBOLIC_DISP_EMPTY = 6;

    /**
     * アイコン設定：満
     */
    public const SYMBOLIC_DISP_FULL = 7;

    /**
     * アイコン設定：--
     */
    public const SYMBOLIC_DISP_NONE = 8;

    /**
     * 複数プラン設定：単一
     */
    public const MULTIPLE_TIME_PLAN_TYPE_SINGLE = 1;

    /**
     * 複数プラン設定：複数選択
     */
    public const MULTIPLE_TIME_PLAN_TYPE_MULTI = 2;

    /**
     * 曜日設定：月
     */
    public const WEEK_MONDAY = 1;

    /**
     * 曜日設定：火
     */
    public const WEEK_TUESDAY = 2;

    /**
     * 曜日設定：水
     */
    public const WEEK_WEDNESDAY = 3;

    /**
     * 曜日設定：木
     */
    public const WEEK_THURSDAY = 4;

    /**
     * 曜日設定：金
     */
    public const WEEK_FRIDAY = 5;

    /**
     * 曜日設定：土
     */
    public const WEEK_SATURDAY = 6;

    /**
     * 曜日設定：日
     */
    public const WEEK_SUNDAY = 7;

    /**
     * 曜日設定：祝日設定
     */
    public const WEEK_HOLIDAY = 8;

    /**
     * 履歴表示設定：利用時間で表示
     */
    public const USAGE_TIME_NOTATION_USE_TIME = 1;

    /**
     * 履歴表示設定：終了時間で表示
     */
    public const USAGE_TIME_NOTATION_END_TIME = 2;

    public const USAGE_TIME_TO_ADMIN = 1440;
    public const USAGE_UNIT_DAY_ADMIN = 1;
    public const USAGE_DAY_FROM_ADMIN = 1;
    public const USAGE_DAY_TO_ADMIN = 30;

    /**
     * QRコード：表示する
     */
    public const QR_CODE_FLG_ON = 1;

    /**
     * QRコード：表示しない
     */
    public const QR_CODE_FLG_OFF = 0;

    /**
     * 判定基準：開始時間
     */
    public const CRITERION_FROM = 1;

    /**
     * 判定基準：終了時間
     */
    public const CRITERION_TO = 2;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'label_id' => true,
        'name' => true,
        'sort_no' => true,
        'type' => true,
        'date_from' => true,
        'date_to' => true,
        'time_from' => true,
        'time_to' => true,
        'event_unit_time' => true,
        'time_plan' => true,
        'multiple_time_plan_type' => true,
        'usage_unit_time' => true,
        'usage_time_from' => true,
        'usage_time_to' => true,
        'usage_unit_day' => true,
        'usage_day_from' => true,
        'usage_day_to' => true,
        'usage_time_notation' => true,
        'interval_time' => true,
        'interval_day' => true,
        'charge' => true,
        'stock' => true,
        'stock_range_from' => true,
        'stock_range_to' => true,
        'stock_unit' => true,
        'stock_display_type' => true,
        'public_flg' => true,
        'public_from' => true,
        'public_to' => true,
        'reception_period_number' => true,
        'reception_period_time' => true,
        'registration_deadline_type' => true,
        'registration_deadline_number' => true,
        'registration_deadline_time' => true,
        'editing_deadline_type' => true,
        'editing_deadline_number' => true,
        'editing_deadline_time' => true,
        'cancellation_deadline_type' => true,
        'cancellation_deadline_number' => true,
        'cancellation_deadline_time' => true,
        'reservation_status_id' => true,
        'waiting_cancellation_flg' => true,
        'reservation_limit_all' => true,
        'reservation_limit_future' => true,
        'reservation_limit_month' => true,
        'reservation_limit_day' => true,
        'duplication_check_flg' => true,
        'background_color_type' => true,
        'color_chip_id' => true,
        'background_color_replace_front' => true,
        'background_color_replace_admin' => true,
        'format_type_display' => true,
        'form_pattern_id' => true,
        'description' => true,
        'organizer_id' => true,
        'qr_code_flg' => true,
        'registration_deadline_criterion' => true,
        'editing_deadline_criterion' => true,
        'cancellation_deadline_criterion' => true,
        'created' => false,
        'modified' => false,
        'label' => true,
        'reservation_status' => true,
        'color_chip' => true,
        'form_pattern' => true,
        'organizer' => false,
        'event_holidays' => true,
        'event_images' => true,
        'event_plans' => true,
        'event_remarks' => true,
        'event_stock_marks' => true,
        'event_stock_settings' => true,
        'event_tags' => true,
        'event_weeks' => true,
        'event_smart_lock' => true,
        'reservations' => false,
        'waiting_cancellations' => false,
    ];

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $dateTimeFrom = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $dateTimeTo = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $timeFrom = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $timeTo = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $receptionDateTime = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $registrationDeadline = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $editingDeadline = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $cancellationDeadline = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $firstReceptionDateTime = null;

    /**
     * @var array|null
     */
    protected $publicHolidays = null;

    /**
     * @var \App\Model\Entity\EventSmartLock|null
     */
    protected $eventSmartLockEntity = null;

    /**
     * 開始日時を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getDateTimeFrom()
    {
        if (!isset($this->dateTimeFrom)) {
            $dateFrom = DateTimeUtility::convertToDateObject($this->get('date_from'));
            if (!isset($dateFrom)) {
                throw new CakeException();
            }
            $dateTimeFrom = new FrozenTime($dateFrom->format('Y-m-d') . ' ' . $this->getTimeFrom()->format('H:i:s'));

            $this->dateTimeFrom = $dateTimeFrom;
        }

        return $this->dateTimeFrom;
    }

    /**
     * 終了日時を取得
     *
     * @return \Cake\I18n\FrozenTime|null
     */
    public function getDateTimeTo()
    {
        if (!isset($this->dateTimeTo)) {
            $dateTo = DateTimeUtility::convertToDateObject($this->get('date_to'));
            if (!isset($dateTo)) {
                return null;
            }
            $dateTimeTo = new FrozenTime($dateTo->format('Y-m-d') . ' ' . $this->getTimeTo()->format('H:i:s'));

            // 日またぎの考慮
            if ($this->getTimeFrom() >= $this->getTimeTo()) {
                $dateTimeTo = $dateTimeTo->addDays(1);
            }

            $this->dateTimeTo = $dateTimeTo;
        }

        return $this->dateTimeTo;
    }

    /**
     * 開始時間を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getTimeFrom()
    {
        if (!isset($this->timeFrom)) {
            $timeFrom = DateTimeUtility::convertToTimeObject($this->get('time_from'));
            if (!isset($timeFrom)) {
                throw new CakeException();
            }
            $timeFrom = new FrozenTime(
                $this->commonData()->getNowDateTime()->format('Y-m-d') . ' ' . $timeFrom->format('H:i:s')
            );

            $this->timeFrom = $timeFrom;
        }

        return $this->timeFrom;
    }

    /**
     * 終了時間を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getTimeTo()
    {
        if (!isset($this->timeTo)) {
            $timeTo = DateTimeUtility::convertToTimeObject($this->get('time_to'));
            if (!isset($timeTo)) {
                throw new CakeException();
            }
            $timeTo = new FrozenTime(
                $this->commonData()->getNowDateTime()->format('Y-m-d') . ' ' . $timeTo->format('H:i:s')
            );

            $this->timeTo = $timeTo;
        }

        return $this->timeTo;
    }

    /**
     * 枠の開始日時から1枠の終了日時を取得
     *
     * @param string|\DateTimeInterface $usageTimestampFrom 枠の開始日時
     * @return \Cake\I18n\FrozenTime
     */
    public function getUsageTimestampTo($usageTimestampFrom)
    {
        $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($usageTimestampFrom);
        if (!isset($usageTimestampFrom)) {
            throw new CakeException();
        }

        $usageTimestampTo = clone $usageTimestampFrom;
        $usageTimestampTo = $usageTimestampTo->addMinutes($this->get('event_unit_time'));

        return $usageTimestampTo;
    }

    /**
     * 枠の開始日時から最小利用時間での終了日時を取得
     *
     * @param string|\DateTimeInterface $usageTimestampFrom 枠の開始日時
     * @return \Cake\I18n\FrozenTime|null
     */
    public function getUnitTimeUsageTimestamp($usageTimestampFrom)
    {
        $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($usageTimestampFrom);
        if (!isset($usageTimestampFrom)) {
            throw new CakeException();
        }

        $usageTimestampTo = clone $usageTimestampFrom;
        if ((string)$this->get('time_plan') === ((string)static::PLAN_SINGLE)) {
            $usageTimestampTo = $usageTimestampTo->addMinutes($this->get('usage_time_from'));
            $usageTimestampTo = $usageTimestampTo->addDays($this->get('usage_day_from') - 1);
        } elseif ((string)$this->get('time_plan') === ((string)static::PLAN_MULTIPLE)) {
            $minMinute = null;
            $minData = null;
            foreach ((array)$this->get('event_plans') as $eventPlan) {
                if ((string)$eventPlan->get('public_flg') === ((string)EventPlan::PUBLIC_FLG_ON)) {
                    $minute = $eventPlan->get('usage_day') * 1440 + $eventPlan->get('usage_time');
                    if (!isset($minMinute) || $minMinute > $minute) {
                        $minMinute = $minute;
                        $minData = $eventPlan;
                    }
                }
            }
            if (!isset($minData)) {
                return null;
            }
            $usageTimestampTo = $usageTimestampTo->addMinutes($minData->get('usage_time'));
            $usageTimestampTo = $usageTimestampTo->addDays($minData->get('usage_day') - 1);
        }

        return $usageTimestampTo;
    }

    /**
     * 開始日時から利用日を取得
     *
     * @param string|\DateTimeInterface $usageTimestampFrom 枠の開始日時
     * @return \Cake\I18n\FrozenDate
     */
    public function getUsageDate($usageTimestampFrom)
    {
        $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($usageTimestampFrom);
        if (!isset($usageTimestampFrom)) {
            throw new CakeException();
        }

        $usageDate = new FrozenDate($usageTimestampFrom->format('Y-m-d'));
        if (
            $this->getTimeFrom() >= $this->getTimeTo()
            && DateTimeUtility::convertToTimeObject($usageTimestampFrom, $this->getTimeFrom()) < $this->getTimeFrom()
        ) {
            $usageDate = $usageDate->subDays(1);
        }

        return $usageDate;
    }

    /**
     * 枠として存在する開始日時の判定
     *
     * @param string|\DateTimeInterface $usageTimestampFrom 枠の開始日時
     * @return bool
     */
    public function existsUsageTimestampFrom($usageTimestampFrom)
    {
        $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($usageTimestampFrom);
        if (!isset($usageTimestampFrom)) {
            throw new CakeException();
        }
        $usageTimestampTo = $this->getUsageTimestampTo($usageTimestampFrom);

        if (
            !DateTimeUtility::isWithinDateTime(
                $usageTimestampFrom,
                $usageTimestampTo,
                $this->getDateTimeFrom(),
                $this->getDateTimeTo()
            )
        ) {
            return false;
        }
        if (
            !DateTimeUtility::isWithinTime(
                $usageTimestampFrom,
                $usageTimestampTo,
                $this->getTimeFrom(),
                $this->getTimeTo()
            )
        ) {
            return false;
        }

        $weeks = [];
        $checkHolidays = false;
        foreach ((array)$this->get('event_weeks') as $eventWeek) {
            if (Configure::check('Master.event.week.' . $eventWeek->get('week'))) {
                if ((string)$eventWeek->get('week') !== ((string)static::WEEK_HOLIDAY)) {
                    $weeks[] = $eventWeek->get('week');
                } else {
                    $checkHolidays = true;
                }
            }
        }

        // 祝日データ
        $usageDate = $this->getUsageDate($usageTimestampFrom);
        $publicHolidays = $this->getPublicHolidays();
        if (!isset($publicHolidays)) {
            /** @var \App\Model\Table\HolidaysTable $holidaysTable */
            $holidaysTable = $this->getTableLocator()->get('Holidays');

            $publicHolidays = $holidaysTable->getHolidaysByYear(
                (int)$usageDate->format('Y'),
                (int)$usageDate->format('Y')
            );
        }

        if (!DateTimeUtility::isWithinWeekHoliday($usageDate, $weeks, $checkHolidays, $publicHolidays)) {
            return false;
        }

        $first = new FrozenTime($usageTimestampFrom->format('Y-m-d') . ' ' . $this->getTimeFrom()->format('H:i:s'));
        if ($first->diffInSeconds($usageTimestampFrom) % (60 * $this->get('event_unit_time')) > 0) {
            return false;
        }

        return true;
    }

    /**
     * 枠として存在する日時の判定
     *
     * @param string|\DateTimeInterface $usageTimestampFrom 開始日時
     * @param string|\DateTimeInterface $usageTimestampTo 終了日時
     * @return bool
     */
    public function existsUsageTimestamp($usageTimestampFrom, $usageTimestampTo)
    {
        $adminFlg = $this->commonData()->existsAdminLoginData();

        $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($usageTimestampFrom);
        $usageTimestampTo = DateTimeUtility::convertToDateTimeObject($usageTimestampTo);
        if (!isset($usageTimestampFrom) || !isset($usageTimestampTo)) {
            throw new CakeException();
        }

        $time = $usageTimestampFrom->diffInSeconds($usageTimestampTo);
        if ((string)$this->get('type') === ((string)static::TYPE_TIME) && !$this->isAllHours() && $time >= DAY) {
            return false;
        }

        if (
            !DateTimeUtility::isWithinDateTime(
                $usageTimestampFrom,
                $usageTimestampTo,
                $this->getDateTimeFrom(),
                $this->getDateTimeTo()
            )
        ) {
            return false;
        }
        if (
            !DateTimeUtility::isWithinTime(
                $usageTimestampFrom,
                $usageTimestampTo,
                $this->getTimeFrom(),
                $this->getTimeTo()
            )
        ) {
            return false;
        }

        $eventTimetable = new EventTimetable($this, $usageTimestampFrom, $usageTimestampTo, $adminFlg);
        foreach ($eventTimetable->createTimetable(true) as $eventUnit) {
            if (!$eventUnit->existsUsageTimestampFrom()) {
                return false;
            }
        }

        return true;
    }

    /**
     * 24時間枠の判定
     *
     * @return bool
     */
    public function isAllHours()
    {
        if ($this->getTimeFrom()->diffInSeconds($this->getTimeTo()) > 0) {
            return false;
        }

        return true;
    }

    /**
     * インターバル時間を加算
     *
     * @param string|\DateTimeInterface|null $dateTime 日時
     * @return \Cake\I18n\FrozenTime
     */
    public function addIntervalTime($dateTime)
    {
        $dateTime = DateTimeUtility::convertToDateTimeObject($dateTime);
        if (!isset($dateTime)) {
            throw new CakeException();
        }

        if ($this->has('interval_day')) {
            $dateTime = $dateTime->addDays($this->get('interval_day'));
        }
        if ($this->has('interval_time')) {
            $dateTime = $dateTime->addMinutes($this->get('interval_time'));
        }

        return $dateTime;
    }

    /**
     * インターバル時間を減算
     *
     * @param string|\DateTimeInterface|null $dateTime 日時
     * @return \Cake\I18n\FrozenTime
     */
    public function subIntervalTime($dateTime)
    {
        $dateTime = DateTimeUtility::convertToDateTimeObject($dateTime);
        if (!isset($dateTime)) {
            throw new CakeException();
        }

        if ($this->has('interval_day')) {
            $dateTime = $dateTime->subDays($this->get('interval_day'));
        }
        if ($this->has('interval_time')) {
            $dateTime = $dateTime->subMinutes($this->get('interval_time'));
        }

        return $dateTime;
    }

    /**
     * 受付期間の境界となる日時を取得
     *
     * @param string|\DateTimeInterface|null $targetDateTime 判定対象の日時(現在日時)
     * @return \Cake\I18n\FrozenTime|null
     */
    public function getReceptionDateTime($targetDateTime = null)
    {
        if ((string)$this->get('reception_period_number') === '') {
            return null;
        }

        $useProperty = false;
        $targetDateTime = DateTimeUtility::convertToDateTimeObject($targetDateTime);
        if (!isset($targetDateTime)) {
            $useProperty = true;
            $targetDateTime = $this->commonData()->getNowDateTime();
        }

        $receptionDateTime = $this->receptionDateTime;
        if (!$useProperty || !isset($this->receptionStartDateTime)) {
            $receptionDateTime = new FrozenTime($targetDateTime->format('Y-m-d'));
            $receptionDateTime = $receptionDateTime->addDays($this->get('reception_period_number'));

            $receptionPeriodTime = DateTimeUtility::convertToTimeObject(
                $this->get('reception_period_time'),
                $targetDateTime
            );
            if ($targetDateTime >= $receptionPeriodTime) {
                $receptionDateTime = $receptionDateTime->addDays(1);
            }

            if ($useProperty) {
                $this->receptionDateTime = $receptionDateTime;
            }
        }

        return $receptionDateTime;
    }

    /**
     * 登録締切の境界となる日時を取得
     *
     * @param string|\DateTimeInterface|null $targetDateTime 判定対象の日時(現在日時)
     * @return \Cake\I18n\FrozenTime
     */
    public function getRegistrationDeadline($targetDateTime = null)
    {
        $useProperty = false;
        $targetDateTime = DateTimeUtility::convertToDateTimeObject($targetDateTime);
        if (!isset($targetDateTime)) {
            $useProperty = true;
            $targetDateTime = $this->commonData()->getNowDateTime();
        }

        $registrationDeadline = $this->registrationDeadline;
        if (!$useProperty || !isset($registrationDeadline)) {
            $registrationDeadline = $this->getDeadlineDateTime(
                $this->get('registration_deadline_type'),
                $this->get('registration_deadline_number'),
                $this->get('registration_deadline_time'),
                $targetDateTime
            );
            if ($useProperty) {
                $this->registrationDeadline = $registrationDeadline;
            }
        }

        return $registrationDeadline;
    }

    /**
     * 編集締切の境界となる日時を取得
     *
     * @param string|\DateTimeInterface|null $targetDateTime 判定対象の日時(現在日時)
     * @return \Cake\I18n\FrozenTime
     */
    public function getEditingDeadline($targetDateTime = null)
    {
        $useProperty = false;
        $targetDateTime = DateTimeUtility::convertToDateTimeObject($targetDateTime);
        if (!isset($targetDateTime)) {
            $useProperty = true;
            $targetDateTime = $this->commonData()->getNowDateTime();
        }

        $editingDeadline = $this->editingDeadline;
        if (!$useProperty || !isset($editingDeadline)) {
            $editingDeadline = $this->getDeadlineDateTime(
                $this->get('editing_deadline_type'),
                $this->get('editing_deadline_number'),
                $this->get('editing_deadline_time'),
                $targetDateTime
            );
            if ($useProperty) {
                $this->editingDeadline = $editingDeadline;
            }
        }

        return $editingDeadline;
    }

    /**
     * キャンセル締切の境界となる日時を取得
     *
     * @param string|\DateTimeInterface|null $targetDateTime 判定対象の日時(現在日時)
     * @return \Cake\I18n\FrozenTime
     */
    public function getCancellationDeadline($targetDateTime = null)
    {
        $useProperty = false;
        $targetDateTime = DateTimeUtility::convertToDateTimeObject($targetDateTime);
        if (!isset($targetDateTime)) {
            $useProperty = true;
            $targetDateTime = $this->commonData()->getNowDateTime();
        }

        $cancellationDeadline = $this->cancellationDeadline;
        if (!$useProperty || !isset($cancellationDeadline)) {
            $cancellationDeadline = $this->getDeadlineDateTime(
                $this->get('cancellation_deadline_type'),
                $this->get('cancellation_deadline_number'),
                $this->get('cancellation_deadline_time'),
                $targetDateTime
            );
            if ($useProperty) {
                $this->cancellationDeadline = $cancellationDeadline;
            }
        }

        return $cancellationDeadline;
    }

    /**
     * 締切の境界となる日時を取得
     *
     * @param int $deadlineType 締切タイプ
     * @param int $deadlineNumber 締切時間数
     * @param string|\DateTimeInterface|null $deadlineTime 締切時間
     * @param \DateTimeInterface $targetDateTime 判定対象の日時(現在日時)
     * @return \Cake\I18n\FrozenTime
     */
    protected function getDeadlineDateTime($deadlineType, $deadlineNumber, $deadlineTime, $targetDateTime)
    {
        $function = [
            static::DEADLINE_TYPE_TIME => function ($deadlineNumber, $deadlineTime, $targetDateTime) {
                $deadlineDateTime = new FrozenTime($targetDateTime->format('Y-m-d H:i:s'));
                $deadlineDateTime = $deadlineDateTime->addHours($deadlineNumber);

                return $deadlineDateTime;
            },
            static::DEADLINE_TYPE_DAY => function ($deadlineNumber, $deadlineTime, $targetDateTime) {
                $deadlineDateTime = new FrozenTime($targetDateTime->format('Y-m-d'));
                $deadlineDateTime = $deadlineDateTime->addDays($deadlineNumber);

                $deadlineTime = DateTimeUtility::convertToTimeObject($deadlineTime, $targetDateTime);
                if ($targetDateTime >= $deadlineTime) {
                    $deadlineDateTime = $deadlineDateTime->addDays(1);
                }

                return $deadlineDateTime;
            },
        ];
        $deadlineDateTime = call_user_func($function[$deadlineType], $deadlineNumber, $deadlineTime, $targetDateTime);

        return $deadlineDateTime;
    }

    /**
     * 直近の受付可能枠の境界となる日時を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getFirstReceptionDateTime()
    {
        if (!isset($this->firstReceptionDateTime)) {
            $firstReceptionDateTime = $this->getDateTimeFrom();
            $registrationDeadline = $this->getRegistrationDeadline();
            if ($firstReceptionDateTime < $registrationDeadline) {
                $firstReceptionDateTime = $registrationDeadline;
            }

            $this->firstReceptionDateTime = $firstReceptionDateTime;
        }

        return $this->firstReceptionDateTime;
    }

    /**
     * 公開設定の判定
     *
     * @param string|\DateTimeInterface $targetDateTime 判定対象の日時(現在日時)
     * @return bool
     */
    public function isPublic($targetDateTime = null)
    {
        if ((string)$this->get('public_flg') !== ((string)static::PUBLIC_FLG_ON)) {
            return false;
        }

        $targetDateTime = DateTimeUtility::convertToDateTimeObject($targetDateTime);
        if (!isset($targetDateTime)) {
            $targetDateTime = $this->commonData()->getNowDateTime();
        }

        $publicFrom = DateTimeUtility::convertToDateTimeObject($this->get('public_from'));
        if (isset($publicFrom) && $publicFrom > $targetDateTime) {
            return false;
        }

        $publicTo = DateTimeUtility::convertToDateTimeObject($this->get('public_to'));
        if (isset($publicTo) && $publicTo <= $targetDateTime) {
            return false;
        }

        return true;
    }

    /**
     * 祝日を取得
     *
     * @return array|null
     */
    public function getPublicHolidays()
    {
        return $this->publicHolidays;
    }

    /**
     * 祝日を設定
     *
     * @param array $publicHolidays 祝日
     * @return void
     */
    public function setPublicHolidays(array $publicHolidays)
    {
        $this->publicHolidays = $publicHolidays;
    }

    /**
     * 休日の判定
     *
     * @param \DateTimeInterface $usageTimestampFrom 枠の開始日時
     * @param \DateTimeInterface $usageTimestampTo 枠の終了日時
     * @return bool
     */
    public function isHoliday($usageTimestampFrom, $usageTimestampTo = null)
    {
        /** @var \App\Model\Table\EventHolidaysTable $eventHolidaysTable */
        $eventHolidaysTable = $this->getTableLocator()->get('EventHolidays');

        if (!isset($usageTimestampTo)) {
            $usageTimestampTo = $this->getUsageTimestampTo($usageTimestampFrom);
        }

        $allHolidays = $eventHolidaysTable->getAllHoliday();
        foreach (array_merge((array)$this->get('event_holidays'), $allHolidays) as $holiday) {
            $weeks = [];
            foreach ((array)$holiday->get('event_holiday_weeks') as $holidayWeek) {
                if (Configure::check('Master.event.week.' . $holidayWeek->get('week'))) {
                    $weeks[] = $holidayWeek->get('week');
                }
            }
            $excludeDates = [];
            foreach ((array)$holiday->get('event_holiday_exclude_dates') as $holidayExcludeDate) {
                $excludeDates[] = $holidayExcludeDate->get('date');
            }

            if (
                $this->isOverlapDateTimeWeek(
                    $usageTimestampFrom,
                    $usageTimestampTo,
                    $holiday->get('date_from'),
                    $holiday->get('date_to'),
                    $weeks,
                    $excludeDates,
                    $holiday->get('time_from'),
                    $holiday->get('time_to')
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 全在庫数を取得
     *
     * @param \DateTimeInterface $usageTimestampFrom 枠の開始日時
     * @param \DateTimeInterface $usageTimestampTo 枠の終了日時
     * @return int
     */
    public function getStock($usageTimestampFrom, $usageTimestampTo = null)
    {
        if (!isset($usageTimestampTo)) {
            $usageTimestampTo = $this->getUsageTimestampTo($usageTimestampFrom);
        }

        foreach (array_reverse((array)$this->get('event_stock_settings')) as $stockSetting) {
            $weeks = [];
            foreach ((array)$stockSetting->get('event_stock_setting_weeks') as $stockSettingWeek) {
                if (Configure::check('Master.event.week.' . $stockSettingWeek->get('week'))) {
                    $weeks[] = $stockSettingWeek->get('week');
                }
            }
            $excludeDates = [];
            foreach ((array)$stockSetting->get('event_stock_setting_exclude_dates') as $stockSettingExcludeDate) {
                $excludeDates[] = $stockSettingExcludeDate->get('date');
            }

            if (
                $this->isOverlapDateTimeWeek(
                    $usageTimestampFrom,
                    $usageTimestampTo,
                    $stockSetting->get('date_from'),
                    $stockSetting->get('date_to'),
                    $weeks,
                    $excludeDates,
                    $stockSetting->get('time_from'),
                    $stockSetting->get('time_to')
                )
            ) {
                return (int)$stockSetting->get('stock');
            }
        }

        return (int)$this->get('stock');
    }

    /**
     * カラーチップの利用を判定
     *
     * @param int $colorChipType カラーチップタイプ
     * @param bool $adminFlg 管理者側フラグ
     * @return bool
     */
    public function usesColorChip(int $colorChipType, bool $adminFlg = false)
    {
        if (((string)$colorChipType) === ((string)ColorChip::TYPE_EMPTY)) {
            if ((string)$this->get('background_color_type') !== ((string)static::BACKGROUND_COLOR_TYPE_DEFAULT)) {
                return false;
            }
        } elseif (((string)$colorChipType) === ((string)ColorChip::TYPE_ADD)) {
            if ((string)$this->get('background_color_type') !== ((string)static::BACKGROUND_COLOR_TYPE_COLOR_CODE)) {
                return false;
            }
        } else {
            $backgroundColorReplace = $this->get('background_color_replace_front');
            if ($adminFlg) {
                $backgroundColorReplace = $this->get('background_color_replace_admin');
            }
            if (!ArrayUtility::inArray($colorChipType, (array)$backgroundColorReplace)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 在庫記号を取得
     *
     * @param int $remainStock 残り在庫
     * @return int|null
     */
    public function getEventStockMark($remainStock)
    {
        $maxNumber = null;
        $maxData = null;
        foreach ((array)$this->get('event_stock_marks') as $eventStockMarks) {
            $number = $eventStockMarks->get('number');
            if ($remainStock >= $number && (!isset($maxNumber) || $maxNumber < $number)) {
                $maxNumber = $number;
                $maxData = $eventStockMarks;
            }
        }

        if (!isset($maxData)) {
            return null;
        }

        return $maxData->get('symbolic');
    }

    /**
     * 単一枠の判定
     *
     * @return bool
     */
    public function isSingleUnit()
    {
        $dateFrom = DateTimeUtility::convertToDateObject($this->get('date_from'));
        $dateTo = DateTimeUtility::convertToDateObject($this->get('date_to'));
        if (!isset($dateFrom) || !isset($dateTo)) {
            return false;
        }
        if ($dateFrom->format('Y-m-d') !== $dateTo->format('Y-m-d') || !$this->isSingleUnitByDate()) {
            return false;
        }

        return true;
    }

    /**
     * EventUnitを生成
     *
     * @param string|\DateTimeInterface $usageTimestampFrom 利用開始日時
     * @param bool|null $isAdmin 管理側フラグ
     * @return \App\Model\EventCalendar\EventUnit
     */
    public function createEventUnit($usageTimestampFrom, ?bool $isAdmin = null)
    {
        if (!isset($isAdmin)) {
            $isAdmin = $this->commonData()->existsAdminLoginData();
        }

        return new EventUnit($this, $usageTimestampFrom, $this->getUsageTimestampTo($usageTimestampFrom), $isAdmin);
    }

    /**
     * 1日での単一枠の判定
     *
     * @return bool
     */
    public function isSingleUnitByDate()
    {
        $timeFrom = $this->getTimeFrom();
        $timeTo = $this->getTimeTo();
        if ($timeFrom >= $timeTo) {
            $timeTo = $timeTo->addDays(1);
        }

        if ($timeFrom->diffInSeconds($timeTo) !== $this->get('event_unit_time') * 60) {
            return false;
        }

        return true;
    }

    /**
     * 枠の日時が日付、曜日、除外日、時間の指定を満たしているか判定
     *
     * @param \DateTimeInterface $usageTimestampFrom 枠の開始日時
     * @param \DateTimeInterface $usageTimestampTo 枠の終了日時
     * @param \DateTimeInterface|null $dateFrom 日付(From)
     * @param \DateTimeInterface|null $dateTo 日付(To)
     * @param array|null $weeks 曜日
     * @param array|null $excludeDates 除外日
     * @param string|\DateTimeInterface|null $timeFrom 時間(From)
     * @param string|\DateTimeInterface|null $timeTo 時間(To)
     * @return bool
     */
    protected function isOverlapDateTimeWeek(
        $usageTimestampFrom,
        $usageTimestampTo,
        $dateFrom,
        $dateTo,
        $weeks,
        $excludeDates,
        $timeFrom,
        $timeTo
    ) {
        // 祝日
        $checkHolidays = false;
        if (isset($weeks)) {
            foreach ($weeks as $index => $week) {
                if (((string)$week) === ((string)static::WEEK_HOLIDAY)) {
                    $checkHolidays = true;
                    unset($weeks[$index]);
                    break;
                }
            }
        }

        $publicHolidays = $this->getPublicHolidays();
        if (!isset($publicHolidays)) {
            /** @var \App\Model\Table\HolidaysTable $holidaysTable */
            $holidaysTable = $this->getTableLocator()->get('Holidays');

            $publicHolidays = $holidaysTable->getHolidaysByYear(
                (int)$usageTimestampFrom->format('Y'),
                (int)$usageTimestampTo->format('Y')
            );
        }

        $usageDate = $this->getUsageDate($usageTimestampFrom);
        if (
            !DateTimeUtility::isWithinDateWeekHoliday(
                $usageDate,
                $dateFrom,
                $dateTo,
                $weeks,
                $checkHolidays,
                $excludeDates,
                $publicHolidays
            )
        ) {
            return false;
        }

        $timeFrom = DateTimeUtility::convertToTimeObject($timeFrom);
        if (!isset($timeFrom)) {
            $timeFrom = $this->getTimeFrom();
        }
        $timeTo = DateTimeUtility::convertToTimeObject($timeTo);
        if (!isset($timeTo)) {
            $timeTo = $this->getTimeTo();
        }

        $timeFrom = new FrozenTime($usageDate->format('Y-m-d') . ' ' . $timeFrom->format('H:i:s'));
        $timeTo = new FrozenTime($usageDate->format('Y-m-d') . ' ' . $timeTo->format('H:i:s'));
        if ($timeFrom >= $timeTo) {
            $timeTo = $timeTo->addDays(1);
        }
        if ($this->getTimeFrom() >= $this->getTimeTo()) {
            if (DateTimeUtility::convertToTimeObject($timeFrom, $this->getTimeFrom()) < $this->getTimeFrom()) {
                $timeFrom = $timeFrom->addDays(1);
                $timeTo = $timeTo->addDays(1);
            }
        }
        if (!DateTimeUtility::isOverlapDateTime($usageTimestampFrom, $usageTimestampTo, $timeFrom, $timeTo)) {
            return false;
        }

        return true;
    }

    /**
     * 残り在庫をチェック
     *
     * @param string|\DateTimeInterface|null $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface|null $dateTimeTo 終了日時
     * @param int|null $excludeReservationId 除外する予約ID
     * @param array|null $reservations 予約中データ
     * @param bool|null $adminFlg 管理者側フラグ
     * @param array|null $waitingCancellation キャンセル待ちデータ
     * @return bool
     */
    public function checkRemainStock(
        $dateTimeFrom = null,
        $dateTimeTo = null,
        ?int $excludeReservationId = null,
        ?array $reservations = null,
        ?bool $adminFlg = null,
        ?array $waitingCancellation = null
    ) {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);
        if (!isset($adminFlg)) {
            $adminFlg = $this->commonData()->existsAdminLoginData();
        }

        // 予約日時の最小値と最大値
        if (empty($reservations)) {
            $reservationMinMaxDateTime = $reservationsTable->find('minMaxDateTime', [
                'inputs' => [
                    'event_id' => $this->get('id'),
                    'usage_timestamp_from' => $dateTimeFrom,
                    'usage_timestamp_to' => $dateTimeTo,
                ],
            ])->first();
            if (!isset($reservationMinMaxDateTime)) {
                return true;
            }

            $minDateTime = $this->subIntervalTime($reservationMinMaxDateTime['min']);
            if (!isset($dateTimeFrom) || $dateTimeFrom < $minDateTime) {
                $dateTimeFrom = $minDateTime;
            }
            $maxDateTime = $this->addIntervalTime($reservationMinMaxDateTime['max']);
            if (!isset($dateTimeTo) || $dateTimeTo > $maxDateTime) {
                $dateTimeTo = $maxDateTime;
            }
        }

        // 1ヶ月単位でチェック
        if (!isset($dateTimeFrom) || !isset($dateTimeTo)) {
            throw new CakeException();
        }
        $current = $dateTimeFrom;
        $limit = $dateTimeTo;
        while ($current < $limit) {
            $from = $current;
            $to = new FrozenTime($from->format('Y-m-01'));
            $to = $to->addMonths(1);
            if ($to > $limit) {
                $to = $limit;
            }

            $eventTimetable = new EventTimetable($this, $from, $to, $adminFlg);
            $eventTimetable->createReservedTimetable($excludeReservationId, $reservations, false, $waitingCancellation);
            if ($eventTimetable->isOutOfStock(false)) {
                return false;
            }

            $current = $to;
        }

        return true;
    }

    /**
     * 予約数の選択肢を取得
     *
     * @return array
     */
    public function getNumberValueOptions()
    {
        $valueOptions = [];

        $current = $this->get('stock_range_from');
        $limit = $this->get('stock_range_to');
        while ($current <= $limit) {
            $valueOptions[$current] = $current . $this->get('stock_unit');
            $current += 1;
        }

        return $valueOptions;
    }

    /**
     * 予約数の選択肢固定を判定
     *
     * @return bool
     */
    public function isFixedNumber()
    {
        if (count($this->getNumberValueOptions()) > 1) {
            return false;
        }

        return true;
    }

    /**
     * 予約数の非表示可否を判定
     *
     * @return bool
     */
    public function canHideNumberInput()
    {
        if (!$this->isFixedNumber()) {
            return false;
        }

        return true;
    }

    /**
     * 時間の選択肢を取得
     *
     * @param bool $adminFlg 管理側フラグ
     * @param int|null $includeValue 含める値
     * @return array
     */
    public function getUsageTimeValueOptions(bool $adminFlg = false, ?int $includeValue = null)
    {
        if ((string)$this->get('time_plan') === ((string)static::PLAN_MULTIPLE)) {
            return [];
        }

        $time = $this->get('usage_time_from');
        $limit = $this->get('usage_time_to');
        $step = $this->get('usage_unit_time');
        if ((string)$this->get('type') === ((string)static::TYPE_TIME) && $adminFlg) {
            $time = $this->get('event_unit_time');
            $limit = max($limit, static::USAGE_TIME_TO_ADMIN);
            $step = $this->get('event_unit_time');
        }

        $valueOptions = [];
        $unit = __('reservation/dateTimeMinute');
        while ($time <= $limit) {
            $valueOptions[$time] = $time . $unit;
            $time += $step;
        }
        if (isset($includeValue)) {
            $valueOptions[$includeValue] = $includeValue . $unit;
        }

        return $valueOptions;
    }

    /**
     * 日付の選択肢を取得
     *
     * @param bool $adminFlg 管理側フラグ
     * @param int|null $includeValue 含める値
     * @return array
     */
    public function getUsageDayValueOptions(bool $adminFlg = false, ?int $includeValue = null)
    {
        if ((string)$this->get('time_plan') === ((string)static::PLAN_MULTIPLE)) {
            return [];
        }

        $day = $this->get('usage_day_from');
        $limit = $this->get('usage_day_to');
        $step = $this->get('usage_unit_day');
        if ((string)$this->get('type') === ((string)static::TYPE_DAY) && $adminFlg) {
            $day = static::USAGE_DAY_FROM_ADMIN;
            $limit = max($limit, static::USAGE_DAY_TO_ADMIN);
            $step = static::USAGE_UNIT_DAY_ADMIN;
        }

        $valueOptions = [];
        $unit = __('reservation/dateTimeDay');
        while ($day <= $limit) {
            $valueOptions[$day] = $day . $unit;
            $day += $step;
        }
        if (isset($includeValue)) {
            $valueOptions[$includeValue] = $includeValue . $unit;
        }

        return $valueOptions;
    }

    /**
     * 複数プランの選択肢を取得
     *
     * @param bool $adminFlg 管理側フラグ
     * @param array|null $includeValues 含める値
     * @return array
     */
    public function getEventPlansValueOptions(bool $adminFlg = false, ?array $includeValues = null)
    {
        if ((string)$this->get('time_plan') !== ((string)static::PLAN_MULTIPLE)) {
            return [];
        }

        $eventPlans = $this->get('event_plans');
        if (!isset($eventPlans)) {
            return [];
        }

        $valueOptions = [];
        foreach ($eventPlans as $eventPlan) {
            if (
                $adminFlg || ((string)$eventPlan->get('public_flg')) === ((string)EventPlan::PUBLIC_FLG_ON)
                || ArrayUtility::inArray($eventPlan->get('id'), (array)$includeValues)
            ) {
                $valueOptions[$eventPlan->get('id')] = $eventPlan->get('name');
            }
        }

        return $valueOptions;
    }

    /**
     * 時間の選択肢の固定を判定
     *
     * @param bool $adminFlg 管理側フラグ
     * @return bool
     */
    public function isFixedUsageTimeValueOptions(bool $adminFlg = false)
    {
        if (count($this->getUsageTimeValueOptions($adminFlg)) > 1) {
            return false;
        }

        return true;
    }

    /**
     * 日付の選択肢の固定を判定
     *
     * @param bool $adminFlg 管理側フラグ
     * @return bool
     */
    public function isFixedUsageDayValueOptions(bool $adminFlg = false)
    {
        if (count($this->getUsageDayValueOptions($adminFlg)) > 1) {
            return false;
        }

        return true;
    }

    /**
     * 予約時間の非表示可否を判定
     *
     * @param bool $adminFlg 管理側フラグ
     * @return bool
     */
    public function canHideReservationTimeInput(bool $adminFlg = false)
    {
        if (
            !$this->isFixedUsageTimeValueOptions($adminFlg) || !$this->isFixedUsageDayValueOptions($adminFlg)
            || ((string)$this->get('time_plan')) === ((string)static::PLAN_MULTIPLE)
        ) {
            return false;
        }

        return true;
    }

    /**
     * time_fromのアクセサ
     *
     * @param mixed $val 値
     * @return string
     */
    protected function _getTimeFrom($val)
    {
        return $this->formatTime24($val);
    }

    /**
     * time_toを**:**表記に
     *
     * @param mixed $val 値
     * @return string
     */
    protected function _getTimeTo($val)
    {
        return $this->formatTime24($val);
    }

    /**
     * reception_period_timeを**:**表記に
     *
     * @param mixed $val 値
     * @return string
     */
    protected function _getReceptionPeriodTime($val)
    {
        return $this->formatTime24($val);
    }

    /**
     * registration_deadline_timeを**:**表記に
     *
     * @param mixed $val 値
     * @return string
     */
    protected function _getRegistrationDeadlineTime($val)
    {
        return $this->formatTime24($val);
    }

    /**
     * editing_deadline_timeを**:**表記に
     *
     * @param mixed $val 値
     * @return string
     */
    protected function _getEditingDeadlineTime($val)
    {
        return $this->formatTime24($val);
    }

    /**
     * cancellation_deadline_timeを**:**表記に
     *
     * @param mixed $val 値
     * @return string
     */
    protected function _getCancellationDeadlineTime($val)
    {
        return $this->formatTime24($val);
    }

    /**
     * background_color_replace_frontのミューテーター
     *
     * @param array|null $data 値
     * @return array|null
     */
    protected function _setBackgroundColorReplaceFront($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        return $this->filterForMultiCheckbox($data);
    }

    /**
     * background_color_replace_adminのミューテーター
     *
     * @param array|null $data 値
     * @return array|null
     */
    protected function _setBackgroundColorReplaceAdmin($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        return $this->filterForMultiCheckbox($data);
    }

    /**
     * format_type_displayのミューテーター
     *
     * @param array|null $data 値
     * @return array|null
     */
    protected function _setFormatTypeDisplay($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        return $this->filterForMultiCheckbox($data);
    }

    /**
     * 削除可能チェック
     *
     * @return bool 判定結果
     */
    public function canDelete()
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $count = $reservationsTable->find('count', [
            'inputs' => ['event_id' => $this->get('id')],
        ])->count();

        if ($count > 0) {
            return false;
        }

        return true;
    }

    /**
     * 日にち単位の予約枠単位時間を取得
     *
     * @return int
     */
    public function calculateUsageUnitTimeForDay()
    {
        $timeFromObj = new FrozenTime($this->get('time_from'));
        $timeToObj = new FrozenTime($this->get('time_to'));

        $usageUnitTimeDiffObj = $timeFromObj->diff($timeToObj);
        if ($usageUnitTimeDiffObj->invert >= 1) {
            $usageUnitTime = ($usageUnitTimeDiffObj->invert * 24 * 60)
                - ($usageUnitTimeDiffObj->h * 60 + $usageUnitTimeDiffObj->i);
        } elseif ($usageUnitTimeDiffObj->h === 0 && $usageUnitTimeDiffObj->i === 0) {
            $usageUnitTime = 24 * 60;
        } else {
            $usageUnitTime = $usageUnitTimeDiffObj->h * 60 + $usageUnitTimeDiffObj->i;
        }

        return $usageUnitTime;
    }

    /**
     * QRコード表示可能可能チェック
     *
     * @return bool 判定結果
     */
    public function canDisplayQrCode()
    {
        if ((string)$this->get('qr_code_flg') !== (string)static::QR_CODE_FLG_ON) {
            return false;
        }

        return true;
    }

    /**
     * 登録締切の判定基準を利用終了日時とするか
     *
     * @return bool
     */
    public function isRegistrationDeadlineCriterionTo()
    {
        return (string)$this->get('registration_deadline_criterion') === (string)Event::CRITERION_TO;
    }

    /**
     * 編集締切の判定基準を利用終了日時とするか
     *
     * @return bool
     */
    public function isEditingDeadlineCriterionTo()
    {
        return (string)$this->get('editing_deadline_criterion') === (string)Event::CRITERION_TO;
    }

    /**
     * キャンセル締切の判定基準を利用終了日時とするか
     *
     * @return bool
     */
    public function isCancellationDeadlineCriterionTo()
    {
        return (string)$this->get('cancellation_deadline_criterion') === (string)Event::CRITERION_TO;
    }

    /**
     * 予約枠スマートロックを取得
     *
     * @return \App\Model\Entity\EventSmartLock|null
     */
    public function getEventSmartLockEntity()
    {
        /** @var \App\Model\Table\EventSmartLocksTable $eventSmartLocksTable */
        $eventSmartLocksTable = $this->getTableLocator()->get('EventSmartLocks');

        if (!isset($this->eventSmartLockEntity) && $this->has('id')) {
            try {
                /** @var \App\Model\Entity\EventSmartLock $eventSmartLockEntity */
                $eventSmartLockEntity = $eventSmartLocksTable->find()
                    ->where(['event_id' => $this->get('id')])
                    ->firstOrFail();
                $this->eventSmartLockEntity = $eventSmartLockEntity;
            } catch (RecordNotFoundException $e) {
                return null;
            }
        }

        return $this->eventSmartLockEntity;
    }
}
