<?= $this->Form->create($reservationForm, [
    'type' => 'post',
    'url' => [
        'prefix' => 'Admin',
        'controller' => 'Reservations',
        'action' => $mode,
        'id' => $reservationForm->getReservationEntity()->get('id'),
        '?' => [
            'key' => $reservationForm->getContinuousParameter('key'),
            'search_payment_expired' => $searchPaymentExpiredFlg,
            'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
        ] + $reservationForm->getReservationParameter(),
    ],
    'idPrefix' => 'reservations-' . $mode,
    'novalidate' => true,
    'class' => ['js_submit_once', 'js_reservation_form'],
]) ?>
<?= $this->Token->getTokenTag() ?>
<?= $this->Token->getTokenError() ?>
<?= $this->element('Admin/Reservations/fieldset', [
    'reservationForm' => $reservationForm,
    'valueOptions' => $valueOptions,
    'mode' => $mode,
]) ?>
<div class="btn-box mgt-20">
    <?php if ($mode != 'add') : ?>
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery') + [
                'search_payment_expired' => $searchPaymentExpiredFlg,
                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
            ],
        ], [
            'class' => ['cmn-btn', 'is-reset', 'is-gray'],
        ]) ?>
    <?php endif; ?>
    <?= $this->Form->button('確認', [
        'type' => 'submit',
        'class' => ['cmn-btn', 'is-blue'],
    ]) ?>
</div>


<?= $this->Form->end() ?>
<?= $this->element('Common/file_form') ?>
