<?php foreach ($calendar->getCalendarDate() as $date): ?>
    <?php if (count($calendar->getTimetable($date)) > 0): ?>
        <?php $first = true; ?>
        <?php foreach ($calendar->getTimetable($date) as $eventTimetable): ?>
            <th class="<?php if ($first): ?>day_div<?php endif; ?>">
                <?php if ($this->Authority->isAuthority(true, 'Events', 'view')): ?>
                    <a class="js_show_event_popup" href="#"
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
                        <span><?= h($eventTimetable->getEvent()->get('name')) ?></span>
                    </a>
                <?php else: ?>
                    <span><?= h($eventTimetable->getEvent()->get('name')) ?></span>
                <?php endif; ?>
            </th>
            <?php $first = false; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <th class="day_div">
        </th>
    <?php endif; ?>
<?php endforeach; ?>
