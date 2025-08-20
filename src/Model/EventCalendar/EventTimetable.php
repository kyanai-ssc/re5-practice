<?php
declare(strict_types=1);

namespace App\Model\EventCalendar;

use App\Model\Entity\ColorChip;
use App\Model\Entity\Event;
use App\Model\Entity\ReservationStatus;
use App\Utility\CommonData\CommonDataTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * EventTimetable class.
 */
class EventTimetable
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
     * @var bool
     */
    protected $limitDisplayTime = false;

    /**
     * @var bool
     */
    protected $limitDisplayable = false;

    /**
     * @var bool
     */
    protected $excludeOverday = false;

    /**
     * @var array|null
     */
    protected $timetable = null;

    /**
     * @var \App\Model\EventCalendar\EventUnit|null
     */
    protected $firstUnit = null;

    /**
     * @var  \App\Model\EventCalendar\EventUnit|null
     */
    protected $lastUnit = null;

    /**
     * @var array|null
     */
    protected $colorChip = null;

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
     * 表示時間の制限を設定
     *
     * @param bool $limitDisplayTime 制限有無
     * @return void
     */
    public function setLimitDisplayTime(bool $limitDisplayTime)
    {
        $this->limitDisplayTime = $limitDisplayTime;
    }

    /**
     * 表示可能枠のみの制限を設定
     *
     * @param bool $limitDisplayable 制限有無
     * @return void
     */
    public function setLimitDisplayable(bool $limitDisplayable)
    {
        $this->limitDisplayable = $limitDisplayable;
    }

    /**
     * 日またぎの除外を設定
     *
     * @param bool $excludeOverday 除外設定
     * @return void
     */
    public function setExcludeOverday(bool $excludeOverday)
    {
        $this->excludeOverday = $excludeOverday;
    }

    /**
     * 枠の存在判定
     *
     * @return bool
     */
    public function hasTimetable()
    {
        $eventUnit = $this->getFirstUnit();
        if (!isset($eventUnit)) {
            return false;
        }

        return true;
    }

    /**
     * タイムテーブルを取得
     *
     * @return array
     */
    public function getTimetable()
    {
        if (!isset($this->timetable)) {
            $timetable = [];
            foreach ($this->createTimetable() as $eventUnit) {
                $timetable[] = $eventUnit;
            }

            $this->timetable = $timetable;
        }

        return $this->timetable;
    }

    /**
     * 最初の枠を取得
     *
     * @return \App\Model\EventCalendar\EventUnit|null
     */
    public function getFirstUnit()
    {
        if (isset($this->timetable)) {
            $this->firstUnit = null;
            if (!empty($this->timetable)) {
                $this->firstUnit = reset($this->timetable);
            }
        } else {
            if (!isset($this->firstUnit)) {
                $firstUnit = null;
                foreach ($this->createTimetable() as $eventUnit) {
                    $firstUnit = $eventUnit;
                    break;
                }
                $this->firstUnit = $firstUnit;
            }
        }

        return $this->firstUnit;
    }

    /**
     * 最後の枠を取得
     *
     * @return \App\Model\EventCalendar\EventUnit|null
     */
    public function getLastUnit()
    {
        if (isset($this->timetable)) {
            $this->lastUnit = null;
            if (!empty($this->timetable)) {
                $lastUnit = array_slice($this->timetable, -1, 1);
                $this->lastUnit = reset($lastUnit);
            }
        } else {
            if (!isset($this->lastUnit)) {
                $lastUnit = null;
                foreach ($this->createTimetable() as $eventUnit) {
                    $lastUnit = $eventUnit;
                }
                $this->lastUnit = $lastUnit;
            }
        }

        return $this->lastUnit;
    }

    /**
     * 枠数を取得
     *
     * @return int
     */
    public function getUnitCount()
    {
        return count($this->getTimetable());
    }

    /**
     * カラーチップを取得
     *
     * @return array|null
     */
    public function getColorChip()
    {
        if (!isset($this->colorChip)) {
            $unitColorChips = [];
            foreach ($this->getTimetable() as $eventUnit) {
                $unitColorChip = $eventUnit->getColorChip();
                $unitColorChips[$unitColorChip['type']] = $unitColorChip;
            }
            if (empty($unitColorChips)) {
                return null;
            }

            // カラーチップの優先順位
            $priority = [
                ColorChip::TYPE_CONTINUOUS,
                ColorChip::TYPE_RESERVED,
                ColorChip::TYPE_ADD,
                ColorChip::TYPE_EMPTY,
                ColorChip::TYPE_FULL,
                ColorChip::TYPE_TERM_END,
            ];

            $colorChip = reset($unitColorChips);
            foreach ($priority as $type) {
                if (isset($unitColorChips[$type])) {
                    $colorChip = $unitColorChips[$type];
                    break;
                }
            }

            $this->colorChip = $colorChip;
        }

        return $this->colorChip;
    }

    /**
     * タイムテーブルを生成
     *
     * @param bool $createNotExists 存在しない枠の生成有無
     * @return \Traversable
     */
    public function createTimetable(bool $createNotExists = false)
    {
        $current = new FrozenTime(
            $this->getDateTimeFrom()->format('Y-m-d') . ' ' . $this->getEvent()->getTimeFrom()->format('H:i:s')
        );

        // 日またぎの考慮
        if (!$this->excludeOverday && $this->getEvent()->getTimeFrom() >= $this->getEvent()->getTimeTo()) {
            $current = $current->subDays(1);
        }

        // 登録締切の日付
        if ($this->limitDisplayable && !$this->isAdmin()) {
            /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
            $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
            if (!$siteSettingsTable->getData()->isCalendarRegistrationDeadlineDisplay()) {
                $registrationDeadline = $this->getEvent()->getRegistrationDeadline();
                $firstRegistrationDeadline = new FrozenTime(
                    $registrationDeadline->format('Y-m-d') . $this->getEvent()->getTimeFrom()->format('H:i:s')
                );
                if ($firstRegistrationDeadline > $registrationDeadline) {
                    $firstRegistrationDeadline = $firstRegistrationDeadline->subDays(1);
                }

                if ($current < $firstRegistrationDeadline) {
                    $current = $firstRegistrationDeadline;
                }
            }
        }

        if ($current < $this->getEvent()->getDateTimeFrom()) {
            $current = $this->getEvent()->getDateTimeFrom();
        }

        $limit = $this->getDateTimeTo();
        if (!is_null($this->getEvent()->getDateTimeTo()) && $limit > $this->getEvent()->getDateTimeTo()) {
            $limit = $this->getEvent()->getDateTimeTo();
        }

        $first = $current;
        $userLoginData = null;
        if (!$this->isAdmin() && $this->commonData()->existsUserLoginData()) {
            /** @var \App\Model\Entity\User $userLoginData */
            $userLoginData = $this->commonData()->getUserLoginData();
        }
        while ($current < $limit) {
            // 枠を生成
            $from = $current;
            $to = $this->getEvent()->getUsageTimestampTo($from);
            // 会員有効期間チェック
            $withinValidPeriod = true;
            if (isset($userLoginData)) {
                $withinValidPeriod = $userLoginData->withinValidPeriod($this->getDateTimeFrom());
            }
            if ($from < $this->getDateTimeTo() && $to > $this->getDateTimeFrom() && $withinValidPeriod) {
                $eventUnit = new EventUnit($this->getEvent(), $from, $to, $this->isAdmin());
                if (
                    (!$this->limitDisplayTime || $eventUnit->isOverlapCalendarTimeRange())
                    && (!$this->limitDisplayable || $eventUnit->canDisplay())
                    && (!$this->excludeOverday || $eventUnit->getDateTimeFrom() >= $this->getDateTimeFrom())
                    && ($createNotExists || $eventUnit->existsUsageTimestampFrom())
                ) {
                    yield $eventUnit;
                }
            }

            // 次の枠の日時を設定
            $current = $to;
            if (!$this->getEvent()->existsUsageTimestampFrom($current)) {
                $first = $first->addDays(1);
                $current = $first;
            }
        }
    }

    /**
     * 予約済みのタイムテーブルを生成
     *
     * @param int|null $excludeReservationId 除外する予約ID
     * @param array|null $reservations 予約中データ
     * @param bool $onlyInRange 範囲内のみチェック
     * @param array|null $waitingCancellation キャンセル待ちデータ
     * @return void
     */
    public function createReservedTimetable(
        ?int $excludeReservationId = null,
        ?array $reservations = null,
        bool $onlyInRange = false,
        ?array $waitingCancellation = null
    ) {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $timetable = [];
        if (isset($this->timetable)) {
            foreach ($this->timetable as $eventUnit) {
                $timetable[$eventUnit->getDateTimeFrom()->format('Y-m-d H:i')] = $eventUnit;
            }
        }

        // 在庫を反映する処理
        $eventStartTime = $this->getEvent()->getTimeFrom()->format('H:i');
        $eventEndTime = $this->getEvent()->getTimeTo()->format('H:i');
        $applyStock = function ($data) use (&$timetable, $eventStartTime, $eventEndTime, $onlyInRange) {
            $usageTimestampFrom = new FrozenTime($data['usage_timestamp_from']);
            $usageTimestampTo = new FrozenTime($data['usage_timestamp_to']);
            $intervalTimeFrom = $this->getEvent()->subIntervalTime($usageTimestampFrom);
            $intervalTimeTo = $this->getEvent()->addIntervalTime($usageTimestampTo);
            $nextIntervalTimeFrom = $usageTimestampTo;
            if ((string)$this->getEvent()->get('type') === (string)Event::TYPE_DAY) {
                $nextIntervalTimeFrom = new FrozenTime(
                    $usageTimestampTo->format('Y-m-d') . ' ' . $usageTimestampFrom->format('H:i')
                );
                if ($nextIntervalTimeFrom < $usageTimestampTo) {
                    $nextIntervalTimeFrom = $nextIntervalTimeFrom->addDays(1);
                }
            }
            $applyTarget = [
                [
                    'from' => $usageTimestampFrom,
                    'to' => $usageTimestampTo,
                    'method' => 'addReservedStock',
                ],
                [
                    'from' => $intervalTimeFrom,
                    'to' => $usageTimestampFrom,
                    'method' => 'addReservedPreviousIntervalStock',
                ],
                [
                    'from' => $nextIntervalTimeFrom,
                    'to' => $intervalTimeTo,
                    'method' => 'addReservedNextIntervalStock',
                ],
            ];

            $timetableFrom = $this->getDateTimeFrom();
            $timetableTo = $this->getDateTimeTo();
            foreach ($applyTarget as $target) {
                $current = $target['from'];
                $limit = $target['to'];
                if ($current->format('H:i') === $eventStartTime) {
                    $first = clone $current;
                } else {
                    $first = new FrozenTime($current->format('Y-m-d') . ' ' . $eventStartTime);
                    if ($first > $current) {
                        $first = $first->subDays(1);
                    }
                }

                while ($current < $limit) {
                    $from = $current;
                    $to = $this->getEvent()->getUsageTimestampTo($from);

                    if (!$onlyInRange || DateTimeUtility::isWithinDateTime($from, $to, $timetableFrom, $timetableTo)) {
                        $key = $current->format('Y-m-d H:i');
                        if (!isset($timetable[$key])) {
                            $timetable[$key] = new EventUnit($this->getEvent(), $from, $to, $this->isAdmin());
                        }

                        /** @var callable $addStock */
                        $addStock = [$timetable[$key], $target['method']];

                        call_user_func($addStock, $data['number']);
                    }

                    $current = $to;
                    if ($from->diffInSeconds($to) < DAY && $to->format('H:i') === $eventEndTime) {
                        $first = $first->addDays(1);
                        $current = $first;
                    }
                }
            }
        };

        // 予約済みの在庫を反映
        $reservedList = $reservationsTable->find('calculateStock', [
            'inputs' => [
                'event_id' => $this->getEvent()->get('id'),
                'usage_timestamp_from' => $this->getDateTimeFrom(),
                'usage_timestamp_to' => $this->getDateTimeTo(),
                'exclude_id' => $excludeReservationId,
            ],
        ]);
        foreach ($reservedList as $data) {
            call_user_func($applyStock, $data);
        }

        // 予約中の在庫を反映
        foreach ((array)$reservations as $reservation) {
            $keepStockFlg = $reservationStatusesTable->getKeepStockFlg($reservation->get('reservation_status_id'));
            if (((string)$keepStockFlg) === ((string)ReservationStatus::KEEP_STOCK_FLG_ON)) {
                call_user_func($applyStock, [
                    'usage_timestamp_from' => $reservation->get('usage_timestamp_from'),
                    'usage_timestamp_to' => $reservation->get('usage_timestamp_to'),
                    'number' => $reservation->get('number'),
                ]);
            }
        }

        // キャンセル待ち分の在庫を反映
        if (!empty($waitingCancellation)) {
            call_user_func($applyStock, [
                'usage_timestamp_from' => $waitingCancellation['usageTimestampFrom'],
                'usage_timestamp_to' => $waitingCancellation['usageTimestampTo'],
                'number' => $waitingCancellation['number'],
            ]);
        }

        $this->timetable = array_values($timetable);
    }

    /**
     * 在庫へ予約を反映
     *
     * @param string|\DateTimeInterface $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface $dateTimeTo 終了日時
     * @param int $number 予約数
     * @return void
     */
    public function applyReservation($dateTimeFrom, $dateTimeTo, int $number)
    {
        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);

        // インターバル時間を含めて範囲チェック
        $intervalTimeFrom = $this->getEvent()->subIntervalTime($dateTimeFrom);
        $intervalTimeTo = $this->getEvent()->addIntervalTime($dateTimeTo);
        if ($this->dateTimeFrom >= $intervalTimeTo || $this->dateTimeTo <= $intervalTimeFrom) {
            return;
        }

        foreach ($this->getTimetable() as $eventUnit) {
            $unitDateTimeFrom = $eventUnit->getDateTimeFrom();
            $unitDateTimeTo = $eventUnit->getDateTimeTo();

            if ($unitDateTimeFrom < $dateTimeTo && $unitDateTimeTo > $dateTimeFrom) {
                $eventUnit->addReservedStock($number);
            } elseif ($unitDateTimeFrom < $dateTimeFrom && $unitDateTimeTo > $intervalTimeFrom) {
                $eventUnit->addReservedPreviousIntervalStock($number);
            } elseif ($unitDateTimeFrom < $intervalTimeTo && $unitDateTimeTo > $dateTimeTo) {
                $eventUnit->addReservedNextIntervalStock($number);
            }
        }
    }

    /**
     * 会員の予約を設定
     *
     * @param string|\DateTimeInterface $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface $dateTimeTo 終了日時
     * @return void
     */
    public function setUserReservation($dateTimeFrom, $dateTimeTo)
    {
        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);

        if ($this->getDateTimeFrom() >= $dateTimeTo || $this->getDateTimeTo() <= $dateTimeFrom) {
            return;
        }

        foreach ($this->getTimetable() as $eventUnit) {
            $unitDateTimeFrom = $eventUnit->getDateTimeFrom();
            $unitDateTimeTo = $eventUnit->getDateTimeTo();

            if ($unitDateTimeFrom < $dateTimeTo && $unitDateTimeTo > $dateTimeFrom) {
                $eventUnit->setExistsUserReservation(true);
            }
        }
    }

    /**
     * 連続予約を設定
     *
     * @param string|\DateTimeInterface $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface $dateTimeTo 終了日時
     * @return void
     */
    public function setContinuousData($dateTimeFrom, $dateTimeTo)
    {
        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);

        if ($this->getDateTimeFrom() >= $dateTimeTo || $this->getDateTimeTo() <= $dateTimeFrom) {
            return;
        }

        foreach ($this->getTimetable() as $eventUnit) {
            $unitDateTimeFrom = $eventUnit->getDateTimeFrom();
            $unitDateTimeTo = $eventUnit->getDateTimeTo();

            if ($unitDateTimeFrom < $dateTimeTo && $unitDateTimeTo > $dateTimeFrom) {
                $eventUnit->setExistsContinuousData(true);
            }
        }
    }

    /**
     * 在庫切れの判定
     *
     * @param bool $includeZero 在庫0を含める
     * @return bool
     */
    public function isOutOfStock($includeZero = true)
    {
        $value = 0;
        if (!$includeZero) {
            $value = -1;
        }
        foreach ($this->getTimetable() as $eventUnit) {
            if ($eventUnit->getRemainStock() <= $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * 予約可否の判定
     *
     * @param string|\DateTimeInterface $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface $dateTimeTo 終了日時
     * @return bool
     */
    public function canReserve($dateTimeFrom, $dateTimeTo)
    {
        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);
        if (!isset($dateTimeFrom) || !isset($dateTimeTo)) {
            throw new CakeException();
        }

        if ($this->getDateTimeFrom() >= $dateTimeTo || $this->getDateTimeTo() <= $dateTimeFrom) {
            return false;
        }
        if (!$this->getEvent()->existsUsageTimestamp($dateTimeFrom, $dateTimeTo)) {
            return false;
        }

        foreach ($this->getTimetable() as $eventUnit) {
            $unitDateTimeFrom = $eventUnit->getDateTimeFrom();
            $unitDateTimeTo = $eventUnit->getDateTimeTo();

            if ($unitDateTimeFrom < $dateTimeTo && $unitDateTimeTo > $dateTimeFrom && !$eventUnit->canReserve()) {
                return false;
            }
        }

        return true;
    }

    /**
     * 予約件数を加算
     *
     * @param string|\DateTimeInterface $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface $dateTimeTo 終了日時
     * @param int $reservationStatusId 予約ステータスID
     * @param int $reservationCount 予約件数
     * @return void
     */
    public function addReservationCount($dateTimeFrom, $dateTimeTo, int $reservationStatusId, int $reservationCount)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);

        if ($this->getDateTimeFrom() >= $dateTimeTo || $this->getDateTimeTo() <= $dateTimeFrom) {
            return;
        }

        $statusType = $reservationStatusesTable->getReservationStatusType($reservationStatusId);
        $keepStockFlg = $reservationStatusesTable->getKeepStockFlg($reservationStatusId);
        foreach ($this->getTimetable() as $eventUnit) {
            $unitDateTimeFrom = $eventUnit->getDateTimeFrom();
            $unitDateTimeTo = $eventUnit->getDateTimeTo();

            if ($unitDateTimeFrom < $dateTimeTo && $unitDateTimeTo > $dateTimeFrom) {
                $eventUnit->addReservationCount($statusType, $keepStockFlg, $reservationCount);
            }
        }
    }

    /**
     * 予約データを設定
     *
     * @param string|\DateTimeInterface $dateTimeFrom 開始日時
     * @param string|\DateTimeInterface $dateTimeTo 終了日時
     * @param mixed $reservationData 予約データ
     * @return void
     */
    public function setReservationData($dateTimeFrom, $dateTimeTo, $reservationData)
    {
        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);

        if ($this->getDateTimeFrom() >= $dateTimeTo || $this->getDateTimeTo() <= $dateTimeFrom) {
            return;
        }

        foreach ($this->getTimetable() as $eventUnit) {
            $unitDateTimeFrom = $eventUnit->getDateTimeFrom();
            $unitDateTimeTo = $eventUnit->getDateTimeTo();

            if ($unitDateTimeFrom < $dateTimeTo && $unitDateTimeTo > $dateTimeFrom) {
                $eventUnit->setReservationData($reservationData);
            }
        }
    }

    /**
     * インターバルの在庫を反映
     *
     * @param int|null $excludeReservationId 除外する予約ID
     * @param array|null $continuousData 予約中データ
     * @return void
     */
    public function applyIntervalStock(?int $excludeReservationId = null, ?array $continuousData = null)
    {
        if (!$this->getEvent()->has('interval_day') && !$this->getEvent()->has('interval_time')) {
            return;
        }

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        // インターバルを含むタイムテーブル
        $intervalTimetable = new EventTimetable(
            $this->getEvent(),
            $this->getEvent()->subIntervalTime($this->getDateTimeFrom()),
            $this->getEvent()->addIntervalTime($this->getDateTimeTo()),
            $this->isAdmin()
        );
        $intervalTimetable->getTimetable();

        // 在庫計算
        $stockReservations = $reservationsTable->find('calculateStock', [
            'inputs' => [
                'event_id' => $intervalTimetable->getEvent()->get('id'),
                'usage_timestamp_from' => $intervalTimetable->getDateTimeFrom(),
                'usage_timestamp_to' => $intervalTimetable->getDateTimeTo(),
                'exclude_id' => $excludeReservationId,
            ],
        ]);
        foreach ($stockReservations as $reservation) {
            $intervalTimetable->applyReservation(
                $reservation['usage_timestamp_from'],
                $reservation['usage_timestamp_to'],
                (int)$reservation['number']
            );
        }
        foreach ((array)$continuousData as $reservation) {
            $intervalTimetable->applyReservation(
                $reservation['usage_timestamp_from'],
                $reservation['usage_timestamp_to'],
                (int)$reservation['number']
            );
        }

        // インターバルの在庫を反映
        foreach ($this->getTimetable() as $eventUnit) {
            $previousFrom = $eventUnit->getEvent()->subIntervalTime($eventUnit->getDateTimeFrom());
            $previousTo = $eventUnit->getDateTimeFrom();
            $nextFrom = $eventUnit->getDateTimeTo();
            $nextTo = $eventUnit->getEvent()->addIntervalTime($eventUnit->getDateTimeTo());

            foreach ($intervalTimetable->getTimetable() as $unit) {
                if ($unit->getDateTimeFrom() < $previousTo && $unit->getDateTimeTo() > $previousFrom) {
                    $eventUnit->setIntervalStock($unit->getRemainStockForPreviousInterval());
                } elseif ($unit->getDateTimeFrom() < $nextTo && $unit->getDateTimeTo() > $nextFrom) {
                    $eventUnit->setIntervalStock($unit->getRemainStockForNextInterval());
                }
            }
        }
    }
}
