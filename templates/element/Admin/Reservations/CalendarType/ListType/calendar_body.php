<?php

use App\Model\EventCalendar\AbstractCalendarPopup;
use Cake\I18n\FrozenDate;

?>
<?php foreach ($calendar->getEvents() as $event): ?>
    <?php $eventTimetable = $calendar->getTimetable($event); ?>
    <tr class="js_apply_style" data-style="<?= h(json_encode($calendar->createCss($eventTimetable))) ?>">
        <td class="reserve-list3-col-name">
            <?php if ($event->has('label')): ?>
                [<?= h($event->get('label')->get('name')) ?>]
            <?php endif; ?>
            <?= h($event->get('name')) ?>
        </td>
        <td class="reserve-list3-col-date">
            <?= h($this->Template->displayDayAndWeek($event->get('date_from'))) ?>
            <?php if ($event->has('date_to')): ?>
                <?php if ((new FrozenDate($event->get('date_from')))->format('Y-m-d') !== (new FrozenDate($event->get('date_to')))->format('Y-m-d')): ?>
                    ～<?= h($this->Template->displayDayAndWeek($event->get('date_to'))) ?>
                <?php endif; ?>
            <?php else : ?>
                ～
            <?php endif; ?>
        </td>
        <td class="reserve-list3-col-time">
            <?= h($event->getTimeFrom()->format('H:i')) ?>
            ～
            <?= h($event->getTimeTo()->format('H:i')) ?>
        </td>
        <td class="reserve_baloon">
            <?php if (!$calendar->isListDisplay($eventTimetable)): ?>
                <span
                    class="single_date <?= h(implode(' ', $calendar->getTimetableHtmlClass($eventTimetable, $selectCalendar))) ?>"
                        <?php if (!$selectCalendar): ?>
                            data-search-data="<?= h(json_encode($calendar->getDetailSearchData($eventTimetable->getFirstUnit()))) ?>"
                        <?php else: ?>
                            data-calendar-data="<?= h(json_encode($calendar->getSelectCalendarData($eventTimetable->getFirstUnit()))) ?>"
                        <?php endif; ?>
                >
                        <?= $this->element('Admin/Reservations/calendar_unit_data', [
                            'calendar' => $calendar,
                            'eventUnit' => $eventTimetable->getFirstUnit(),
                        ]) ?>
                    </span>
            <?php else: ?>
                <span
                    class="multiple_date js_show_calendar_popup"
                    data-popup-type="<?= h(AbstractCalendarPopup::TYPE_MULTIPLE_DATE_TIMETABLE) ?>"
                    data-search-data="<?= h(json_encode($calendar->getTimetablePopupSearchData($eventTimetable))) ?>"
                >
                        一覧
                    </span>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
