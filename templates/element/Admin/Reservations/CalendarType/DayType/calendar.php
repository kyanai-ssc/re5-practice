<?php $this->start('calendarDayBody'); ?>
    <?php if ($calendar->hasTimetable()): ?>
        <?= $this->element('Admin/Reservations/CalendarType/DayType/calendar_body', [
            'calendar' => $calendar,
        ]) ?>
    <?php endif; ?>
<?php $this->end('calendarDayBody'); ?>
<?php if (!isset($calendarPage) || !$calendarPage): ?>
    <section class="calendar-wrap">
        <?= $this->element('Admin/Reservations/calendar_head', [
            'calendar' => $calendar,
            'valueOptions' => $valueOptions,
            'currentDate' => $this->Template->displayDayAndWeek($calendar->getDateFrom()),
            'showPager' => false,
            'horizontalScroll' => false,
        ]) ?>
        <div id="calender_list" class="time_table_wrap<?php if (!$calendar->hasTimetable()): ?> list_none<?php endif; ?>">
            <table class="day_table <?php if ($calendar->getCalendarPeriod() > 1): ?>type_week<?php else: ?>type_day<?php endif; ?> js_load_page_table">
                <thead class="stickyTable">
                    <tr>
                        <th class="sticky">
                        </th>
                        <?php foreach ($calendar->getCalendarDate() as $date): ?>
                            <th class="sticky">
                                <span><?= h($this->Template->displayDayAndWeek($date, null, 'm/d')) ?></span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="js_calendar_page_container">
                    <?= $this->fetch('calendarDayBody') ?>
                </tbody>
            </table>
        </div>
        <?php if (!$calendar->hasTimetable()): ?>
            <p class="txt_list_none">該当する期間に予約枠は見つかりません</p>
        <?php endif; ?>
    </section>
    <div class="hidden">
        <?php if ($calendar->getCalendarPeriod() > 1): ?>
            <input type="hidden" class="js_calendar_trigger" value="cal_week_trigger"/>
        <?php else: ?>
            <input type="hidden" class="js_calendar_trigger" value="cal_day_trigger"/>
        <?php endif; ?>
        <?php if ($this->Paginator->hasNext()): ?>
            <input type="hidden" class="js_calendar_next_page" value="<?= h(json_encode(['page' => $this->Paginator->current() + 1])) ?>" data-scroll="bottom"/>
        <?php endif; ?>
    </div>
<?php else: ?>
    <input type="hidden" class="js_calendar_page_html" value="<?= h($this->fetch('calendarDayBody')) ?>" data-selector=".js_calendar_page_container"/>
    <?php if ($this->Paginator->hasNext()): ?>
        <input type="hidden" class="js_calendar_next_page" value="<?= h(json_encode(['page' => $this->Paginator->current() + 1])) ?>" data-scroll="bottom"/>
    <?php endif; ?>
<?php endif; ?>
