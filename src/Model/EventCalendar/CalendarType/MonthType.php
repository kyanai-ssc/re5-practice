<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\CalendarType;

use App\Model\EventCalendar\AbstractCalendarType;
use App\Model\EventCalendar\EventTimetable;
use App\Model\EventCalendar\Traits\TimetablePopupTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * MonthType class.
 */
class MonthType extends AbstractCalendarType
{
    use TimetablePopupTrait;

    /**
     * @var array
     */
    protected $calendar = null;

    /**
     * @var bool|null
     */
    protected $hasTimetable = false;

    /**
     * @var array
     */
    protected $timetable = null;

    /**
     * @var array|null
     */
    protected $isListDisplay = null;

    /**
     * @var mixed
     */
    protected $otherEventIds = null;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->isListDisplay = [];
    }

    /**
     * @inheritDoc
     */
    public function buildEventQuery(Query $query)
    {
        if (!isset($this->dateFrom) || !isset($this->dateTo)) {
            throw new CakeException();
        }

        $query->order([
            'Events.sort_no' => 'ASC',
            'Events.time_from' => 'ASC',
            'Events.time_to' => 'ASC',
            'Events.id' => 'ASC',
        ], true);

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function createCalendar()
    {
        if (!isset($this->date)) {
            throw new CakeException();
        }
        $calendar = DateTimeUtility::createCalendar($this->date);

        $dates = [];
        foreach (Hash::flatten($calendar['dates']) as $date) {
            if ($this->isDisplayDate($date)) {
                $dates[] = $date;
            }
        }

        $timetable = [];
        $otherEventIds = [];
        foreach ($dates as $date) {
            $timetable[$date->format('Y-m-d')] = [];
            $otherEventIds[$date->format('Y-m-d')] = [];
        }

        // 祝日取得
        $publicHolidays = $this->getPublicHolidays($this->getDateFrom(), $this->getDateTo());

        // 1ヶ月のタイムテーブルを生成
        $events = [];
        $displayLimit = $this->getDisplayLimit();
        foreach ($this->getEvents() as $event) {
            $event->setPublicHolidays($publicHolidays);

            foreach ($dates as $date) {
                $dateTimeFrom = new FrozenTime($date->format('Y-m-d'));
                $dateTimeTo = clone $dateTimeFrom;
                $dateTimeTo = $dateTimeTo->addDays(1);

                $eventTimetable = new EventTimetable($event, $dateTimeFrom, $dateTimeTo, $this->isAdmin());
                $eventTimetable->setLimitDisplayTime($this->limitDisplayTime);
                $eventTimetable->setLimitDisplayable(true);
                $eventTimetable->setExcludeOverday(true);

                if (empty($otherEventIds[$date->format('Y-m-d')])) {
                    if ($eventTimetable->hasTimetable()) {
                        if (count($timetable[$date->format('Y-m-d')]) < $displayLimit) {
                            $events[$event->get('id')] = $event;
                            $timetable[$date->format('Y-m-d')][$event->get('id')] = $eventTimetable;
                        } else {
                            $otherEventIds[$date->format('Y-m-d')][] = $event->get('id');
                        }
                    }
                } else {
                    $otherEventIds[$date->format('Y-m-d')][] = $event->get('id');
                }
            }
        }

        // 件数制限のチェック
        foreach ($otherEventIds as $date => $eventIds) {
            if (!empty($eventIds)) {
                foreach ($timetable[$date] as $eventTimetable) {
                    $eventIds[] = $eventTimetable->getEvent()->get('id');
                }
                $otherEventIds[$date] = $eventIds;
                $timetable[$date] = array_slice($timetable[$date], 0, $displayLimit - 1, true);
            }
        }

        // 在庫計算
        $checkStockFrom = new FrozenTime($this->dateFrom->format('Y-m-d'));
        $checkStockTo = new FrozenTime($this->dateTo->format('Y-m-d'));
        $checkStockTo = $checkStockTo->addDays(1);
        $checkStockTimetable = [];
        foreach ($timetable as $timetableByDate) {
            foreach ($timetableByDate as $eventTimetable) {
                $checkStockTimetable[] = $eventTimetable;
            }
        }
        $this->applyReservations($checkStockTimetable, $checkStockFrom, $checkStockTo);

        // 予約データ設定
        $applyAdminCalendarTimetable = [];
        foreach ($timetable as $timetableByDate) {
            foreach ($timetableByDate as $eventTimetable) {
                if (!$this->isListDisplay($eventTimetable)) {
                    $applyAdminCalendarTimetable[] = $eventTimetable;
                }
            }
        }
        $this->applyAdminCalendarData($applyAdminCalendarTimetable, $checkStockFrom, $checkStockTo);

        $this->events = $events;
        $this->calendar = $calendar;
        $this->timetable = $timetable;
        $this->otherEventIds = $otherEventIds;
        $this->hasTimetable = null;
    }

    /**
     * @inheritDoc
     */
    public function hasTimetable()
    {
        if (!isset($this->hasTimetable)) {
            $hasTimetable = false;
            foreach ($this->timetable as $timetableByDate) {
                if (!empty($timetableByDate)) {
                    return true;
                }
            }

            $this->hasTimetable = $hasTimetable;
        }

        return $this->hasTimetable;
    }

    /**
     * @inheritDoc
     */
    protected function setDatePeriod()
    {
        if (!isset($this->date)) {
            $this->dateFrom = null;
            $this->dateTo = null;
            $this->datePrevious = null;
            $this->dateNext = null;

            return;
        }

        $dateFrom = $this->date;
        $dateFrom = $dateFrom->firstOfMonth();
        $dateTo = clone $dateFrom;
        $dateTo = $dateTo->lastOfMonth();
        $datePrevious = clone $dateFrom;
        $datePrevious = $datePrevious->subMonths(1);
        $dateNext = clone $dateTo;
        $dateNext = $dateNext->addDays(1);

        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->datePrevious = $datePrevious;
        $this->dateNext = $dateNext;
    }

    /**
     * @inheritDoc
     */
    protected function getColorChipIds(): array
    {
        $colorChipIds = [];
        foreach ($this->timetable as $timetableByDate) {
            foreach ($timetableByDate as $eventTimetable) {
                $colorChip = $eventTimetable->getColorChip();
                $colorChipIds[$colorChip['id']] = $colorChip['id'];
            }
        }

        return $colorChipIds;
    }

    /**
     * カレンダーの曜日を取得
     *
     * @return array
     */
    public function getCalendarWeeks()
    {
        $weeks = [];
        foreach ($this->calendar['weeks'] as $week) {
            $weeks[$week] = Configure::readOrFail('Master.common.week.' . $week);
        }

        return $weeks;
    }

    /**
     * カレンダーの日付を取得
     *
     * @return array
     */
    public function getCalendarDates()
    {
        return $this->calendar['dates'];
    }

    /**
     * カレンダーの日付表示を判定
     *
     * @param string|\DateTimeInterface $date 日付
     * @return bool
     */
    public function isDisplayDate($date)
    {
        if (!isset($this->date)) {
            throw new CakeException();
        }

        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        if ($date->format('n') !== $this->date->format('n')) {
            return false;
        }

        return true;
    }

    /**
     * 日付のクラスを取得
     *
     * @param string|\DateTimeInterface $date 日付
     * @return array
     */
    public function getDateClass($date)
    {
        if (!isset($this->date)) {
            throw new CakeException();
        }

        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        $class = [];
        if ($date->format('n') !== $this->date->format('n')) {
            $class[] = 'exclude';
        }
        if ($date->isSaturday()) {
            $class[] = 'saturday';
        } elseif ($date->isSunday()) {
            $class[] = 'sunday';
        }

        return $class;
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
     * 一覧表示の判定
     *
     * @param \App\Model\EventCalendar\EventTimetable $eventTimetable タイムテーブル
     * @return bool
     */
    public function isListDisplay(EventTimetable $eventTimetable)
    {
        $event = $eventTimetable->getEvent();
        if (!isset($this->isListDisplay[$event->get('id')])) {
            $this->isListDisplay[$event->get('id')] = !$event->isSingleUnitByDate();
        }

        return $this->isListDisplay[$event->get('id')];
    }

    /**
     * タイムテーブルのクラスを取得
     *
     * @param \App\Model\EventCalendar\EventTimetable $eventTimetable タイムテーブル
     * @param bool $selectCalendar カレンダー選択
     * @return array
     */
    public function getTimetableHtmlClass(EventTimetable $eventTimetable, bool $selectCalendar = false)
    {
        $class = [];
        if (!$this->isListDisplay($eventTimetable)) {
            $class += $this->getCommonUnitHtmlClass($eventTimetable->getFirstUnit(), $selectCalendar);
            if (!$selectCalendar) {
                if ($this->isAdmin()) {
                    $class[] = 'js_show_calendar_detail';
                } else {
                    $class[] = 'js_make_reservation';
                }
            }
        } else {
            $class[] = 'js_show_calendar_popup';
        }

        return $class;
    }

    /**
     * CSSを生成
     *
     * @param \App\Model\EventCalendar\EventTimetable $eventTimetable タイムテーブル
     * @return array
     */
    public function createCss(EventTimetable $eventTimetable)
    {
        $colorTip = (array)$eventTimetable->getColorChip();
        $css = [
            'background-color' => $colorTip['color_code'],
        ];

        return $css;
    }

    /**
     * その他予約枠の存在を判定
     *
     * @param string|\DateTimeInterface $date 日付
     * @return bool
     */
    public function existsOtherEvents($date)
    {
        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        $otherEventIds = Hash::get($this->otherEventIds, $date->format('Y-m-d'));
        if (empty($otherEventIds)) {
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

        $searchData = [
            'id' => Hash::get($this->otherEventIds, $date->format('Y-m-d')),
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
     * 枠の表示制限を取得
     *
     * @return int
     */
    protected function getDisplayLimit()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        return $siteSettingsTable->getData()->get('calendar_month_display_limit');
    }
}
