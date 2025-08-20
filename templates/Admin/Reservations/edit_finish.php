<?php

$this->assign('title', '予約 編集完了');
$this->Html->script('admin/reservations/confirm', [
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
    'id' => $reservationId,
    '?' => [
        'search_payment_expired' => $searchPaymentExpiredFlg,
        'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
    ],
]);

$this->Breadcrumbs->add(
    '編集完了'
);

?>
<section class="form-input">
    <?= $this->Flash->render('reservationsFinish') ?>

    <div class="btn-box tac">
        <?= $this->Html->link('予約一覧', [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery') + [
                'search_payment_expired' => $searchPaymentExpiredFlg,
                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
            ],
        ], ['class' => ['cmn-btn', 'is-gray']]) ?>

        <?= $this->Html->link('予約詳細', [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'view',
            'id' => $reservationId,
            '?' => [
                'search_payment_expired' => $searchPaymentExpiredFlg,
                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
            ],
        ], ['class' => ['cmn-btn', 'is-blue']]) ?>
    </div>
</section>
