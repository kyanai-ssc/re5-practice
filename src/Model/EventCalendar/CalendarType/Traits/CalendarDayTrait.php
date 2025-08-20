<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\CalendarType\Traits;

use App\Model\Entity\Event;
use App\Model\EventCalendar\EventTimetable;
use Cake\Core\Exception\CakeException;

/**
 * CalendarDay trait.
 */
trait CalendarDayTrait
{
    /**
     * タイムテーブルのクラスを取得
     *
     * @param \App\Model\EventCalendar\EventTimetable $eventTimetable タイムテーブル
     * @param bool $selectCalendar カレンダー選択
     * @return array
     */
    public function getTimetableHtmlClass(EventTimetable $eventTimetable, bool $selectCalendar = false)
    {
        $class = $this->getCommonUnitHtmlClass($eventTimetable->getFirstUnit(), $selectCalendar);
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
     * @param \App\Model\EventCalendar\EventTimetable $eventTimetable タイムテーブル
     * @return array
     */
    public function createCss(EventTimetable $eventTimetable)
    {
        $eventUnit = $eventTimetable->getFirstUnit();
        if (!isset($eventUnit)) {
            throw new CakeException();
        }
        if (
            $this->isListDisplay($eventTimetable)
            && $eventTimetable->getEvent()->get('background_color_type') === Event::BACKGROUND_COLOR_TYPE_DEFAULT
        ) {
            $css = [];
        } else {
            $colorTip = $eventUnit->getColorChip();
            $css = [
                'background-color' => $colorTip['color_code'],
            ];
        }

        return $css;
    }
}
