<?php
use App\Model\EventCalendar\AbstractCalendarPopup;
?>
<?php foreach ($calendar->getEvents() as $event): ?>
    <tr>
        <td>
            <?= h($event->get('name')) ?>
        </td>
        <?php foreach ($calendar->getCalendarDate() as $date): ?>
            <td>
                <?php $eventTimetable = $calendar->getTimetable($event, $date) ?>
                <?php if (isset($eventTimetable)): ?>
                    <?php if (!$calendar->isListDisplay($eventTimetable)): ?>
                        <div
                            class="calender_list <?= h(implode(' ', $calendar->getTimetableHtmlClass($eventTimetable, $selectCalendar))) ?> js_apply_style"
                            <?php if (!$selectCalendar): ?>
                                data-search-data="<?= h(json_encode($calendar->getDetailSearchData($eventTimetable->getFirstUnit()))) ?>"
                            <?php else: ?>
                                data-calendar-data="<?= h(json_encode($calendar->getSelectCalendarData($eventTimetable->getFirstUnit()))) ?>"
                            <?php endif; ?>
                            data-style="<?= h(json_encode($calendar->createCss($eventTimetable))) ?>"
                        >
                            <dl>
                                <dd>
                                    <?= $this->element('Admin/Reservations/calendar_unit_data', [
                                        'calendar' => $calendar,
                                        'eventUnit' => $eventTimetable->getFirstUnit(),
                                    ]) ?>
                                </dd>
                            </dl>
                        </div>
                    <?php else: ?>
                        <div
                            class="calender_list_day_some js_show_calendar_popup"
                            data-popup-type="<?= h(AbstractCalendarPopup::TYPE_SINGLE_DATE_TIMETABLE) ?>"
                            data-search-data="<?= h(json_encode($calendar->getTimetablePopupSearchData($date, $eventTimetable))) ?>"
                        >
                            <span>一覧表示</span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        <?php endforeach; ?>
    </tr>
<?php endforeach; ?>
