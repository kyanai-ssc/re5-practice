<?php
$this->assign('title', '予約 登録');
$this->assign('headerType', 'data');
$this->Html->script('admin/reservations/fieldset', [
    'block' => true,
]);

$this->Breadcrumbs->add(
    '予約台帳',
    ['prefix' => 'Admin', 'controller' => 'Reservations', 'action' => 'calendar']
);
$this->Breadcrumbs->add(
    '予約登録'
);
?>

<section class="form-input">
    <?= $this->Flash->render('reservationsError') ?>
    <?= $this->element('Admin/Reservations/form', [
        'reservationForm' => $reservationForm,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>
</section>
