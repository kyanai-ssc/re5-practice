<?php foreach ($calendar->getCalendarDate() as $date): ?>
    <?php $first = true; ?>
    <?php if (count($calendar->getTimetable($date)) > 0): ?>
        <?php foreach ($calendar->getTimetable($date) as $eventTimetable): ?>
            <td class="line time<?php if ($first): ?> day_div<?php endif; ?>">
                <div class="layer">
                    <?php foreach ($eventTimetable->getTimetable() as $eventUnit): ?>
                        <?php if (!$calendar->isOverlapCalendarTimeRange($eventUnit, $date)) { continue; } ?>
                        <div
                            class="calender_list <?= h(implode(' ', $calendar->getUnitHtmlClass($eventUnit, $selectCalendar))) ?> js_apply_style"
                            <?php if (!$selectCalendar): ?>
                                data-url="<?= $this->Url->build($calendar->getReservationUrl($eventUnit)) ?>"
                            <?php else: ?>
                                data-calendar-data="<?= h(json_encode($calendar->getSelectCalendarData($eventUnit))) ?>"
                            <?php endif; ?>
                            data-event-id="<?= h($eventUnit->getEvent()->get('id')) ?>"
                            data-usage-timestamp="<?= h($eventUnit->getDateTimeFrom()->format('Y/m/d H:i')) ?>"
                            data-style="<?= h(json_encode($calendar->createCss($date, $eventUnit))) ?>"
                        >
                            <dl class="clearfix">
                                <?php if (!$eventUnit->usesEventStockMark() && !$eventUnit->isWaitingCancellation()): ?>
                                    <dd>
                                        <?= $this->Tr->h('reservation/calendar/remainStock') ?><?= h($eventUnit->getDisplayRemainStock()) ?><?= h($eventUnit->getEvent()->get('stock_unit')) ?>
                                    </dd>
                                <?php else: ?>
                                    <dd class="dd_dispaly_icon">
                                    </dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($calendar->getCalendarTimeRange() as $time): ?>
                    <div class="ground">
                    </div>
                <?php endforeach; ?>
            </td>
            <?php $first = false; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <td class="line time day_div">
            <div class="layer">
                <?php foreach ($calendar->getCalendarTimeRange() as $time): ?>
                    <div class="ground">
                    </div>
                <?php endforeach; ?>
            </div>
        </td>
    <?php endif; ?>
<?php endforeach; ?>
