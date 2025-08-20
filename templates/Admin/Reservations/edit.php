<?php
$this->assign('title', '予約 編集');
$this->Html->script('admin/reservations/fieldset', [
    'block' => true,
]);
$this->assign('headerType', 'data');

$this->Breadcrumbs->add('予約一覧', [
    'prefix' => 'Admin',
    'controller' => 'Reservations',
    'action' => 'list',
    '?' => [
        'search_payment_expired' => $searchPaymentExpiredFlg,
        'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
    ],
]);
$this->Breadcrumbs->add('詳細', [
    'prefix' => 'Admin',
    'controller' => 'Reservations',
    'action' => 'view',
    'id' => $reservationForm->getReservationEntity()->get('id'),
    '?' => [
        'search_payment_expired' => $searchPaymentExpiredFlg,
        'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
    ],
]);

$this->Breadcrumbs->add(
    '編集'
);
?>

<section class="form-input">
    <?= $this->Flash->render('reservationsError') ?>
    <?= $this->element('Admin/Reservations/form', [
        'reservationForm' => $reservationForm,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
    ]) ?>
    <div class="hidden">
        <?php if ($searchPaymentExpiredFlg): ?>
            <input type="hidden" class="js_search_payment_expired_query" value="<?= h($searchPaymentExpiredFlg) ?>"/>
        <?php endif; ?>
    </div>
</section>
