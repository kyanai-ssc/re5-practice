<?php $this->start('calendarDayBody'); ?>
    <?php if ($calendar->hasTimetable()): ?>
        <?= $this->element('User/Reservations/CalendarType/DayType/calendar_body', [
            'calendar' => $calendar,
        ]) ?>
    <?php endif; ?>
<?php $this->end('calendarDayBody'); ?>
<?php if (!isset($calendarPage) || !$calendarPage): ?>
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
            <div>
                <table id="timeTable" class="dayTable type_day <?php if ($calendar->getCalendarPeriod() > 1): ?>type_week<?php else: ?>timeTable<?php endif; ?>">
                    <thead>
                        <tr class="stickyTable">
                            <th class="emptyTD sticky">
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
        </div>
        <?php if (!$calendar->hasTimetable()): ?>
            <p class="txt_list_none">
                <?= $this->Tr->nl2br('reservation/calendar/noResult') ?>
            </p>
        <?php endif; ?>
    </section>
    <div class="hidden">
        <?php if ($calendar->getCalendarPeriod() > 1): ?>
            <input type="hidden" class="js_calendar_trigger" value="re_day_week_trigger"/>
        <?php else: ?>
            <input type="hidden" class="js_calendar_trigger" value="re_day_day_trigger"/>
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
