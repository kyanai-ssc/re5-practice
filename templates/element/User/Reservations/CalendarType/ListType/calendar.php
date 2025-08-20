<?php $this->start('calendarListBody'); ?>
    <?php if ($calendar->hasTimetable()): ?>
        <?= $this->element('User/Reservations/CalendarType/ListType/calendar_body', [
            'calendar' => $calendar,
        ]) ?>
    <?php endif; ?>
<?php $this->end('calendarListBody'); ?>
<?php if (!isset($calendarPage) || !$calendarPage): ?>
    <section class="contents-area l-main l-calendar">
        <?php $this->start('reservationsCalendarListTypeHeader'); ?>
        <div class="pc-only">
            <div class="reserve_list_head">
                <ul>
                    <li class="h-detail">
                        &nbsp;
                    </li>
                    <li class="h-day">
                        <?= $this->Tr->h('reservation/calendar/listHeaderDate') ?>
                    </li>
                    <li class="h-time">
                        <?= $this->Tr->h('reservation/calendar/listHeaderTime') ?>
                    </li>
                    <li class="h-status">
                        <?= $this->Tr->h('reservation/calendar/listHeaderStock') ?>
                    </li>
                </ul>
            </div>
        </div>
        <?php $this->end('reservationsCalendarListTypeHeader'); ?>
        <?= $this->element('User/Reservations/calendar_head', [
            'calendar' => $calendar,
            'valueOptions' => $valueOptions,
            'showDate' => false,
            'showPager' => false,
            'currentDate' => $this->Template->displayDayAndWeek($calendar->getDateFrom()),
            'additionHtml' => $this->fetch('reservationsCalendarListTypeHeader'),
        ]) ?>
        <div class="reserve_list_body" data-list-type-flg="true">
            <ul class="js_calendar_page_container">
                <?= $this->fetch('calendarListBody') ?>
            </ul>
        </div>
        <?php if (!$calendar->hasTimetable()): ?>
            <p class="txt_list_none">
                <?= $this->Tr->nl2br('reservation/calendar/noResult') ?>
            </p>
        <?php endif; ?>
    </section>
    <div class="hidden">
        <input type="hidden" class="js_calendar_trigger" value="re_list_trigger"/>
        <?php if ($this->Paginator->hasNext()): ?>
            <input type="hidden" class="js_calendar_next_page" value="<?= h(json_encode(['page' => $this->Paginator->current() + 1])) ?>" data-scroll="bottom"/>
        <?php endif; ?>
    </div>
<?php else: ?>
    <input type="hidden" class="js_calendar_page_html" value="<?= h($this->fetch('calendarListBody')) ?>" data-selector=".js_calendar_page_container"/>
    <?php if ($this->Paginator->hasNext()): ?>
        <input type="hidden" class="js_calendar_next_page" value="<?= h(json_encode(['page' => $this->Paginator->current() + 1])) ?>" data-scroll="bottom"/>
    <?php endif; ?>
<?php endif; ?>
