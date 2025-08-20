<?php foreach ($calendar->getCalendarDate() as $date): ?>
    <?php $first = true; ?>
    <?php if (count($calendar->getTimetable($date)) > 0): ?>
        <?php foreach ($calendar->getTimetable($date) as $eventTimetable): ?>
            <td class="line time<?php if ($first): ?> day_start<?php endif; ?>">
                <div class="layer">
                    <?php foreach ($eventTimetable->getTimetable() as $eventUnit): ?>
                        <?php if (!$calendar->isOverlapCalendarTimeRange($eventUnit, $date)) { continue; } ?>
                        <div
                            class="calender_list <?= h(implode(' ', $calendar->getUnitHtmlClass($eventUnit, $selectCalendar))) ?> js_apply_style"
                            <?php if (!$selectCalendar): ?>
                                data-search-data="<?= h(json_encode($calendar->getDetailSearchData($eventUnit))) ?>"
                            <?php else: ?>
                                data-calendar-data="<?= h(json_encode($calendar->getSelectCalendarData($eventUnit))) ?>"
                            <?php endif; ?>
                            data-style="<?= h(json_encode($calendar->createCss($date, $eventUnit))) ?>"
                        >
                            <dl class="clearfix">
                                <dd>
                                    <?= $this->element('Admin/Reservations/calendar_unit_data', [
                                        'calendar' => $calendar,
                                        'eventUnit' => $eventUnit,
                                    ]) ?>
                                </dd>
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
        <td class="line time day_start">
            <div class="layer">
                <?php foreach ($calendar->getCalendarTimeRange() as $time): ?>
                    <div class="ground">
                    </div>
                <?php endforeach; ?>
            </div>
        </td>
    <?php endif; ?>
<?php endforeach; ?>
