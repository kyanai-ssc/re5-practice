<?php $this->start('calendarTimeHeadDate'); ?>
    <?= $this->element('Admin/Reservations/CalendarType/TimeType/calendar_head_date', [
        'calendar' => $calendar,
    ]) ?>
<?php $this->end('calendarTimeHeadDate'); ?>
<?php $this->start('calendarTimeHeadName'); ?>
    <?php if ($calendar->hasTimetable()): ?>
        <?= $this->element('Admin/Reservations/CalendarType/TimeType/calendar_head_name', [
            'calendar' => $calendar,
        ]) ?>
    <?php endif; ?>
<?php $this->end('calendarTimeHeadName'); ?>
<?php $this->start('calendarTimeBody'); ?>
    <?php if ($calendar->hasTimetable()): ?>
        <?= $this->element('Admin/Reservations/CalendarType/TimeType/calendar_body', [
            'calendar' => $calendar,
        ]) ?>
    <?php endif; ?>
<?php $this->end('calendarTimeBody'); ?>
<?php if (!isset($calendarPage) || !$calendarPage): ?>
    <section class="calendar-wrap">
        <?= $this->element('Admin/Reservations/calendar_head', [
            'calendar' => $calendar,
            'valueOptions' => $valueOptions,
            'currentDate' => $this->Template->displayDayAndWeek($calendar->getDateFrom()),
            'showPager' => false,
            'horizontalScroll' => true,
        ]) ?>
        <div id="calender_list" class="time_table_wrap<?php if (!$calendar->hasTimetable()): ?> list_none<?php endif; ?>">
            <?php if ($calendar->hasTimetable()): ?>
                <table id="timeTableTime" class="<?php if ($calendar->getCalendarPeriod() === 1): ?>type_day type_time<?php endif; ?>">
                    <tr>
                        <td class="column time">
                            <div class="emptyCell">
                            </div>
                            <?php foreach ($calendar->getCalendarTimeRange() as $time): ?>
                                <div class="<?php if ($calendar->isCurrentTime($time)): ?>present_time<?php endif; ?><?php if ($calendar->isScrollTime($time)): ?> js_calendar_time_scroll<?php endif; ?>">
                                    <?= h($time->format('H:i')) ?>
                                </div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>
            <div class="timeTable-wrap js_calendar_horizontal_scroll">
                <?php if ($calendar->hasTimetable()): ?>
                    <div class="timeTableHead js_timetable_header">
                        <table class="<?php if ($calendar->getCalendarPeriod() === 1): ?>type_day type_time<?php endif; ?>">
                            <thead class="stickyTable">
                                <tr class="js_calendar_page_head_date_container">
                                    <th>
                                    </th>
                                    <?= $this->fetch('calendarTimeHeadDate') ?>
                                </tr>
                                <?php if ($calendar->hasTimetable()): ?>
                                    <tr class="js_calendar_page_head_name_container">
                                        <th>
                                        </th>
                                        <?= $this->fetch('calendarTimeHeadName') ?>
                                    </tr>
                                <?php endif; ?>
                            </thead>
                        </table>
                    </div>
                <?php endif; ?>
                <table class="time_table<?php if ($calendar->getCalendarPeriod() === 1): ?> type_day type_time<?php endif; ?>">
                    <thead>
                        <tr class="js_calendar_page_head_date_container">
                            <th>
                            </th>
                            <?= $this->fetch('calendarTimeHeadDate') ?>
                        </tr>
                        <?php if ($calendar->hasTimetable()): ?>
                            <tr class="js_calendar_page_head_name_container">
                                <th>
                                </th>
                                <?= $this->fetch('calendarTimeHeadName') ?>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <?php if ($calendar->hasTimetable()): ?>
                        <tbody>
                            <tr class="js_calendar_page_body_container">
                                <td class="column time">
                                </td>
                                <?= $this->fetch('calendarTimeBody') ?>
                            </tr>
                        </tbody>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <?php if (!$calendar->hasTimetable()): ?>
            <p class="txt_list_none">該当する期間に予約枠は見つかりません</p>
        <?php endif; ?>
    </section>
    <div class="hidden">
        <input type="hidden" class="js_calendar_trigger" value="cal_time_trigger"/>
        <?php $nextPageParameter = $calendar->getNextPageParameter(); ?>
        <?php if (isset($nextPageParameter)): ?>
            <input type="hidden" class="js_calendar_next_page" value="<?= h(json_encode($nextPageParameter)) ?>" data-scroll="right"/>
        <?php endif; ?>
    </div>
<?php else: ?>
    <input type="hidden" class="js_calendar_page_html" value="<?= h($this->fetch('calendarTimeHeadDate')) ?>" data-selector=".js_calendar_page_head_date_container"/>
    <input type="hidden" class="js_calendar_page_html" value="<?= h($this->fetch('calendarTimeHeadName')) ?>" data-selector=".js_calendar_page_head_name_container"/>
    <input type="hidden" class="js_calendar_page_html" value="<?= h($this->fetch('calendarTimeBody')) ?>" data-selector=".js_calendar_page_body_container"/>
    <?php $nextPageParameter = $calendar->getNextPageParameter(); ?>
    <?php if (isset($nextPageParameter)): ?>
        <input type="hidden" class="js_calendar_next_page" value="<?= h(json_encode($nextPageParameter)) ?>" data-scroll="right"/>
    <?php endif; ?>
<?php endif; ?>
