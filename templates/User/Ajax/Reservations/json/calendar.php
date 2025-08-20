<?php $this->start('ajax_html'); ?>
    <?= $this->element('User/Reservations/calendar', [
        'calendar' => $calendarForm->getEventCalendar(),
        'calendarForm' => $calendarForm,
        'valueOptions' => $valueOptions,
        'selectCalendar' => $selectCalendar,
        'formType' => $formType,
        'tagList' => $tagList,
        'eventNameList' => $eventNameList,
    ]) ?>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
]) ?>
