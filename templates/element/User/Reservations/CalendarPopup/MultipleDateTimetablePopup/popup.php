<div data-title="<?= $this->Tr->h('calendar/multipleDateTimetablePopupTitle') ?>"
     data-popup-width="400"
     data-popup-type="<?= h($calendarPopup->getPopupType()) ?>"
     data-search-data="<?= h(json_encode($calendarPopup->getPopupSearchData())) ?>">
    <h4 class="ttl-popup">
        <?php if ($this->Authority->isAuthority(true, 'Events', 'view')): ?>
            <a href="#" class="js_show_event_popup"
               data-title="<?= h($calendarPopup->getEvent()->get('name')) ?>"
               data-url="<?= $this->Url->build([
                   'prefix' => 'User',
                   'controller' => 'Events',
                   'action' => 'view',
                   'id' => $calendarPopup->getEvent()->get('id'),
                   '?' => [
                       'frame' => $this->Configure->read('Master.common.flg.on'),
                   ],
               ]) ?>">
                <?php if ($calendarPopup->getEvent()->has('label')): ?>
                    <span class="name-label">[<?= h($calendarPopup->getEvent()->get('label')->get('name')) ?>]</span>
                <?php endif; ?>
                <span class="name-detail"><?= h($calendarPopup->getEvent()->get('name')) ?></span>
            </a>
        <?php else: ?>
            <span>
            <?php if ($calendarPopup->getEvent()->has('label')): ?>
                <span class="name-label">[<?= h($calendarPopup->getEvent()->get('label')->get('name')) ?>]</span>
            <?php endif; ?>
                <span class="name-detail"><?= h($calendarPopup->getEvent()->get('name')) ?></span>
            </span>
        <?php endif; ?>
    </h4>
    <div class="date-wrap clearfix">
        <div class="select-day f-l">
            <?= $this->Form->button('', [
                'type' => 'button',
                'id' => 'prev',
                'class' => ['btn-calender', 'is-prev', 'js_change_popup_date'],
                'data-date' => $calendarPopup->getDatePrevious()->format('Y/m/d'),
            ]) ?>
            <button type="button" class="current-day js-datepicker tooltip js_date_select"
                    title="<?= $this->Tr->h('reservation/calendar/dateBtn') ?>" data-toggle
                    value="<?= h($calendarPopup->getDateFrom()) ?>">
                    <span class="icon"><svg class="icon-calendar">
                            <use xlink:href="#icon_calendar"></use>
                        </svg></span>
                <?= h($this->Template->displayDayAndWeek($calendarPopup->getDateFrom())) ?>
            </button>
            <?= $this->Form->button('', [
                'type' => 'button',
                'id' => 'next',
                'class' => ['btn-calender', 'is-next', 'js_change_popup_date'],
                'data-date' => $calendarPopup->getDateNext()->format('Y/m/d'),
            ]) ?>
        </div>
    </div>
    <?= $this->element('User/Reservations/calendar_color_chips', [
        'colorChips' => $calendarPopup->getColorChips(),
    ]) ?>
    <div class="list-typeB">
        <div class="is-listOnly pc-only">
            <div class="reserve_list_head">
                <ul>
                    <li class="h-time">
                        <?= $this->Tr->h('reservation/calendar/listHeaderTime') ?>
                    </li>
                    <li class="h-status">
                        <?= $this->Tr->h('reservation/calendar/listHeaderStock') ?>
                    </li>
                </ul>
            </div>
        </div>
        <div class="reserve_list_body">
            <ul>
                <?php foreach ($calendarPopup->getTimetable()->getTimetable() as $eventUnit): ?>
                    <li class="list_body_line_wrap clearfix js_apply_style"
                        data-style="<?= h(json_encode($calendarPopup->createCss($eventUnit))) ?>">
                        <ul class="list_body_line">
                            <li class="b-time">
                                <?= h($eventUnit->getDateTimeFrom()->format('H:i')) ?> ~
                            </li>
                            <li class="b-status">
                                <a
                                    href="#"
                                    class="<?= h(implode(' ', $calendarPopup->getUnitHtmlClass($eventUnit, $selectCalendar))) ?> "
                                    <?php if (!$selectCalendar): ?>
                                        data-url="<?= $this->Url->build($calendarPopup->getReservationUrl($eventUnit)) ?>"
                                    <?php else: ?>
                                        data-calendar-data="<?= h(json_encode($calendarPopup->getSelectCalendarData($eventUnit))) ?>"
                                    <?php endif; ?>
                                    data-event-id="<?= h($eventUnit->getEvent()->get('id')) ?>"
                                    data-usage-timestamp="<?= h($eventUnit->getDateTimeFrom()->format('Y/m/d H:i')) ?>"
                                >
                                    <?php if (!$eventUnit->usesEventStockMark() && !$eventUnit->isWaitingCancellation()): ?>
                                        <span>
                                            <?= $this->Tr->h('reservation/calendar/remainStock') ?><?= h($eventUnit->getDisplayRemainStock()) ?><?= h($eventUnit->getEvent()->get('stock_unit')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="span_dispaly_icon">
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
