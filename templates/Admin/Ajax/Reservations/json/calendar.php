<?php $this->start('ajax_html'); ?>
    <?= $this->element('Admin/Reservations/calendar', [
        'calendar' => $calendarForm->getEventCalendar(),
        'calendarForm' => $calendarForm,
        'valueOptions' => $valueOptions,
        'selectCalendar' => $selectCalendar,
    ]) ?>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
]) ?>
