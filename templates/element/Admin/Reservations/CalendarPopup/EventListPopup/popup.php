<?php
use App\Model\EventCalendar\AbstractCalendarPopup;
?>
<div>
    <div class="tooltip" title="予約枠一覧">
        <h4 class="ttl-popup"><span><?= h($this->Template->displayDayAndWeek($calendarPopup->getDate())) ?></span></h4>
        <?= $this->element('Admin/Reservations/calendar_color_chips', [
            'colorChips' => $calendarPopup->getColorChips(),
        ]) ?>
        <div class="list-typeA">
            <div class="is-listOnly pc-only">
                <div class="reserve_list_head">
                    <ul>
                        <li class="h-detail">
                        </li>
                        <li class="h-time">
                            時間
                        </li>
                        <li class="h-status">
                            空き情報
                        </li>
                    </ul>
                </div>
            </div>
            <div class="reserve_list_body">
                <ul>
                    <?php foreach ($calendarPopup->getTimetable() as $eventTimetable): ?>
                        <li class="list_body_line_wrap clearfix js_apply_style" data-style="<?= h(json_encode($calendarPopup->createCss($eventTimetable))) ?>">
                            <ul class="list_body_line">
                                <?php if ($this->Authority->isAuthority(true, 'Events', 'edit')): ?>
                                    <li class="b-detail">
                                        <a href="<?= $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Events',
                                            'action' => 'edit',
                                            'id' => $eventTimetable->getEvent()->get('id'),
                                        ]) ?>" target="_blank">
                                            <?php if ($eventTimetable->getEvent()->has('label')): ?>
                                                <span class="name-label"><?= h($eventTimetable->getEvent()->get('label')->get('name')) ?></span>
                                            <?php endif; ?>
                                            <span class="name-detail"><?= h($eventTimetable->getEvent()->get('name')) ?></span>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="b-detail b-detail-no-link">
                                        <?php if ($eventTimetable->getEvent()->has('label')): ?>
                                            <span class="name-label"><?= h($eventTimetable->getEvent()->get('label')->get('name')) ?></span>
                                        <?php endif; ?>
                                        <span class="name-detail"><?= h($eventTimetable->getEvent()->get('name')) ?></span>
                                    </li>
                                <?php endif; ?>
                                <li class="b-time">
                                    <?php if (!$calendarPopup->isListDisplay($eventTimetable)): ?>
                                        <?= h($eventTimetable->getFirstUnit()->getDateTimeFrom()->format('H:i')) ?> ～
                                    <?php endif; ?>
                                </li>
                                <li class="b-status">
                                    <?php if (!$calendarPopup->isListDisplay($eventTimetable)): ?>
                                        <a
                                            href="#"
                                            class="<?= h(implode(' ', $calendarPopup->getTimetableHtmlClass($eventTimetable, $selectCalendar))) ?>"
                                            <?php if (!$selectCalendar): ?>
                                                data-search-data="<?= h(json_encode($calendarPopup->getDetailSearchData($eventTimetable->getFirstUnit()))) ?>"
                                            <?php else: ?>
                                                data-calendar-data="<?= h(json_encode($calendarPopup->getSelectCalendarData($eventTimetable->getFirstUnit()))) ?>"
                                            <?php endif; ?>
                                        >
                                            <?= $this->element('Admin/Reservations/calendar_unit_data', [
                                                'calendar' => $calendarPopup,
                                                'eventUnit' => $eventTimetable->getFirstUnit(),
                                            ]) ?>
                                        </a>
                                    <?php else: ?>
                                        <a
                                            href="#"
                                            class="multiple_date js_show_calendar_popup"
                                            data-popup-type="<?= h(AbstractCalendarPopup::TYPE_SINGLE_DATE_TIMETABLE) ?>"
                                            data-search-data="<?= h(json_encode($calendarPopup->getTimetablePopupSearchData($calendarPopup->getDate(), $eventTimetable))) ?>"
                                        >
                                            一覧
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
</div>
