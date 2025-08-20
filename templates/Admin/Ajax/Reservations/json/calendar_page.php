<?php $this->start('ajax_html'); ?>
<?= $this->element($calendarForm->getEventCalendar()->getTemplatePath(), [
    'calendar' => $calendarForm->getEventCalendar(),
    'valueOptions' => $valueOptions,
    'selectCalendar' => $selectCalendar,
    'calendarPage' => true,
]) ?>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
    'colorChips' => $calendarForm->getEventCalendar()->getColorChips(true)
]) ?>
