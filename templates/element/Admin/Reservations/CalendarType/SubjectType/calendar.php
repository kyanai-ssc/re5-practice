<?php
use App\Model\EventCalendar\AbstractCalendarPopup;
?>
<section class="calendar-wrap mgt-20">
    <?= $this->element('Admin/Reservations/calendar_head', [
        'calendar' => $calendar,
        'valueOptions' => $valueOptions,
        'currentDate' => $this->Template->displayDayAndWeek($calendar->getDateFrom()),
        'showPager' => false,
        'horizontalScroll' => false,
    ]) ?>
    <div id="calender_list" class="<?php if (!$calendar->hasTimetable()): ?>list_none<?php endif; ?>">
        <table class="subject_table <?php if ($calendar->getCalendarPeriod() > 1): ?>type_week<?php else: ?>type_day<?php endif; ?>">
            <thead class="js_timetable_header">
                <tr class="stickyTable">
                    <th class="sticky">
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
                                <div class="<?php if ($calendar->isCurrentTime($time)): ?>present_time<?php endif; ?><?php if ($calendar->isScrollTime($time)): ?> js_calendar_time_scroll<?php endif; ?>">
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
                                                        data-search-data="<?= h(json_encode($calendar->getDetailSearchData($eventUnit))) ?>"
                                                    <?php else: ?>
                                                        data-calendar-data="<?= h(json_encode($calendar->getSelectCalendarData($eventUnit))) ?>"
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    data-popup-type="<?= h(AbstractCalendarPopup::TYPE_EVENT_LIST) ?>"
                                                    data-search-data="<?= h(json_encode($calendar->getEventListPopupSearchData($date))) ?>"
                                                <?php endif; ?>
                                                data-style="<?= h(json_encode($calendar->createCss($date, $eventUnit))) ?>"
                                            >
                                                <dl>
                                                    <dt>
                                                        <span><?= h($eventUnit->getEvent()->get('name')) ?></span>
                                                    </dt>
                                                    <dd>
                                                        <?= $this->element('Admin/Reservations/calendar_unit_data', [
                                                            'calendar' => $calendar,
                                                            'eventUnit' => $eventUnit,
                                                        ]) ?>
                                                    </dd>
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
                表示する予約枠数が多いため表示できません。<br/>
                表示する予約枠を絞り込んでご確認ください。
            <?php else: ?>
                該当する期間に予約枠は見つかりません
            <?php endif; ?>
        </p>
    <?php endif; ?>
</section>
<div class="hidden">
    <?php if ($calendar->getCalendarPeriod() > 1): ?>
        <input type="hidden" class="js_calendar_trigger" value="cal_week_trigger"/>
    <?php else: ?>
        <input type="hidden" class="js_calendar_trigger" value="cal_day_trigger"/>
    <?php endif; ?>
</div>
