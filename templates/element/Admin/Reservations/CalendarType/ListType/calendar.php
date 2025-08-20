<?php $this->start('calendarListBody'); ?>
    <?php if ($calendar->hasTimetable()): ?>
        <?= $this->element('Admin/Reservations/CalendarType/ListType/calendar_body', [
            'calendar' => $calendar,
            'calendarPage' => isset($calendarPage),
        ]) ?>
    <?php endif; ?>
<?php $this->end('calendarListBody'); ?>
<?php if (!isset($calendarPage) || !$calendarPage): ?>
    <section class="calendar-wrap mgt-20">
        <?= $this->element('Admin/Reservations/calendar_head', [
            'calendar' => $calendar,
            'valueOptions' => $valueOptions,
            'currentDate' => $this->Template->displayDayAndWeek($calendar->getDateFrom()) . '～',
            'showPager' => false,
            'horizontalScroll' => false,
        ]) ?>
        <div id="side-primary" class="<?php if (!$calendar->hasTimetable()): ?>list_none<?php endif; ?>">
            <?php if ($calendar->hasTimetable()): ?>
                <table class="table_reserve_list cmn-table" data-list-type-flg="true">
                    <thead>
                    <tr>
                        <th>
                            <span>&nbsp;</span>
                        </th>
                        <th>
                            <span>日付</span>
                        </th>
                        <th>
                            <span>時間</span>
                        </th>
                        <th class="reserve_baloon">
                            <span>空き情報</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="js_calendar_page_container">
                        <?= $this->fetch('calendarListBody') ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php if (!$calendar->hasTimetable()): ?>
            <p class="txt_list_none">該当する期間に予約枠は見つかりません</p>
        <?php endif; ?>
    </section>
    <div class="hidden">
        <input type="hidden" class="js_calendar_trigger" value="cal_list_trigger"/>
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
