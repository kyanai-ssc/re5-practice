<?php
$this->assign('title', '予約 詳細');
$this->Html->script('admin/reservations/detail', [
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
$this->Breadcrumbs->add(
    '詳細'
);
?>

<section class="form-input">
    <?= $this->Flash->render('reservationsFinish') ?>
    <?= $this->Flash->render('reservationsError') ?>

    <?= $this->element('Admin/Reservations/detail', [
        'reservationForm' => $reservationForm,
        'valueOptions' => $valueOptions,
        'mode' => 'detail',
    ]) ?>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery') + [
                'search_payment_expired' => $searchPaymentExpiredFlg,
                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
            ],
        ], ['class' => ['cmn-btn', 'is-reset', 'is-gray']]) ?>

        <?= $this->Html->link('編集', [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'edit',
            'id' => $reservationForm->getReservationEntity()->get('id'),
            '?' => [
                'search_payment_expired' => $searchPaymentExpiredFlg,
                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
            ],
        ], ['class' => ['cmn-btn', 'is-blue']]) ?>
        <?php if ($reservationForm->getReservationEntity()->canCancel()): ?>
            <?php $this->start('reservation_cancel_dialog'); ?>
            <?= $this->Form->create(null, [
                'type' => 'post',
                'url' => [
                    'prefix' => 'Admin',
                    'controller' => 'Reservations',
                    'action' => 'cancel',
                    'id' => $reservationForm->getReservationEntity()->get('id'),
                    '?' => [
                        'search_payment_expired' => $searchPaymentExpiredFlg,
                        'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
                    ],
                ],
                'idPrefix' => 'reservations-cancel',
                'novalidate' => true,
                'class' => ['js_reservation_cancel_form'],
            ]) ?>
            <?= $this->element('Admin/Common/fieldset/mail_check') ?>
            <?= $this->Form->end() ?>
            <?php $this->end('reservation_cancel_dialog'); ?>
            <?= $this->Form->button('キャンセル', [
                'class' => ['js_post_confirm', 'cmn-btn', 'is-pink', 'is-circle'],
                'data-confirm-message' => '予約をキャンセルしてもよろしいですか？',
                'data-confirm-title' => '予約のキャンセル',
                'data-confirm-html' => $this->fetch('reservation_cancel_dialog'),
                'data-confirm-form' => '.js_reservation_cancel_form',
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Reservations',
                    'action' => 'cancel',
                    'id' => $reservationForm->getReservationEntity()->get('id'),
                    '?' => [
                        'search_payment_expired' => $searchPaymentExpiredFlg,
                        'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
                    ],
                ], ['escape' => false]),
            ]) ?>
        <?php endif; ?>
        <?php $this->start('reservation_delete_dialog'); ?>
            <?= $this->Form->create(null, [
                'type' => 'post',
                'url' => [
                    'prefix' => 'Admin',
                    'controller' => 'Reservations',
                    'action' => 'delete',
                    'id' => $reservationForm->getReservationEntity()->get('id'),
                    '?' => [
                        'search_payment_expired' => $searchPaymentExpiredFlg,
                        'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
                    ],
                ],
                'idPrefix' => 'reservations-delete',
                'novalidate' => true,
                'class' => ['js_reservation_delete_form'],
            ]) ?>
                <p class="msg-error">
                    <svg class="icon is-error">
                        <use xlink:href="#icon_info"></use>
                    </svg>
                    削除したデータの復旧はできません
                </p>
                <?= $this->element('Admin/Reservations/waiting_cancellation') ?>
            <?= $this->Form->end() ?>
        <?php $this->end('reservation_delete_dialog'); ?>
        <?php $deleteMessage = '予約データの削除をおこなってよろしいですか？'; ?>
        <?php if ($reservationForm->getReservationEntity()->getUserEntity()->isGuest()): ?>
            <?php $deleteMessage .= '\n※顧客一覧に登録されているデータも削除されます。'; ?>
        <?php endif; ?>
        <?= $this->Form->button('削除', [
            'class' => ['js_post_confirm', 'cmn-btn', 'is-pink', 'is-circle'],
            'data-confirm-message' => $deleteMessage,
            'data-confirm-title' => '予約データの削除',
            'data-confirm-html' => $this->fetch('reservation_delete_dialog'),
            'data-confirm-form' => '.js_reservation_delete_form',
            'data-url' => $this->Url->build([
                'prefix' => 'Admin',
                'controller' => 'Reservations',
                'action' => 'delete',
                'id' => $reservationForm->getReservationEntity()->get('id'),
                '?' => [
                    'search_payment_expired' => $searchPaymentExpiredFlg,
                    'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
                ],
            ], ['escape' => false]),
        ]) ?>
        <div class="mgt-20">
            <?php if ($reservationForm->getReservationEntity()->canAddVideoMeeting()): ?>
                <?php $this->start('add_video_metting_form'); ?>
                    <?= $this->Form->create(null, [
                        'type' => 'post',
                        'url' => [
                            'prefix' => 'Admin',
                            'controller' => 'Reservations',
                            'action' => 'view',
                            'id' => $reservationForm->getReservationEntity()->get('id'),
                            '?' => [
                                'search_payment_expired' => $searchPaymentExpiredFlg,
                                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
                            ],
                        ],
                        'idPrefix' => 'add-video-metting',
                        'novalidate' => true,
                        'class' => ['js_submit_once', 'js_add_video_metting'],
                        'data-reservation-id' => $reservationForm->getReservationEntity()->get('id'),
                    ]) ?>
                    <?= $this->Form->end() ?>
                <?php $this->end('add_video_metting_form'); ?>
                <?= $this->Form->button('ビデオ会議の登録', [
                    'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_post_confirm'],
                    'data-confirm-title' => 'ビデオ会議の登録',
                    'data-confirm-message' => 'ビデオ会議の登録をおこなってよろしいですか？',
                    'data-confirm-html' => $this->fetch('add_video_metting_form'),
                    'data-confirm-form' => '.js_add_video_metting',
                ]) ?>
            <?php endif; ?>
            <?php if ($reservationForm->getReservationEntity()->canDeleteVideoMeeting()): ?>
                <?php $this->start('delete_video_metting_form'); ?>
                    <?= $this->Form->create(null, [
                        'type' => 'post',
                        'url' => [
                            'prefix' => 'Admin',
                            'controller' => 'Reservations',
                            'action' => 'view',
                            'id' => $reservationForm->getReservationEntity()->get('id'),
                            '?' => [
                                'search_payment_expired' => $searchPaymentExpiredFlg,
                                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
                            ],
                        ],
                        'idPrefix' => 'delete-video-metting',
                        'novalidate' => true,
                        'class' => ['js_submit_once', 'js_delete_video_metting'],
                        'data-reservation-id' => $reservationForm->getReservationEntity()->get('id'),
                    ]) ?>
                    <?= $this->Form->end() ?>
                <?php $this->end('delete_video_metting_form'); ?>
                <?= $this->Form->button('ビデオ会議の削除', [
                    'class' => ['cmn-btn', 'is-pink', 'is-circle', 'js_post_confirm'],
                    'data-confirm-title' => 'ビデオ会議の削除',
                    'data-confirm-message' => 'ビデオ会議の削除をおこなってよろしいですか？',
                    'data-confirm-html' => $this->fetch('delete_video_metting_form'),
                    'data-confirm-form' => '.js_delete_video_metting',
                ]) ?>
            <?php endif; ?>
            <?php if ($reservationForm->getReservationEntity()->canSmartLockReLink()): ?>
                <?php $this->start('add_smart_lock_form'); ?>
                    <?= $this->Form->create(null, [
                        'type' => 'post',
                        'url' => [
                            'prefix' => 'Admin',
                            'controller' => 'Reservations',
                            'action' => 'view',
                            'id' => $reservationForm->getReservationEntity()->get('id'),
                            '?' => [
                                'search_payment_expired' => $searchPaymentExpiredFlg,
                                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
                            ],
                        ],
                        'idPrefix' => 'add-smart-lock',
                        'novalidate' => true,
                        'class' => ['js_submit_once', 'js_add_smart_lock'],
                        'data-reservation-id' => $reservationForm->getReservationEntity()->get('id'),
                    ]) ?>
                    <?= $this->Form->end() ?>
                <?php $this->end('add_smart_lock_form'); ?>
                <?= $this->Form->button('スマートロック再連携', [
                    'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_post_confirm'],
                    'data-confirm-title' => 'スマートロック再連携',
                    'data-confirm-message' => 'スマートロック再連携の登録をおこなってよろしいですか？',
                    'data-confirm-html' => $this->fetch('add_smart_lock_form'),
                    'data-confirm-form' => '.js_add_smart_lock',
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="hidden">
        <?php if ($searchPaymentExpiredFlg): ?>
            <input type="hidden" class="js_search_payment_expired_query" value="<?= h($searchPaymentExpiredFlg) ?>"/>
        <?php endif; ?>
        <?php if ($searchSmartLockUnlinkedFlg): ?>
            <input type="hidden" class="js_search_smart_lock_unlinked_query" value="<?= h($searchSmartLockUnlinkedFlg) ?>"/>
        <?php endif; ?>
    </div>
</section>
