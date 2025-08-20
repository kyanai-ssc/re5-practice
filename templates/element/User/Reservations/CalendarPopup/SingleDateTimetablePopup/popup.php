<div data-title="<?= $this->Tr->h('calendar/singleDateTimetablePopupTitle') ?>" data-popup-width="400">
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
            <?php if ($calendarPopup->getEvent()->has('label')): ?>
                <span class="name-label">[<?= h($calendarPopup->getEvent()->get('label')->get('name')) ?>]</span>
            <?php endif; ?>
            <span class="name-detail"><?= h($calendarPopup->getEvent()->get('name')) ?></span>
        <?php endif; ?>
    </h4>
    <div class="date-wrap clearfix mgb-10">
        <div class="select-day f-l">
            <span class="current-day"><?= h($this->Template->displayDayAndWeek($calendarPopup->getDate())) ?></span>
        </div>
    </div>
    <?= $this->element('User/Reservations/calendar_color_chips', [
        'colorChips' => $calendarPopup->getColorChips(),
    ]) ?>
    <div class="list-typeC">
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
                                    class="<?= h(implode(' ', $calendarPopup->getUnitHtmlClass($eventUnit, $selectCalendar))) ?>"
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
