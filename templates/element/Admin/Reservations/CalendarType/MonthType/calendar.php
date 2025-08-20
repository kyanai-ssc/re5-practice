<?php
use App\Model\EventCalendar\AbstractCalendarPopup;
?>
<section class="calendar-wrap">
    <?= $this->element('Admin/Reservations/calendar_head', [
        'calendar' => $calendar,
        'valueOptions' => $valueOptions,
        'currentDate' =>  $this->Template->displayDayAndWeek($calendar->getDateFrom()),
        'showPager' => false,
        'horizontalScroll' => false,
    ]) ?>
    <div id="calender_list">
        <div class="colomun_right">
            <table class="month_table">
                <thead class="stickyTable">
                    <tr>
                        <?php foreach ($calendar->getCalendarWeeks() as $week): ?>
                            <th class="sticky">
                                <span><?= h($week) ?></span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($calendar->getCalendarDates() as $dates): ?>
                        <tr>
                            <?php foreach ($dates as $date): ?>
                                <td class="<?= h(implode(' ', $calendar->getDateClass($date))) ?>">
                                    <?php if ($calendar->isDisplayDate($date)): ?>
                                        <span class="date"><?= h($date->format('d')) ?></span>
                                        <?php foreach ($calendar->getTimetable($date) as $eventTimetable): ?>
                                            <div
                                                class="calender_list <?= h(implode(' ', $calendar->getTimetableHtmlClass($eventTimetable, $selectCalendar))) ?> js_apply_style"
                                                <?php if (!$calendar->isListDisplay($eventTimetable)): ?>
                                                    <?php if (!$selectCalendar): ?>
                                                        data-search-data="<?= h(json_encode($calendar->getDetailSearchData($eventTimetable->getFirstUnit()))) ?>"
                                                    <?php else: ?>
                                                        data-calendar-data="<?= h(json_encode($calendar->getSelectCalendarData($eventTimetable->getFirstUnit()))) ?>"
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    data-popup-type="<?= h(AbstractCalendarPopup::TYPE_SINGLE_DATE_TIMETABLE) ?>"
                                                    data-search-data="<?= h(json_encode($calendar->getTimetablePopupSearchData($date, $eventTimetable))) ?>"
                                                <?php endif; ?>
                                                data-style="<?= h(json_encode($calendar->createCss($eventTimetable))) ?>"
                                            >
                                                <dl>
                                                    <dt>
                                                        <span><?= h($eventTimetable->getEvent()->get('name')) ?></span>
                                                    </dt>
                                                    <?php if (!$calendar->isListDisplay($eventTimetable)): ?>
                                                        <dd>
                                                            <?= $this->element('Admin/Reservations/calendar_unit_data', [
                                                                'calendar' => $calendar,
                                                                'eventUnit' => $eventTimetable->getFirstUnit(),
                                                            ]) ?>
                                                        </dd>
                                                    <?php endif; ?>
                                                </dl>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if ($calendar->existsOtherEvents($date)): ?>
                                            <div
                                                class="other_info js_show_calendar_popup"
                                                data-popup-type="<?= h(AbstractCalendarPopup::TYPE_EVENT_LIST) ?>"
                                                data-search-data="<?= h(json_encode($calendar->getEventListPopupSearchData($date))) ?>"
                                            >
                                                全て見る
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<div class="hidden">
    <input type="hidden" class="js_calendar_trigger" value="cal_month_trigger"/>
</div>
