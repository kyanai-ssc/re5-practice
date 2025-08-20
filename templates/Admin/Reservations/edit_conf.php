<?php

$this->assign('title', '予約 編集内容確認');
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
    'id' => $reservationForm->getReservationEntity()->get('id'),
    '?' => [
        'search_payment_expired' => $searchPaymentExpiredFlg,
        'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
    ],
]);

$this->Breadcrumbs->add(
    '編集内容確認'
);

?>
<section class="form-input">
    <?= $this->Flash->render('reservationsError') ?>
    <?= $this->Form->create($reservationForm->getReservationEntity(), [
        'type' => 'post',
        'url' => [
            'controller' => 'Reservations',
            'action' => 'edit-conf',
            'id' => $reservationForm->getReservationEntity()->get('id'),
            '?' => [
                'search_payment_expired' => $searchPaymentExpiredFlg,
                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
            ],
        ],
        'idPrefix' => 'reservations-edit-conf',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <?= $this->Token->getTokenTag() ?>
    <?= $this->Token->getTokenError() ?>
    <?= $this->element('Admin/Reservations/detail', [
        'reservationForm' => $reservationForm,
        'valueOptions' => $valueOptions,
        'mode' => 'editConf',
    ]) ?>
    <?= $this->element('Admin/Common/fieldset/mail_check') ?>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'edit',
            'id' => $reservationForm->getReservationEntity()->get('id'),
            '?' => $this->Configure->read('Setting.formInput.backQuery') + [
                'search_payment_expired' => $searchPaymentExpiredFlg,
                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
            ],
        ], [
            'class' => ['cmn-btn', 'is-reset', 'is-gray'],
        ]) ?>
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
