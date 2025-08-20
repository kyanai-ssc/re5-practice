<?php $this->start('calendarTimeHeadDate'); ?>
    <?= $this->element('User/Reservations/CalendarType/TimeType/calendar_head_date', [
        'calendar' => $calendar,
    ]) ?>
<?php $this->end('calendarTimeHeadDate'); ?>
<?php $this->start('calendarTimeHeadName'); ?>
    <?php if ($calendar->hasTimetable()): ?>
        <?= $this->element('User/Reservations/CalendarType/TimeType/calendar_head_name', [
            'calendar' => $calendar,
        ]) ?>
    <?php endif; ?>
<?php $this->end('calendarTimeHeadName'); ?>
<?php $this->start('calendarTimeBody'); ?>
    <?php if ($calendar->hasTimetable()): ?>
        <?= $this->element('User/Reservations/CalendarType/TimeType/calendar_body', [
            'calendar' => $calendar,
        ]) ?>
    <?php endif; ?>
<?php $this->end('calendarTimeBody'); ?>
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
            <?php if ($calendar->hasTimetable()): ?>
                <table id="timeTableTime">
                    <tr>
                        <td class="column time">
                            <div class="emptyCell js_empty_cell">
                            </div>
                            <?php foreach ($calendar->getCalendarTimeRange() as $time): ?>
                                <div class="<?php if ($calendar->isScrollTime($time)): ?>js_calendar_time_scroll<?php endif; ?>">
                                    <?= h($time->format('H:i')) ?>
                                </div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>
            <div class="timeTable-wrap js_calendar_horizontal_scroll">
                <?php if ($calendar->hasTimetable()): ?>
                    <div id="timeTableHead" class="js_timetable_header">
                        <table>
                            <thead class="stickyTable">
                                <tr class="js_calendar_page_head_date_container">
                                    <th class="emptyTD">
                                        &nbsp;
                                    </th>
                                    <?= $this->fetch('calendarTimeHeadDate') ?>
                                </tr>
                                <?php if ($calendar->hasTimetable()): ?>
                                    <tr class="js_calendar_page_head_name_container">
                                        <th class="emptyTD">
                                            &nbsp;
                                        </th>
                                        <?= $this->fetch('calendarTimeHeadName') ?>
                                    </tr>
                                <?php endif; ?>
                            </thead>
                        </table>
                    </div>
                <?php endif; ?>
                <table id="timeTable" class="timeTable time">
                    <thead>
                        <tr class="js_calendar_page_head_date_container">
                            <th class="emptyTD">
                                &nbsp;
                            </th>
                            <?= $this->fetch('calendarTimeHeadDate') ?>
                        </tr>
                        <?php if ($calendar->hasTimetable()): ?>
                            <tr class="js_calendar_page_head_name_container">
                                <th class="emptyTD">
                                    &nbsp;
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
            <div class="calender-scroll-btn-wrap">
                <?= $this->Form->button('', [
                    'type' => 'button',
                    'id' => 'left-button',
                    'class' => ['calender-scroll-btn', 'is-prev'],
                ]) ?>
                <?= $this->Form->button('', [
                    'type' => 'button',
                    'id' => 'right-button',
                    'class' => ['calender-scroll-btn', 'is-next'],
                ]) ?>
            </div>
        </div>
        <?php if (!$calendar->hasTimetable()): ?>
            <p class="txt_list_none">
                <?= $this->Tr->nl2br('reservation/calendar/noResult') ?>
            </p>
        <?php endif; ?>
    </section>
    <div class="hidden">
        <input type="hidden" class="js_calendar_trigger" value="re_time_trigger"/>
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
