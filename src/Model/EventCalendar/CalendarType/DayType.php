<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\CalendarType;

use App\Model\Entity\Event;
use App\Model\EventCalendar\AbstractCalendarType;
use App\Model\EventCalendar\CalendarType\Traits\CalendarDayTrait;
use App\Model\EventCalendar\EventTimetable;
use App\Model\EventCalendar\PaginateTypeInterface;
use App\Model\EventCalendar\Traits\CalendarPeriodTrait;
use App\Model\EventCalendar\Traits\TimetablePopupTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * DayType class.
 */
class DayType extends AbstractCalendarType implements PaginateTypeInterface
{
    use CalendarDayTrait;
    use CalendarPeriodTrait;
    use TimetablePopupTrait;

    public const CALENDAR_PERIOD_1DAY = 1;
    public const CALENDAR_PERIOD_1WEEK = 7;

    public const PAGINATE_LIMIT = 20;

    /**
     * @var array
     */
    protected $timetable = null;

    /**
     * @var array|null
     */
    protected $isListDisplay = null;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $calendarPeriod = [
            Event::CALENDAR_TYPE_DAY_1DAY => static::CALENDAR_PERIOD_1DAY,
            Event::CALENDAR_TYPE_DAY_1WEEK => static::CALENDAR_PERIOD_1WEEK,
        ];

        $this->calendarPeriod = $calendarPeriod[$this->getCalendarType()];
        $this->isListDisplay = [];
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

        // 祝日取得
        $publicHolidays = $this->getPublicHolidays($this->getDateFrom(), $this->getDateTo());

        // タイムテーブルを生成
        $events = [];
        $timetable = [];
        foreach ($this->getEvents() as $event) {
            $event->setPublicHolidays($publicHolidays);

            foreach ($this->getCalendarDate() as $date) {
                $dateTimeFrom = new FrozenTime($date->format('Y-m-d'));
                $dateTimeTo = clone $dateTimeFrom;
                $dateTimeTo = $dateTimeTo->addDays(1);

                $eventTimetable = new EventTimetable($event, $dateTimeFrom, $dateTimeTo, $this->isAdmin());
                $eventTimetable->setLimitDisplayTime($this->limitDisplayTime);
                $eventTimetable->setLimitDisplayable(true);
                $eventTimetable->setExcludeOverday(true);
                if ($eventTimetable->hasTimetable()) {
                    $events[$event->get('id')] = $event;
                    $timetable[$event->get('id')][$dateTimeFrom->format('Y-m-d')] = $eventTimetable;
                }
            }
        }

        // 在庫計算
        $checkStockFrom = new FrozenTime($this->dateFrom->format('Y-m-d'));
        $checkStockTo = new FrozenTime($this->dateTo->format('Y-m-d'));
        $checkStockTo = $checkStockTo->addDays(1);
        $checkStockTimetable = [];
        foreach (Hash::flatten($timetable) as $eventTimetable) {
            if (!$this->isListDisplay($eventTimetable)) {
                $checkStockTimetable[] = $eventTimetable;
            }
        }
        $this->applyReservations($checkStockTimetable, $checkStockFrom, $checkStockTo);

        // 予約データ設定
        $this->applyAdminCalendarData($checkStockTimetable, $checkStockFrom, $checkStockTo);

        $this->events = $events;
        $this->timetable = $timetable;
    }

    /**
     * @inheritDoc
     */
    public function hasTimetable()
    {
        if (!empty($this->timetable)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function getColorChipIds(): array
    {
        $colorChipIds = [];
        foreach ($this->timetable as $timetableByEvent) {
            foreach ($timetableByEvent as $eventTimetable) {
                if (!$this->isListDisplay($eventTimetable)) {
                    $eventUnit = $eventTimetable->getFirstUnit();
                    if (!isset($eventUnit)) {
                        throw new CakeException();
                    }
                    $colorChip = $eventUnit->getColorChip();
                    $colorChipIds[$colorChip['id']] = $colorChip['id'];
                }
            }
        }

        return $colorChipIds;
    }

    /**
     * @inheritDoc
     */
    public function paginateValueOptions()
    {
        $valueOptions = [
            'sort' => [],
            'direction' => [],
            'limit' => [static::PAGINATE_LIMIT => static::PAGINATE_LIMIT],
        ];

        return $valueOptions;
    }

    /**
     * @inheritDoc
     */
    public function paginateDefaultValues()
    {
        $defaultValues = [
            'sort' => null,
            'direction' => null,
            'limit' => static::PAGINATE_LIMIT,
            'page' => 1,
        ];

        return $defaultValues;
    }

    /**
     * @inheritDoc
     */
    public function paginateMaxLimit()
    {
        return static::PAGINATE_LIMIT;
    }

    /**
     * タイムテーブルを取得
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @param string|\DateTimeInterface $date 日付
     * @return \App\Model\EventCalendar\EventTimetable|null
     */
    public function getTimetable(Event $event, $date)
    {
        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        return Hash::get($this->timetable, $event->get('id') . '.' . $date->format('Y-m-d'));
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
}
