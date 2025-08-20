<?php

use App\Model\EventCalendar\AbstractCalendarPopup;

?>
<?php foreach ($calendar->getEvents() as $event): ?>
    <tr>
        <td>
            <?php if ($this->Authority->isAuthority(true, 'Events', 'view')): ?>
                <a class="btn-reName js_show_event_popup" href="#" data-title="<?= h($event->get('name')) ?>"
                   data-url="<?= $this->Url->build([
                       'prefix' => 'User',
                       'controller' => 'Events',
                       'action' => 'view',
                       'id' => $event->get('id'),
                       '?' => [
                           'frame' => $this->Configure->read('Master.common.flg.on'),
                       ],
                   ]) ?>">
                    <div class="table_reserve_name">
                        <?= h($event->get('name')) ?>
                    </div>
                </a>
            <?php else: ?>
                <div class="table_reserve_name">
                    <?= h($event->get('name')) ?>
                </div>
            <?php endif; ?>
        </td>
        <?php foreach ($calendar->getCalendarDate() as $date): ?>
            <td class="line time">
                <div class="layer">
                    <?php $eventTimetable = $calendar->getTimetable($event, $date) ?>
                    <?php if (isset($eventTimetable)): ?>
                        <?php if (!$calendar->isListDisplay($eventTimetable)): ?>
                            <div
                                class="calender_list <?= h(implode(' ', $calendar->getTimetableHtmlClass($eventTimetable, $selectCalendar))) ?> js_apply_style"
                                <?php if (!$selectCalendar): ?>
                                    data-url="<?= $this->Url->build($calendar->getReservationUrl($eventTimetable->getFirstUnit())) ?>"
                                <?php else: ?>
                                    data-calendar-data="<?= h(json_encode($calendar->getSelectCalendarData($eventTimetable->getFirstUnit()))) ?>"
                                <?php endif; ?>
                                data-event-id="<?= h($eventTimetable->getFirstUnit()->getEvent()->get('id')) ?>"
                                data-usage-timestamp="<?= h($eventTimetable->getFirstUnit()->getDateTimeFrom()->format('Y/m/d H:i')) ?>"
                                data-style="<?= h(json_encode($calendar->createCss($eventTimetable))) ?>"
                            >
                                <dl>
                                    <?php if (!$eventTimetable->getFirstUnit()->usesEventStockMark() && !$eventTimetable->getFirstUnit()->isWaitingCancellation()): ?>
                                        <dd>
                                            <?= $this->Tr->h('reservation/calendar/remainStock') ?><?= h($eventTimetable->getFirstUnit()->getDisplayRemainStock()) ?><?= h($eventTimetable->getFirstUnit()->getEvent()->get('stock_unit')) ?>
                                        </dd>
                                    <?php else: ?>
                                        <dd class="dd_dispaly_icon">
                                        </dd>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        <?php else: ?>
                            <div
                                class="calender_list_day_some js_show_calendar_popup"
                                data-popup-type="<?= h(AbstractCalendarPopup::TYPE_SINGLE_DATE_TIMETABLE) ?>"
                                data-search-data="<?= h(json_encode($calendar->getTimetablePopupSearchData($date, $eventTimetable))) ?>"
                            >
                                <span><?= $this->Tr->h('reservation/calendar/listIcon') ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </td>
        <?php endforeach; ?>
    </tr>
<?php endforeach; ?>
