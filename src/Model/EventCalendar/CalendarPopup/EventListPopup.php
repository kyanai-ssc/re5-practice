<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\CalendarPopup;

use App\Model\Entity\Event;
use App\Model\EventCalendar\AbstractCalendarPopup;
use App\Model\EventCalendar\CalendarType\Traits\CalendarDayTrait;
use App\Model\EventCalendar\EventTimetable;
use App\Model\EventCalendar\Traits\TimetablePopupTrait;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;

/**
 * EventListPopup class.
 */
class EventListPopup extends AbstractCalendarPopup
{
    use CalendarDayTrait;
    use TimetablePopupTrait;

    /**
     * @var bool
     */
    protected $isMultipleEventType = true;

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
        $this->isListDisplay = [];
    }

    /**
     * @inheritDoc
     */
    public function createTimetable()
    {
        if (!isset($this->date)) {
            throw new CakeException();
        }

        $dateTimeFrom = new FrozenTime($this->date->format('Y-m-d'));
        $dateTimeTo = clone $dateTimeFrom;
        $dateTimeTo = $dateTimeTo->addDays(1);

        // 祝日取得
        $publicHolidays = $this->getPublicHolidays($this->getDate(), $this->getDate());

        // タイムテーブルを生成
        $timetable = [];
        foreach ($this->getEvents() as $event) {
            $event->setPublicHolidays($publicHolidays);

            $eventTimetable = new EventTimetable($event, $dateTimeFrom, $dateTimeTo, $this->isAdmin());
            $eventTimetable->setLimitDisplayTime($this->limitDisplayTime);
            $eventTimetable->setLimitDisplayable(true);
            if ((string)$event->get('type') === ((string)Event::TYPE_DAY)) {
                $eventTimetable->setExcludeOverday(true);
            }
            if ($eventTimetable->hasTimetable()) {
                $timetable[$event->get('id')] = $eventTimetable;
            }
        }

        // 在庫計算
        $checkStockTimetable = [];
        foreach ($timetable as $eventTimetable) {
            if (!$this->isListDisplay($eventTimetable)) {
                $checkStockTimetable[] = $eventTimetable;
            }
        }
        $this->applyReservations($checkStockTimetable, $dateTimeFrom, $dateTimeTo);

        // 予約データ設定
        $this->applyAdminCalendarData($checkStockTimetable, $dateTimeFrom, $dateTimeTo);

        $this->timetable = $timetable;
    }

    /**
     * @inheritDoc
     */
    protected function getColorChipIds(): array
    {
        $colorChipIds = [];
        foreach ($this->timetable as $eventTimetable) {
            $eventUnit = $eventTimetable->getFirstUnit();
            if (!isset($eventUnit)) {
                throw new CakeException();
            }
            $colorChip = $eventUnit->getColorChip();
            $colorChipIds[$colorChip['id']] = $colorChip['id'];
        }

        return $colorChipIds;
    }

    /**
     * タイムテーブルを取得
     *
     * @return array
     */
    public function getTimetable()
    {
        return $this->timetable;
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
