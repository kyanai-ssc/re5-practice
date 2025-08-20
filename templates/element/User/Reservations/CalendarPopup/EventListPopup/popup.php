<?php
use App\Model\EventCalendar\AbstractCalendarPopup;
?>
<div data-title="<?= $this->Tr->h('calendar/eventListPopupTitle') ?>" class="js_event_list_popup" data-popup-width="800">
    <h4 class="ttl-popup"><span><?= h($this->Template->displayDayAndWeek($calendarPopup->getDate())) ?></span></h4>
    <?= $this->element('User/Reservations/calendar_color_chips', [
        'colorChips' => $calendarPopup->getColorChips(),
    ]) ?>
    <div class="list-typeA">
        <div class="is-listOnly pc-only">
            <div class="reserve_list_head">
                <ul>
                    <li class="h-detail">
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
        <div class="reserve_list_body">
            <ul>
                <?php foreach ($calendarPopup->getTimetable() as $eventTimetable): ?>
                    <li class="list_body_line_wrap clearfix js_apply_style" data-style="<?= h(json_encode($calendarPopup->createCss($eventTimetable))) ?>">
                        <ul class="list_body_line">
                            <li class="b-detail">
                                <?php if ($this->Authority->isAuthority(true, 'Events', 'view')): ?>
                                    <a href="#" class="js_show_event_popup"
                                       data-title="<?= h($eventTimetable->getEvent()->get('name')) ?>"
                                       data-url="<?= $this->Url->build([
                                        'prefix' => 'User',
                                        'controller' => 'Events',
                                        'action' => 'view',
                                        'id' => $eventTimetable->getEvent()->get('id'),
                                        '?' => [
                                            'frame' => $this->Configure->read('Master.common.flg.on'),
                                        ],
                                    ]) ?>">
                                        <?php if ($eventTimetable->getEvent()->has('label')): ?>
                                            <span class="name-label"><?= h($eventTimetable->getEvent()->get('label')->get('name')) ?></span>
                                        <?php endif; ?>
                                        <span class="name-detail"><?= h($eventTimetable->getEvent()->get('name')) ?></span>
                                    </a>
                                <?php else: ?>
                                    <?php if ($eventTimetable->getEvent()->has('label')): ?>
                                        <span class="name-label"><?= h($eventTimetable->getEvent()->get('label')->get('name')) ?></span>
                                    <?php endif; ?>
                                    <span class="name-detail"><?= h($eventTimetable->getEvent()->get('name')) ?></span>
                                <?php endif; ?>
                            </li>
                            <li class="b-time">
                                <?php if (!$calendarPopup->isListDisplay($eventTimetable)): ?>
                                    <?= h($eventTimetable->getFirstUnit()->getDateTimeFrom()->format('H:i')) ?> ~
                                <?php endif; ?>
                            </li>
                            <li class="b-status">
                                <?php if (!$calendarPopup->isListDisplay($eventTimetable)): ?>
                                    <a
                                        href="#"
                                        class="<?= h(implode(' ', $calendarPopup->getTimetableHtmlClass($eventTimetable, $selectCalendar))) ?>"
                                        <?php if (!$selectCalendar): ?>
                                            data-url="<?= $this->Url->build($calendarPopup->getReservationUrl($eventTimetable->getFirstUnit())) ?>"
                                        <?php else: ?>
                                            data-calendar-data="<?= h(json_encode($calendarPopup->getSelectCalendarData($eventTimetable->getFirstUnit()))) ?>"
                                        <?php endif; ?>
                                        data-event-id="<?= h($eventTimetable->getFirstUnit()->getEvent()->get('id')) ?>"
                                        data-usage-timestamp="<?= h($eventTimetable->getFirstUnit()->getDateTimeFrom()->format('Y/m/d H:i')) ?>"
                                    >
                                        <?php if (!$eventTimetable->getFirstUnit()->usesEventStockMark() && !$eventTimetable->getFirstUnit()->isWaitingCancellation()): ?>
                                            <span>
                                                <?= $this->Tr->h('reservation/calendar/remainStock') ?><?= h($eventTimetable->getFirstUnit()->getDisplayRemainStock()) ?><?= h($eventTimetable->getFirstUnit()->getEvent()->get('stock_unit')) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="span_dispaly_icon">
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                <?php else: ?>
                                    <a
                                        href="#"
                                        class="btn-list js_show_calendar_popup"
                                        data-popup-type="<?= h(AbstractCalendarPopup::TYPE_SINGLE_DATE_TIMETABLE) ?>"
                                        data-search-data="<?= h(json_encode($calendarPopup->getTimetablePopupSearchData($calendarPopup->getDate(), $eventTimetable))) ?>"
                                    >
                                        <span>
                                            <?= $this->Tr->h('reservation/calendar/listIcon') ?>
                                        </span>
                                    </a>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
