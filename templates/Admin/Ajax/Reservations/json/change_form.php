<?php $this->start('ajax_html'); ?>
    <?php if($mode === 'detail'): ?>
        <?= $this->element('Admin/Reservations/detail', [
            'reservationForm' => $reservationForm,
            'valueOptions' => $valueOptions,
            'mode' => $mode,
        ]) ?>
    <?php else: ?>
        <?= $this->element('Admin/Reservations/form', [
            'reservationForm' => $reservationForm,
            'valueOptions' => $valueOptions,
            'mode' => $mode,
        ]) ?>
    <?php endif; ?>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
]) ?>
