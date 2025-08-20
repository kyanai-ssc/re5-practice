<?php
use App\Model\EventCalendar\AbstractCalendarPopup;
?>
<section class="contents-area l-main l-calendar">
    <?= $this->element('User/Reservations/calendar_head', [
        'calendar' => $calendar,
        'valueOptions' => $valueOptions,
        'showDate' => true,
        'showPager' => false,
        'currentDate' => $this->Template->displayDayAndWeek($calendar->getDateFrom()),
        'additionHtml' => null,
    ]) ?>
    <div id="img_scroll_slide" class="d-flex hidden">
        <svg class="icon"><use xlink:href="#icon_scroll_slide_sp"/></svg>
        <p><?= $this->Tr->nl2br('reservation/calendar/canScroll') ?></p>
    </div>
    <div id="calender_list" class="<?php if (!$calendar->hasTimetable()): ?>list_none<?php endif; ?>">
        <table class="subject_table <?php if ($calendar->getCalendarPeriod() > 1): ?>type_week<?php else: ?>type_day<?php endif; ?>">
            <thead class="js_timetable_header">
                <tr class="stickyTable">
                    <th class="js_empty_cell <?php if ($calendar->getCalendarPeriod() > 1): ?>emptyCell<?php else: ?>sticky<?php endif; ?>">
                    </th>
                    <?php foreach ($calendar->getCalendarDate() as $date): ?>
                        <th class="sticky">
                            <span><?= h($this->Template->displayDayAndWeek($date, null, 'm/d')) ?></span>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($calendar->hasTimetable()): ?>
                    <tr>
                        <td class="column time">
                            <?php foreach ($calendar->getCalendarTimeRange() as $time): ?>
                                <div class="<?php if ($calendar->isScrollTime($time)): ?>js_calendar_time_scroll<?php endif; ?>">
                                    <?= h($time->format('H:i')) ?>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <?php foreach ($calendar->getCalendarDate() as $date): ?>
                            <td class="line time">
                                <div class="layer">
                                    <?php foreach ($calendar->getTimetable($date) as $eventTimetable): ?>
                                        <?php foreach ($eventTimetable->getTimetable() as $eventUnit): ?>
                                            <?php if (!$calendar->isOverlapCalendarTimeRange($eventUnit, $date)) { continue; } ?>
                                            <div
                                                class="calender_list <?= h(implode(' ', $calendar->getUnitHtmlClass($date, $eventUnit, $selectCalendar))) ?> js_apply_style"
                                                <?php if (!$calendar->hasEventListPopup($date, $eventUnit)): ?>
                                                    <?php if (!$selectCalendar): ?>
                                                        data-url="<?= $this->Url->build($calendar->getReservationUrl($eventUnit)) ?>"
                                                    <?php else: ?>
                                                        data-calendar-data="<?= h(json_encode($calendar->getSelectCalendarData($eventUnit))) ?>"
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    data-popup-type="<?= h(AbstractCalendarPopup::TYPE_EVENT_LIST) ?>"
                                                    data-search-data="<?= h(json_encode($calendar->getEventListPopupSearchData($date))) ?>"
                                                <?php endif; ?>
                                                data-event-id="<?= h($eventUnit->getEvent()->get('id')) ?>"
                                                data-usage-timestamp="<?= h($eventUnit->getDateTimeFrom()->format('Y/m/d H:i')) ?>"
                                                data-style="<?= h(json_encode($calendar->createCss($date, $eventUnit))) ?>"
                                            >
                                                <dl>
                                                    <dt>
                                                        <span><?= h($eventUnit->getEvent()->get('name')) ?></span>
                                                    </dt>
                                                    <?php if (!$eventUnit->usesEventStockMark() && !$eventUnit->isWaitingCancellation()): ?>
                                                        <dd>
                                                            <?= $this->Tr->h('reservation/calendar/remainStock') ?><?= h($eventUnit->getDisplayRemainStock()) ?><?= h($eventUnit->getEvent()->get('stock_unit')) ?>
                                                        </dd>
                                                    <?php else: ?>
                                                        <dd class="dd_dispaly_icon">
                                                        </dd>
                                                    <?php endif; ?>
                                                </dl>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>
                                <?php foreach ($calendar->getCalendarTimeRange() as $time): ?>
                                    <div class="ground">
                                    </div>
                                <?php endforeach; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$calendar->hasTimetable()): ?>
        <p class="txt_list_none">
            <?php if ($calendar->exceedsLimit()): ?>
                <?= $this->Tr->nl2br('reservation/calendar/exceedLimit') ?>
            <?php else: ?>
                <?= $this->Tr->nl2br('reservation/calendar/noResult') ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>
</section>
<div class="hidden">
    <?php if ($calendar->getCalendarPeriod() > 1): ?>
        <input type="hidden" class="js_calendar_trigger" value="re_subject_week_trigger"/>
    <?php else: ?>
        <input type="hidden" class="js_calendar_trigger" value="re_subject_day_trigger"/>
    <?php endif; ?>
</div>
