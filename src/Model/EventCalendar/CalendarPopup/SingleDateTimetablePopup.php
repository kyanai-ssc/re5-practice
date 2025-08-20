<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\CalendarPopup;

use App\Model\EventCalendar\AbstractCalendarPopup;
use App\Model\EventCalendar\EventTimetable;
use App\Model\EventCalendar\EventUnit;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;

/**
 * SingleDateTimetablePopup class.
 */
class SingleDateTimetablePopup extends AbstractCalendarPopup
{
    /**
     * @var \App\Model\EventCalendar\EventTimetable
     */
    protected $timetable = null;

    /**
     * @inheritDoc
     */
    public function createTimetable()
    {
        if (!isset($this->event) || !isset($this->date)) {
            throw new CakeException();
        }

        $dateTimeFrom = new FrozenTime($this->date->format('Y-m-d'));
        $dateTimeTo = clone $dateTimeFrom;
        $dateTimeTo = $dateTimeTo->addDays(1);

        // 祝日取得
        $publicHolidays = $this->getPublicHolidays($this->getDate(), $this->getDate());
        $this->event->setPublicHolidays($publicHolidays);

        // タイムテーブルを生成
        $eventTimetable = new EventTimetable($this->event, $dateTimeFrom, $dateTimeTo, $this->isAdmin());
        $eventTimetable->setLimitDisplayTime($this->limitDisplayTime);
        $eventTimetable->setLimitDisplayable(true);

        // 在庫計算
        $this->applyReservations([$eventTimetable], $dateTimeFrom, $dateTimeTo);

        // 予約データ設定
        $this->applyAdminCalendarData([$eventTimetable], $dateTimeFrom, $dateTimeTo);

        $this->timetable = $eventTimetable;
    }

    /**
     * @inheritDoc
     */
    protected function getColorChipIds(): array
    {
        $colorChipIds = [];
        foreach ($this->timetable->getTimetable() as $eventUnit) {
            $colorChip = $eventUnit->getColorChip();
            $colorChipIds[$colorChip['id']] = $colorChip['id'];
        }

        return $colorChipIds;
    }

    /**
     * タイムテーブルを取得
     *
     * @return \App\Model\EventCalendar\EventTimetable
     */
    public function getTimetable()
    {
        return $this->timetable;
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
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @return array
     */
    public function createCss(EventUnit $eventUnit)
    {
        $colorTip = $eventUnit->getColorChip();
        $css = [
            'background-color' => $colorTip['color_code'],
        ];

        return $css;
    }
}
