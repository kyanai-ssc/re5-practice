<?php $this->start('ajax_html'); ?>
    <?= $this->element($calendarPopupForm->getCalendarPopup()->getTemplatePath(), [
        'calendarPopup' => $calendarPopupForm->getCalendarPopup(),
        'valueOptions' => $valueOptions,
        'selectCalendar' => $selectCalendar,
    ]) ?>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
]) ?>
