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
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use DateInterval;
use DatePeriod;
use Traversable;

/**
 * TimeType class.
 */
class TimeType extends AbstractCalendarType
{
    use CalendarPeriodTrait;
    use CalendarTimeTrait;

    public const CALENDAR_PERIOD_1DAY = 1;
    public const CALENDAR_PERIOD_1WEEK = 7;

    public const DISPLAY_LIMIT = 21;

    /**
     * @var \Cake\I18n\FrozenDate|null
     */
    protected $displayDateTo = null;

    /**
     * @var bool
     */
    protected $hasTimetable = false;

    /**
     * @var array
     */
    protected $timetable = null;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $calendarPeriod = [
            Event::CALENDAR_TYPE_TIME_1DAY => static::CALENDAR_PERIOD_1DAY,
            Event::CALENDAR_TYPE_TIME_1WEEK => static::CALENDAR_PERIOD_1WEEK,
        ];

        $this->calendarPeriod = $calendarPeriod[$this->getCalendarType()];
    }

    /**
     * @inheritDoc
     */
    public function setDate($date)
    {
        parent::setDate($date);

        if (isset($this->searchData['next_date']) && $this->dateFrom && $this->dateTo) {
            $nextDate = new FrozenDate($this->searchData['next_date']);
            if (DateTimeUtility::isWithinDate($nextDate, $nextDate, $this->dateFrom, $this->dateTo)) {
                $this->dateFrom = $nextDate;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function buildEventQuery(Query $query)
    {
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
        if (!isset($this->dateFrom) || !isset($this->dateTo)) {
            throw new CakeException();
        }

        $allEvents = [];
        foreach ($this->getEvents() as $event) {
            $allEvents[] = $event;
        }

        // 祝日取得
        $publicHolidays = $this->getPublicHolidays($this->getDateFrom(), $this->getDateTo());

        // 各日付のタイムテーブルを生成
        $events = [];
        $timetable = [];
        $timetableCount = 0;
        foreach ($this->getCalendarDate() as $date) {
            $timetable[$date->format('Y-m-d')] = [];

            foreach ($allEvents as $event) {
                $event->setPublicHolidays($publicHolidays);

                $dateTimeFrom = new FrozenTime($date->format('Y-m-d'));
                $dateTimeTo = clone $dateTimeFrom;
                $dateTimeTo = $dateTimeTo->addDays(1);

                $eventTimetable = new EventTimetable($event, $dateTimeFrom, $dateTimeTo, $this->isAdmin());
                $eventTimetable->setLimitDisplayTime($this->limitDisplayTime);
                $eventTimetable->setLimitDisplayable(true);
                $eventTimetable->getTimetable();

                if ($eventTimetable->hasTimetable()) {
                    $events[$event->get('id')] = $event;
                    $timetable[$date->format('Y-m-d')][$event->get('id')] = $eventTimetable;
                    $timetableCount += 1;
                }
            }

            if ($timetableCount >= static::DISPLAY_LIMIT && $date < $this->getDateTo()) {
                $this->displayDateTo = new FrozenDate($date->format('Y-m-d'));
                break;
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
        $this->hasTimetable = $timetableCount > 0;
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
     * @inheritDoc
     */
    public function getCalendarDate(): Traversable
    {
        $dateTo = new FrozenDate($this->dateTo->format('Y-m-d'));
        if (isset($this->displayDateTo)) {
            $dateTo = new FrozenDate($this->displayDateTo->format('Y-m-d'));
        }
        $dateTo = $dateTo->addDays(1);

        return new DatePeriod($this->dateFrom, new DateInterval('P1D'), $dateTo);
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

        return Hash::get($this->timetable, $date->format('Y-m-d'), []);
    }

    /**
     * 指定日の枠数を取得
     *
     * @param string|\DateTimeInterface $date 日付
     * @return int
     */
    public function getCountByDate($date)
    {
        return count($this->getTimetable($date));
    }

    /**
     * 次ページのパラメータを取得
     *
     * @return array|null
     */
    public function getNextPageParameter()
    {
        if (!isset($this->displayDateTo)) {
            return null;
        }
        $nextDate = new FrozenDate($this->displayDateTo->format('Y-m-d'));
        $nextDate = $nextDate->addDays(1);

        $parameter = [
            'next_date' => $nextDate->format('Y/m/d'),
        ];

        return $parameter;
    }

    /**
     * 枠のクラスを取得
     *
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @param bool $selectCalendar カレンダー選択
     * @return array
     */
    public function getUnitHtmlClass(EventUnit $eventUnit, bool $selectCalendar = false)
    {
        $class = $this->getCommonUnitHtmlClass($eventUnit, $selectCalendar);
        if (!$selectCalendar) {
            if ($this->isAdmin()) {
                $class[] = 'js_show_calendar_detail';
            } else {
                $class[] = 'js_make_reservation';
            }
        }

        return $class;
    }

    /**
     * CSSを生成
     *
     * @param \DateTimeInterface $date 日付
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @return array
     */
    public function createCss($date, EventUnit $eventUnit)
    {
        $unitTime = $this->getDateTimeWithinCalendarTime(
            $date,
            $eventUnit->getDateTimeFrom(),
            $eventUnit->getDateTimeTo()
        );
        $colorTip = $eventUnit->getColorChip();
        $css = [
            'top' => $this->calculateUnitTop($unitTime['from']) . 'px',
            'height' => $this->calculateUnitHeight($unitTime['from'], $unitTime['to']) . 'px',
            'background-color' => $colorTip['color_code'],
        ];

        return $css;
    }
}
