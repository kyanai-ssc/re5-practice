<?php foreach ($calendar->getCalendarDate() as $date): ?>
    <?php if (count($calendar->getTimetable($date)) > 0): ?>
        <?php $first = true; ?>
        <?php foreach ($calendar->getTimetable($date) as $eventTimetable): ?>
            <th class="<?php if ($first): ?>day_start<?php endif; ?>">
                <span><?= h($eventTimetable->getEvent()->get('name')) ?></span>
            </th>
            <?php $first = false; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <th class="day_start">
        </th>
    <?php endif; ?>
<?php endforeach; ?>
