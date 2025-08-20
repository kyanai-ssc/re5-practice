<?php

use App\Model\EventCalendar\AbstractCalendarPopup;
use Cake\I18n\FrozenDate;

?>
<?php foreach ($calendar->getEvents() as $event): ?>
    <?php $eventTimetable = $calendar->getTimetable($event); ?>
    <li class="list_body_line_wrap clearfix js_apply_style" data-style="<?= h(json_encode($calendar->createCss($eventTimetable))) ?>">
        <ul class="list_body_line<?php if (!$calendar->isListDisplay($eventTimetable)): ?> is-list<?php endif; ?>">
            <li class="b-detail">
                <?php if ($this->Authority->isAuthority(true, 'Events', 'view')): ?>
                    <a href="#" class="js_show_event_popup"
                    data-title="<?= h($event->get('name')) ?>"
                    data-url="<?= $this->Url->build([
                        'prefix' => 'User',
                        'controller' => 'Events',
                        'action' => 'view',
                        'id' => $event->get('id'),
                        '?' => [
                            'frame' => $this->Configure->read('Master.common.flg.on'),
                        ],
                    ]) ?>">
                        <?php if ($event->has('label')): ?>
                            <span class="name-label"><?= h($event->get('label')->get('name')) ?></span>
                        <?php endif; ?>
                        <span class="name-detail"><?= h($event->get('name')) ?></span>
                    </a>
                <?php else: ?>
                    <?php if ($event->has('label')): ?>
                        <span class="name-label"><?= h($event->get('label')->get('name')) ?></span>
                    <?php endif; ?>
                    <span class="name-detail"><?= h($event->get('name')) ?></span>
                <?php endif; ?>
            </li>
            <?php if (!$calendar->isListDisplay($eventTimetable)): ?>
                <li class="b-day">
                    <?= h($this->Template->displayDayAndWeek($event->get('date_from'))) ?>
                    <?php if ($event->has('date_to')): ?>
                        <?php if ((new FrozenDate($event->get('date_from')))->format('Y-m-d') !== (new FrozenDate($event->get('date_to')))->format('Y-m-d')): ?>
                            ～<br/>
                            <?= h($this->Template->displayDayAndWeek($event->get('date_to'))) ?>
                        <?php endif; ?>
                    <?php else : ?>
                        ～
                    <?php endif; ?>
                </li>
                <li class="b-time">
                    <?= h($event->getTimeFrom()->format('H:i')) ?> ～
                </li>
            <?php endif; ?>
            <li class="b-status">
                <?php if (!$calendar->isListDisplay($eventTimetable)): ?>
                    <div
                        class="statusBtn <?= h(implode(' ', $calendar->getTimetableHtmlClass($eventTimetable, $selectCalendar))) ?>"
                        <?php if (!$selectCalendar): ?>
                            data-url="<?= $this->Url->build($calendar->getReservationUrl($eventTimetable->getFirstUnit())) ?>"
                        <?php else: ?>
                            data-calendar-data="<?= h(json_encode($calendar->getSelectCalendarData($eventTimetable->getFirstUnit()))) ?>"
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
                    </div>
                <?php else: ?>
                    <div
                        class="statusBtn btn-list js_show_calendar_popup"
                        data-popup-type="<?= h(AbstractCalendarPopup::TYPE_MULTIPLE_DATE_TIMETABLE) ?>"
                        data-search-data="<?= h(json_encode($calendar->getTimetablePopupSearchData($eventTimetable))) ?>"
                    >
                        <span>
                            <?= $this->Tr->h('reservation/calendar/listBtn') ?>
                        </span>
                    </div>
                <?php endif; ?>
            </li>
        </ul>
    </li>
<?php endforeach; ?>
