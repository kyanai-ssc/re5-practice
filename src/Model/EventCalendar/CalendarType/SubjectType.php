<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\CalendarType;

use App\Model\Entity\Event;
use App\Model\EventCalendar\AbstractCalendarType;
use App\Model\EventCalendar\CalendarType\Traits\CalendarTimeTrait;
use App\Model\EventCalendar\EventTimetable;
use App\Model\EventCalendar\EventUnit;
use App\Model\EventCalendar\Traits\CalendarPeriodTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * SubjectType class.
 */
class SubjectType extends AbstractCalendarType
{
    use CalendarPeriodTrait;
    use CalendarTimeTrait;

    public const CALENDAR_PERIOD_1DAY = 1;
    public const CALENDAR_PERIOD_1WEEK = 7;

    public const DISPLAY_LIMIT = 350;

    public const POPUP_OVERLAP_COUNT = 3;

    /**
     * @var bool
     */
    protected $hasTimetable = false;

    /**
     * @var array
     */
    protected $timetable = null;

    /**
     * @var bool
     */
    protected $exceedsLimit = null;

    /**
     * @var array|null
     */
    protected $eventIds = null;

    /**
     * @var array|null
     */
    protected $timetableOverlap = null;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $calendarPeriod = [
            Event::CALENDAR_TYPE_SUBJECT_1DAY => static::CALENDAR_PERIOD_1DAY,
            Event::CALENDAR_TYPE_SUBJECT_1WEEK => static::CALENDAR_PERIOD_1WEEK,
        ];

        $this->calendarPeriod = $calendarPeriod[$this->getCalendarType()];
    }

    /**
     * @inheritDoc
     */
    public function buildEventQuery(Query $query)
    {
        $query->order([
            'Events.time_from' => 'ASC',
            'Events.time_to' => 'ASC',
            'Events.sort_no' => 'ASC',
            'Events.id' => 'ASC',
        ], true);

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function createCalendar()
    {
        if (!isset($this->dateFrom) || !isset($this->dateTo)) {
            throw new CakeException();
        }

        $timetable = [];
        foreach ($this->getCalendarDate() as $date) {
            $timetable[$date->format('Y-m-d')] = [];
        }

        // 祝日取得
        $publicHolidays = $this->getPublicHolidays($this->getDateFrom(), $this->getDateTo());

        // 各日付のタイムテーブルを生成
        $events = [];
        $unitCount = 0;
        foreach ($this->getEvents() as $event) {
            $event->setPublicHolidays($publicHolidays);

            foreach ($this->getCalendarDate() as $date) {
                $dateTimeFrom = new FrozenTime($date->format('Y-m-d'));
                $dateTimeTo = clone $dateTimeFrom;
                $dateTimeTo = $dateTimeTo->addDays(1);

                $eventTimetable = new EventTimetable($event, $dateTimeFrom, $dateTimeTo, $this->isAdmin());
                $eventTimetable->setLimitDisplayTime($this->limitDisplayTime);
                $eventTimetable->setLimitDisplayable(true);
                $eventTimetable->getTimetable();

                if ($eventTimetable->hasTimetable()) {
                    $unitCount += $eventTimetable->getUnitCount();
                    if ($unitCount > static::DISPLAY_LIMIT) {
                        $this->events = [];
                        $this->timetable = [];
                        $this->hasTimetable = false;
                        $this->exceedsLimit = true;

                        return;
                    }

                    $events[$event->get('id')] = $event;
                    $timetable[$date->format('Y-m-d')][$event->get('id')] = $eventTimetable;
                }
            }
        }

        // 在庫計算
        $checkStockFrom = new FrozenTime($this->dateFrom->format('Y-m-d'));
        $checkStockTo = new FrozenTime($this->dateTo->format('Y-m-d'));
        $checkStockTo = $checkStockTo->addDays(1);
        $checkStockTimetable = [];
        foreach ($timetable as $timetableByDate) {
            if (!empty($timetableByDate)) {
                $checkStockTimetable = array_merge($checkStockTimetable, $timetableByDate);
            }
        }
        $this->applyReservations($checkStockTimetable, $checkStockFrom, $checkStockTo);

        // 予約データ設定
        $this->applyAdminCalendarData($checkStockTimetable, $checkStockFrom, $checkStockTo);

        $this->events = $events;
        $this->timetable = $timetable;
        $this->hasTimetable = $unitCount > 0;
        $this->exceedsLimit = false;

        $this->calculateOverlap();
    }

    /**
     * @inheritDoc
     */
    public function hasTimetable()
    {
        return $this->hasTimetable;
    }

    /**
     * @inheritDoc
     */
    protected function getColorChipIds(): array
    {
        return $this->getColorChipIdsByTimetable($this->timetable);
    }

    /**
     * タイムテーブルを取得
     *
     * @param string|\DateTimeInterface $date 日付
     * @return array
     */
    public function getTimetable($date)
    {
        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        return Hash::get($this->timetable, $date->format('Y-m-d'));
    }

    /**
     * 表示制限を超えているか判定
     *
     * @return bool
     */
    public function exceedsLimit()
    {
        return $this->exceedsLimit;
    }

    /**
     * 枠のクラスを取得
     *
     * @param string|\DateTimeInterface $date 日付
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @param bool $selectCalendar カレンダー選択
     * @return array
     */
    public function getUnitHtmlClass($date, EventUnit $eventUnit, bool $selectCalendar = false)
    {
        $class = [];
        if (!$this->hasEventListPopup($date, $eventUnit)) {
            $class += $this->getCommonUnitHtmlClass($eventUnit, $selectCalendar);
            if (!$selectCalendar) {
                if ($this->isAdmin()) {
                    $class[] = 'js_show_calendar_detail';
                } else {
                    $class[] = 'js_make_reservation';
                }
            }
        } else {
            $class += $this->getCommonUnitHtmlClass($eventUnit, false);
            $class[] = 'js_show_calendar_popup';
        }

        return $class;
    }

    /**
     * CSSを生成
     *
     * @param string|\DateTimeInterface $date 日付
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @return array
     */
    public function createCss($date, EventUnit $eventUnit)
    {
        if (is_string($date)) {
            $date = new FrozenDate($date);
        }

        $unitTime = $this->getDateTimeWithinCalendarTime(
            $date,
            $eventUnit->getDateTimeFrom(),
            $eventUnit->getDateTimeTo()
        );
        $eventId = $eventUnit->getEvent()->get('id');
        $unitColumn = $this->getUnitColumn($date, $eventId);
        $colorTip = $eventUnit->getColorChip();

        $css = [
            'top' => $this->calculateUnitTop($unitTime['from']) . 'px',
            'left' => $this->calculateUnitLeft($date, $eventId, $unitColumn) . '%',
            'width' => $this->calculateUnitWidth($date, $eventId) . '%',
            'height' => $this->calculateUnitHeight($unitTime['from'], $unitTime['to']) . 'px',
            'z-index' => $unitColumn,
            'background-color' => $colorTip['color_code'],
        ];

        return $css;
    }

    /**
     * 予約枠一覧ポップアップの判定
     *
     * @param string|\DateTimeInterface $date 日付
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @return bool
     */
    public function hasEventListPopup($date, EventUnit $eventUnit)
    {
        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        if (!isset($this->timetableOverlap)) {
            throw new CakeException();
        }

        $eventId = $eventUnit->getEvent()->get('id');
        if ($this->timetableOverlap[$date->format('Y-m-d')]['count'][$eventId] < static::POPUP_OVERLAP_COUNT) {
            return false;
        }

        return true;
    }

    /**
     * 予約枠一覧ポップアップの検索データを取得
     *
     * @param string|\DateTimeInterface $date 日付
     * @return array
     */
    public function getEventListPopupSearchData($date)
    {
        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        $eventIds = [];
        foreach ($this->timetable[$date->format('Y-m-d')] as $eventTimetable) {
            $eventIds[] = $eventTimetable->getEvent()->get('id');
        }

        $searchData = [
            'id' => $eventIds,
            'date' => $date->format('Y/m/d'),
        ];
        if ($this->isAdmin() && !$this->limitDisplayTime) {
            $searchData['display_all_time'] = Configure::readOrFail('Master.common.flg.on');
        }
        if ($this->isAdmin() && isset($this->displayItem)) {
            $searchData['display_item'] = $this->displayItem;
        }

        return $searchData;
    }

    /**
     * 枠の重なりを計算
     *
     * @return void
     */
    protected function calculateOverlap()
    {
        $timetableOverlap = [];
        foreach ($this->getCalendarDate() as $date) {
            // 各列に配置した枠のY座標を格納
            $table = [];
            // 枠の列番号を格納
            $indexes = [];

            // 枠を配置
            $base = new FrozenTime($date->format('Y-m-d'));
            foreach ($this->getTimetable($date) as $eventTimetable) {
                $firstUnit = $eventTimetable->getFirstUnit();
                $lastUnit = $eventTimetable->getLastUnit();
                if (!isset($firstUnit) || !isset($lastUnit)) {
                    throw new CakeException();
                }

                // 枠の範囲を取得
                $unitTime = $this->getDateTimeWithinCalendarTime(
                    $date,
                    $firstUnit->getDateTimeFrom(),
                    $lastUnit->getDateTimeTo()
                );
                $position = [
                    'from' => $base->diffInSeconds($unitTime['from']),
                    'to' => $base->diffInSeconds($unitTime['to']),
                ];

                // 重複する枠がない列を特定
                $index = 0;
                while (true) {
                    $overlap = false;
                    if (isset($table[$index])) {
                        foreach ($table[$index] as $otherPosition) {
                            if ($position['from'] < $otherPosition['to'] && $position['to'] > $otherPosition['from']) {
                                $overlap = true;
                                break;
                            }
                        }
                    }
                    if (!$overlap) {
                        break;
                    }

                    $index += 1;
                }

                $eventId = $eventTimetable->getEvent()->get('id');
                $table[$index][$eventId] = $position;
                $indexes[$eventId] = $index;
            }

            // 重複する枠を再帰的に取得する処理
            $checkDuplicate = function ($indexes, $targetPosition) use ($table, &$checkDuplicate) {
                $duplicate = [];

                // 対象と重複する枠を取得
                foreach ($indexes as $eventId => $index) {
                    $position = $table[$index][$eventId];
                    if ($targetPosition['from'] < $position['to'] && $targetPosition['to'] > $position['from']) {
                        $duplicate[$eventId] = $index;
                    }
                }

                // 再帰的に取得
                foreach ($duplicate as $eventId => $index) {
                    $indexes = array_diff_key($indexes, $duplicate);
                    $duplicate += call_user_func($checkDuplicate, $indexes, $table[$index][$eventId]);
                }

                return $duplicate;
            };

            // 重複する列数を取得
            $counts = [];
            $remain = $indexes;
            while (!empty($remain)) {
                // チェック対象の枠を1つ設定
                $index = reset($remain);
                $eventId = key($remain);

                // 重複する枠を再帰的に取得
                $duplicate = call_user_func($checkDuplicate, $remain, $table[$index][$eventId]);

                if (!is_array($duplicate) || empty($duplicate)) {
                    throw new CakeException('日またぎの予約枠を時間割表示のカレンダーに表示できない状態です。「RE5DEV-2369」を参照してください。');
                }

                // 列数をセットし残りを処理対象へ
                $count = max($duplicate) + 1;
                foreach ($duplicate as $eventId => $index) {
                    $counts[$eventId] = $count;
                    unset($remain[$eventId]);
                }
            }

            $timetableOverlap[$date->format('Y-m-d')] = [
                'column' => $indexes,
                'count' => $counts,
            ];
        }

        $this->timetableOverlap = $timetableOverlap;
    }

    /**
     * 枠の列を取得
     *
     * @param \DateTimeInterface $date 日付
     * @param int $eventId 予約枠ID
     * @return int
     */
    protected function getUnitColumn($date, $eventId)
    {
        if (!isset($this->timetableOverlap)) {
            throw new CakeException();
        }

        return $this->timetableOverlap[$date->format('Y-m-d')]['column'][$eventId];
    }

    /**
     * 枠の横方向の位置を計算
     *
     * @param \DateTimeInterface $date 日付
     * @param int $eventId 予約枠ID
     * @param int $column 列
     * @return int|float
     */
    protected function calculateUnitLeft($date, $eventId, $column)
    {
        if (!isset($this->timetableOverlap)) {
            throw new CakeException();
        }
        $count = $this->timetableOverlap[$date->format('Y-m-d')]['count'][$eventId];

        $left = 0;
        if ($count > 0) {
            $left = 100 * $column / $count;
        }

        return $left;
    }

    /**
     * 枠の幅を計算
     *
     * @param \DateTimeInterface $date 日付
     * @param int $eventId 予約枠ID
     * @return int|float
     */
    protected function calculateUnitWidth($date, $eventId)
    {
        if (!isset($this->timetableOverlap)) {
            throw new CakeException();
        }
        $count = $this->timetableOverlap[$date->format('Y-m-d')]['count'][$eventId];
        $width = 100 / $count;

        return $width;
    }
}
