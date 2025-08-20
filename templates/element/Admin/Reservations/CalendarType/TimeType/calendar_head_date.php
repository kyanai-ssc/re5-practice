<?php foreach ($calendar->getCalendarDate() as $date): ?>
    <th <?php if ($calendar->getCountByDate($date) > 0): ?>colspan="<?= h($calendar->getCountByDate($date)) ?>"<?php endif; ?> class="day_start bg-red">
        <div>
            <span><?= h($this->Template->displayDayAndWeek($date, null, 'm/d')) ?></span>
        </div>
    </th>
<?php endforeach; ?>
