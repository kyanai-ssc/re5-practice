<?php

use App\Model\EventCalendar\AbstractCalendarPopup;

?>
<section class="contents-area l-main l-calendar">
    <?= $this->element('User/Reservations/calendar_head', [
        'calendar' => $calendar,
        'valueOptions' => $valueOptions,
        'showDate' => true,
        'showPager' => false,
        'currentDate' => $calendar->getDateFrom()->format('Y/m'),
        'additionHtml' => null,
    ]) ?>
    <div id="calender_list">
        <table class="timeTable month_table">
            <thead>
            <tr class="stickyTable">
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
                                                data-url="<?= $this->Url->build($calendar->getReservationUrl($eventTimetable->getFirstUnit())) ?>"
                                            <?php else: ?>
                                                data-calendar-data="<?= h(json_encode($calendar->getSelectCalendarData($eventTimetable->getFirstUnit()))) ?>"
                                            <?php endif; ?>
                                        <?php else: ?>
                                            data-popup-type="<?= h(AbstractCalendarPopup::TYPE_SINGLE_DATE_TIMETABLE) ?>"
                                            data-search-data="<?= h(json_encode($calendar->getTimetablePopupSearchData($date, $eventTimetable))) ?>"
                                        <?php endif; ?>
                                        data-event-id="<?= h($eventTimetable->getFirstUnit()->getEvent()->get('id')) ?>"
                                        data-usage-timestamp="<?= h($eventTimetable->getFirstUnit()->getDateTimeFrom()->format('Y/m/d H:i')) ?>"
                                        data-style="<?= h(json_encode($calendar->createCss($eventTimetable))) ?>"
                                    >
                                        <dl>
                                            <dt>
                                                <span><?= h($eventTimetable->getEvent()->get('name')) ?></span>
                                            </dt>
                                            <?php if (!$calendar->isListDisplay($eventTimetable)): ?>
                                                <?php if (!$eventTimetable->getFirstUnit()->usesEventStockMark() && !$eventTimetable->getFirstUnit()->isWaitingCancellation()): ?>
                                                    <dd>
                                                        <?= $this->Tr->h('reservation/calendar/remainStock') ?><?= h($eventTimetable->getFirstUnit()->getDisplayRemainStock()) ?><?= h($eventTimetable->getFirstUnit()->getEvent()->get('stock_unit')) ?>
                                                    </dd>
                                                <?php else: ?>
                                                    <dd class="dd_dispaly_icon">
                                                    </dd>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </dl>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($calendar->existsOtherEvents($date)): ?>
                                    <div
                                        class="other_info link-txt js_show_calendar_popup"
                                        data-popup-type="<?= h(AbstractCalendarPopup::TYPE_EVENT_LIST) ?>"
                                        data-search-data="<?= h(json_encode($calendar->getEventListPopupSearchData($date))) ?>"
                                    >
                                        <?= $this->Tr->h('reservation/calendar/otherEventLink') ?>
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
</section>
<div class="hidden">
    <input type="hidden" class="js_calendar_trigger" value="re_month_trigger"/>
</div>
