<?php
declare(strict_types=1);

namespace App\Model\EventCalendar;

use App\Model\Entity\ColorChip;
use App\Model\Entity\Event;
use App\Utility\ArrayUtility;
use App\Utility\CommonData\CommonDataTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Hash;

/**
 * EventUnit class.
 */
class EventUnit
{
    use CommonDataTrait;
    use LocatorAwareTrait;

    /**
     * @var bool
     */
    protected $isAdmin = null;

    /**
     * @var \App\Model\Entity\Event
     */
    protected $event = null;

    /**
     * @var \Cake\I18n\FrozenTime
     */
    protected $dateTimeFrom = null;

    /**
     * @var \Cake\I18n\FrozenTime
     */
    protected $dateTimeTo = null;

    /**
     * @var bool|null
     */
    protected $isHoliday = null;

    /**
     * @var bool|null
     */
    protected $isWithinReceptionPeriod = null;

    /**
     * @var bool|null
     */
    protected $isWithinRegistrationDeadline = null;

    /**
     * @var bool|null
     */
    protected $isWithinEditingDeadline = null;

    /**
     * @var bool|null
     */
    protected $isWithinCancellationDeadline = null;

    /**
     * @var int|null
     */
    protected $allStock = null;

    /**
     * @var int|null
     */
    protected $reservedStock = null;

    /**
     * @var int|null
     */
    protected $remainStock = null;

    /**
     * @var bool
     */
    protected $existsContinuousData = false;

    /**
     * @var bool
     */
    protected $existsUserReservation = false;

    /**
     * @var array|null
     */
    protected $colorChip = null;

    /**
     * @var array|\ArrayAccess
     */
    protected $reservationCount = null;

    /**
     * @var mixed|null
     */
    protected $reservationData = null;

    /**
     * @var array
     */
    protected $reservedIntervalStock = null;

    /**
     * @var int|null
     */
    protected $intervalStock = null;

    /**
     * Constructor.
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @param string|\DateTimeInterface $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface $dateTimeTo 終了日時
     * @param bool $isAdmin 管理側フラグ
     */
    public function __construct(Event $event, $dateTimeFrom, $dateTimeTo, bool $isAdmin = false)
    {
        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);
        if (!isset($dateTimeFrom) || !isset($dateTimeTo)) {
            throw new CakeException();
        }

        $this->isAdmin = $isAdmin;
        $this->event = $event;
        $this->dateTimeFrom = $dateTimeFrom;
        $this->dateTimeTo = $dateTimeTo;

        $this->reservationCount = [];
        $this->reservedIntervalStock = [
            'previous' => 0,
            'next' => 0,
        ];
    }

    /**
     * 管理側フラグを取得
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * 予約枠を取得
     *
     * @return \App\Model\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * 開始日時を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getDateTimeFrom()
    {
        return $this->dateTimeFrom;
    }

    /**
     * 終了日時を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getDateTimeTo()
    {
        return $this->dateTimeTo;
    }

    /**
     * 休日の判定
     *
     * @return bool
     */
    public function isHoliday()
    {
        if (!isset($this->isHoliday)) {
            $this->isHoliday = $this->getEvent()->isHoliday($this->getDateTimeFrom(), $this->getDateTimeTo());
        }

        return $this->isHoliday;
    }

    /**
     * 受付期間の判定
     *
     * @return bool
     */
    public function isWithinReceptionPeriod()
    {
        if (!isset($this->isWithinReceptionPeriod)) {
            $isWithinReceptionPeriod = true;
            $receptionDateTime = $this->getEvent()->getReceptionDateTime();
            if (isset($receptionDateTime) && $receptionDateTime <= $this->getDateTimeFrom()) {
                $isWithinReceptionPeriod = false;
            }

            $this->isWithinReceptionPeriod = $isWithinReceptionPeriod;
        }

        return $this->isWithinReceptionPeriod;
    }

    /**
     * 登録締切の判定基準日時を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getRegistrationDeadlineCriterion()
    {
        return $this->getEvent()->isRegistrationDeadlineCriterionTo() ?
            $this->getDateTimeTo() : $this->getDatetimeFrom();
    }

    /**
     * 編集締切の判定基準日時を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getEditingDeadlineCriterion()
    {
        return $this->getEvent()->isEditingDeadlineCriterionTo() ? $this->getDateTimeTo() : $this->getDatetimeFrom();
    }

    /**
     * キャンセル締切の判定基準日時を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getCancellationDeadlineCriterion()
    {
        return $this->getEvent()->isCancellationDeadlineCriterionTo() ?
            $this->getDateTimeTo() : $this->getDatetimeFrom();
    }

    /**
     * 登録締切日の判定
     *
     * @return bool
     */
    public function isWithinRegistrationDeadline()
    {
        if (!isset($this->isWithinRegistrationDeadline)) {
            $isWithinRegistrationDeadline = true;
            $registrationDeadline = $this->getEvent()->getRegistrationDeadline();
            if (
                $registrationDeadline > $this->getRegistrationDeadlineCriterion()
                || $this->getDateTimeTo() <= $this->commonData()->getNowDateTime()
            ) {
                $isWithinRegistrationDeadline = false;
            }

            $this->isWithinRegistrationDeadline = $isWithinRegistrationDeadline;
        }

        return $this->isWithinRegistrationDeadline;
    }

    /**
     * 編集締切日の判定
     *
     * @return bool
     */
    public function isWithinEditingDeadline()
    {
        if (!isset($this->isWithinEditingDeadline)) {
            $isWithinEditingDeadline = true;
            $editingDeadline = $this->getEvent()->getEditingDeadline();
            if ($editingDeadline > $this->getEditingDeadlineCriterion()) {
                $isWithinEditingDeadline = false;
            }

            $this->isWithinEditingDeadline = $isWithinEditingDeadline;
        }

        return $this->isWithinEditingDeadline;
    }

    /**
     * キャンセル締切日の判定
     *
     * @return bool
     */
    public function isWithinCancellationDeadline()
    {
        if (!isset($this->isWithinCancellationDeadline)) {
            $isWithinCancellationDeadline = true;
            $cancellationDeadline = $this->getEvent()->getCancellationDeadline();
            if ($cancellationDeadline > $this->getCancellationDeadlineCriterion()) {
                $isWithinCancellationDeadline = false;
            }

            $this->isWithinCancellationDeadline = $isWithinCancellationDeadline;
        }

        return $this->isWithinCancellationDeadline;
    }

    /**
     * 全在庫数を取得
     *
     * @return int
     */
    public function getAllStock()
    {
        if (!isset($this->allStock)) {
            $this->allStock = $this->getEvent()->getStock($this->getDateTimeFrom(), $this->getDateTimeTo());
        }

        return $this->allStock;
    }

    /**
     * 予約数を取得
     *
     * @return int
     */
    public function getReservedStock()
    {
        if (!isset($this->reservedStock)) {
            $this->reservedStock = 0;
        }

        return $this->reservedStock;
    }

    /**
     * 残り在庫を取得
     *
     * @return int
     */
    public function getRemainStock()
    {
        if (!isset($this->remainStock)) {
            $remainStock = $this->getAllStock()
                - $this->getReservedStock()
                - ArrayUtility::arrayMax($this->reservedIntervalStock);
            if (isset($this->intervalStock)) {
                $remainStock = min($remainStock, $this->intervalStock);
            }

            $this->remainStock = $remainStock;
        }

        return $this->remainStock;
    }

    /**
     * 前部インターバル部分で予約可能な残り在庫を取得
     *
     * @return int
     */
    public function getRemainStockForPreviousInterval()
    {
        return $this->getAllStock() - $this->getReservedStock() - $this->reservedIntervalStock['previous'];
    }

    /**
     * 後部インターバル部分で予約可能な残り在庫を取得
     *
     * @return int
     */
    public function getRemainStockForNextInterval()
    {
        return $this->getAllStock() - $this->getReservedStock() - $this->reservedIntervalStock['next'];
    }

    /**
     * 会員の予約存在判定を設定
     *
     * @param bool $existsUserReservation 存在判定
     * @return void
     */
    public function setExistsUserReservation(bool $existsUserReservation)
    {
        $this->existsUserReservation = $existsUserReservation;
    }

    /**
     * 連続予約の存在判定を設定
     *
     * @param bool $existsContinuousData 存在判定
     * @return void
     */
    public function setExistsContinuousData(bool $existsContinuousData)
    {
        $this->existsContinuousData = $existsContinuousData;
    }

    /**
     * カラーチップを取得
     *
     * @return array
     */
    public function getColorChip()
    {
        if (!isset($this->colorChip)) {
            /** @var \App\Model\Table\ColorChipsTable $colorChipsTable */
            $colorChipsTable = $this->getTableLocator()->get('ColorChips');

            $colorChip = null;
            if (
                $this->getEvent()->usesColorChip(ColorChip::TYPE_CONTINUOUS, $this->isAdmin())
                && $this->existsContinuousData
            ) {
                // 連続予約は「予約中」のカラー
                $colorChip = $colorChipsTable->getDataByDefaultType(ColorChip::TYPE_CONTINUOUS);
            } elseif (
                $this->getEvent()->usesColorChip(ColorChip::TYPE_RESERVED, $this->isAdmin())
                && $this->existsUserReservation
            ) {
                // 会員の予約が存在する場合は「予約済み」のカラー
                $colorChip = $colorChipsTable->getDataByDefaultType(ColorChip::TYPE_RESERVED);
            } elseif ($this->isHoliday()) {
                // 休日は「受付期間外」のカラー
                $colorChip = $colorChipsTable->getDataByDefaultType(ColorChip::TYPE_TERM_END);
            } elseif (
                $this->getEvent()->usesColorChip(ColorChip::TYPE_TERM_END, $this->isAdmin())
                && (!$this->isWithinReceptionPeriod() || !$this->isWithinRegistrationDeadline())
            ) {
                // 受付期間、登録締切の期間外は「受付期間外」のカラー
                $colorChip = $colorChipsTable->getDataByDefaultType(ColorChip::TYPE_TERM_END);
            } elseif (
                $this->getEvent()->usesColorChip(ColorChip::TYPE_FULL, $this->isAdmin())
                && $this->getRemainStock() <= 0
            ) {
                // 在庫がない場合は「空きなし」のカラー
                $colorChip = $colorChipsTable->getDataByDefaultType(ColorChip::TYPE_FULL);
            } elseif ($this->getEvent()->usesColorChip(ColorChip::TYPE_ADD, $this->isAdmin())) {
                // 背景色利用の場合は指定されたカラー
                $colorChip = $colorChipsTable->getDataById($this->getEvent()->get('color_chip_id'));
            } else {
                // その他の場合は「空きあり」のカラー
                $colorChip = $colorChipsTable->getDataByDefaultType(ColorChip::TYPE_EMPTY);
            }

            $this->colorChip = $colorChip;
        }

        return $this->colorChip;
    }

    /**
     * 予約件数を取得
     *
     * @param int $statusType ステータスタイプ
     * @param int $keepStockFlg 在庫確保フラグ
     * @return int
     */
    public function getReservationCount(int $statusType, int $keepStockFlg)
    {
        return Hash::get($this->reservationCount, $statusType . '.' . $keepStockFlg, 0);
    }

    /**
     * 予約件数を加算
     *
     * @param int $statusType ステータスタイプ
     * @param int $keepStockFlg 在庫確保フラグ
     * @param int $reservationCount 予約件数
     * @return void
     */
    public function addReservationCount(int $statusType, int $keepStockFlg, int $reservationCount)
    {
        if (!isset($this->reservationCount[$statusType][$keepStockFlg])) {
            $this->reservationCount[$statusType][$keepStockFlg] = 0;
        }
        $this->reservationCount[$statusType][$keepStockFlg] += $reservationCount;
    }

    /**
     * 予約データを取得
     *
     * @return mixed
     */
    public function getReservationData()
    {
        return $this->reservationData;
    }

    /**
     * 予約データを設定
     *
     * @param mixed $reservationData 予約データ
     * @return void
     */
    public function setReservationData($reservationData)
    {
        $this->reservationData = $reservationData;
    }

    /**
     * 表示可否の判定
     *
     * @return bool
     */
    public function canDisplay()
    {
        if ($this->getAllStock() === 0) {
            return false;
        }

        if (!$this->isAdmin()) {
            /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
            $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
            if (
                $this->isHoliday()
                || (
                    !$siteSettingsTable->getData()->isCalendarRegistrationDeadlineDisplay()
                    && !$this->isWithinRegistrationDeadline()
                )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * 予約可否の判定
     *
     * @return bool
     */
    public function canReserve()
    {
        if ($this->getRemainStock() <= 0) {
            return false;
        }
        if (!$this->isAdmin() && (!$this->isWithinReceptionPeriod() || !$this->isWithinRegistrationDeadline())) {
            return false;
        }

        return true;
    }

    /**
     * 在庫記号利用の判定
     *
     * @return bool
     */
    public function usesEventStockMark()
    {
        if (((string)$this->getEvent()->get('stock_display_type')) !== ((string)Event::STOCK_DISPLAY_TYPE_ICON)) {
            return false;
        }

        return true;
    }

    /**
     * 在庫記号を取得
     *
     * @return int|null
     */
    public function getEventStockMark()
    {
        return $this->getEvent()->getEventStockMark($this->getRemainStock());
    }

    /**
     * 予約数を加算
     *
     * @param int $number 予約数
     * @return void
     */
    public function addReservedStock($number)
    {
        $this->reservedStock = $this->getReservedStock() + $number;

        $this->remainStock = null;
        $this->colorChip = null;
    }

    /**
     * 前部インターバル時間の予約数を加算
     *
     * @param int $number 予約数
     * @return void
     */
    public function addReservedPreviousIntervalStock($number)
    {
        $this->reservedIntervalStock['previous'] += $number;

        $this->remainStock = null;
        $this->colorChip = null;
    }

    /**
     * 後部インターバル時間の予約数を加算
     *
     * @param int $number 予約数
     * @return void
     */
    public function addReservedNextIntervalStock($number)
    {
        $this->reservedIntervalStock['next'] += $number;

        $this->remainStock = null;
        $this->colorChip = null;
    }

    /**
     * 最小利用時間での予約可否の判定
     *
     * @return bool
     */
    public function canReserveUnitTime()
    {
        // 最小利用時間
        $unitUsageTimestamp = $this->getEvent()->getUnitTimeUsageTimestamp($this->getDateTimeFrom());
        if (!isset($unitUsageTimestamp)) {
            return false;
        }

        // 予約枠の曜日設定
        $weeks = [];
        $checkHolidays = false;
        $publicHolidays = $this->getEvent()->getPublicHolidays();
        if (!isset($publicHolidays)) {
            /** @var \App\Model\Table\HolidaysTable $holidaysTable */
            $holidaysTable = $this->getTableLocator()->get('Holidays');

            $publicHolidays = $holidaysTable->getHolidaysByYear(
                (int)$this->getDateTimeFrom()->format('Y'),
                (int)$unitUsageTimestamp->format('Y')
            );
        }
        foreach ((array)$this->getEvent()->get('event_weeks') as $eventWeek) {
            if ((string)$eventWeek->get('week') !== ((string)Event::WEEK_HOLIDAY)) {
                $weeks[] = $eventWeek->get('week');
            } else {
                $checkHolidays = true;
            }
        }

        $usageTimestampFrom = new FrozenTime($this->getDateTimeFrom()->format('Y-m-d H:i:s'));
        $interval = DAY;
        if (((string)$this->getEvent()->get('type')) === ((string)Event::TYPE_TIME)) {
            $interval = (int)$this->getEvent()->get('event_unit_time') * MINUTE;
        }
        while ($usageTimestampFrom < $unitUsageTimestamp) {
            $usageTimestampTo = $this->getEvent()->getUsageTimestampTo($usageTimestampFrom);

            // 実施期間の判定
            if (
                !DateTimeUtility::isWithinDateTime(
                    $usageTimestampFrom,
                    $usageTimestampTo,
                    $this->getEvent()->getDateTimeFrom(),
                    $this->getEvent()->getDateTimeTo()
                )
            ) {
                return false;
            }

            // 実施時間の判定
            if (((string)$this->getEvent()->get('type')) === ((string)Event::TYPE_TIME)) {
                if (
                    !DateTimeUtility::isWithinTime(
                        $usageTimestampFrom,
                        $usageTimestampTo,
                        $this->getEvent()->getTimeFrom(),
                        $this->getEvent()->getTimeTo()
                    )
                ) {
                    return false;
                }
            }

            // 利用曜日の判定
            $usageDate = $this->getEvent()->getUsageDate($usageTimestampFrom);
            if (!DateTimeUtility::isWithinWeekHoliday($usageDate, $weeks, $checkHolidays, $publicHolidays)) {
                return false;
            }

            $usageTimestampFrom = $usageTimestampFrom->addSeconds($interval);
        }

        return true;
    }

    /**
     * キャンセル待ち通知の判定
     *
     * @return bool
     */
    public function isWaitingCancellation()
    {
        if (
            ((string)$this->getEvent()->get('waiting_cancellation_flg')) !== ((string)Event::COMMON_FLG_ON)
            || !$this->isWithinReceptionPeriod()
            || !$this->isWithinRegistrationDeadline()
            || $this->getRemainStock() > 0
        ) {
            return false;
        }

        return true;
    }

    /**
     * カレンダーの時間帯に重複するか判定
     *
     * @return bool
     */
    public function isOverlapCalendarTimeRange()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        if (
            !$siteSettingsTable->getData()->isOverlapCalendarTimeRange(
                $this->getDateTimeFrom(),
                $this->getDateTimeTo()
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     * 枠として存在する開始日時の判定
     *
     * @return bool
     */
    public function existsUsageTimestampFrom()
    {
        if (!$this->getEvent()->existsUsageTimestampFrom($this->getDateTimeFrom())) {
            return false;
        }

        return true;
    }

    /**
     * インターバルの在庫を設定
     *
     * @param int $stock 在庫
     * @return void
     */
    public function setIntervalStock($stock)
    {
        if (!isset($this->intervalStock)) {
            $this->intervalStock = $stock;
        }
        $this->intervalStock = min($stock, $this->intervalStock);
    }

    /**
     * カレンダーに表示する残り在庫を取得
     *
     * @return int
     */
    public function getDisplayRemainStock()
    {
        $displayRemainStock = $this->getRemainStock();
        if ($displayRemainStock < 0) {
            $displayRemainStock = 0;
        }

        return $displayRemainStock;
    }
}
