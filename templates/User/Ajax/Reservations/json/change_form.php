<?php
$formController = null;
$formAction = null;
if ($mode === 'edit' && !$this->CommonData->existsUserLoginData()) {
    $formController = 'Guest';
    $formAction = 'reservationEdit';
}
?>
<?php $this->start('ajax_html'); ?>
    <?= $this->element('User/Reservations/form', [
        'reservationForm' => $reservationForm,
        'valueOptions' => $valueOptions,
        'mode' => $mode,
        'formController' => $formController,
        'formAction' => $formAction,
    ]) ?>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
]) ?>
