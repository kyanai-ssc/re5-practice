<?php


$this->assign('title', '予約 一括削除確認');
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
    '予約 一括削除確認'
);

?>
<aside class="cmn-msg is-caution">
    <p>
        <svg class="icon is-msg">
            <use xlink:href="#icon_info"></use>
        </svg>
        削除確認画面遷移後に一覧画面に遷移した場合、チェック情報が更新されている可能性がありますのでご注意ください。
    </p>
</aside>

<section class="form-input">
    <?= $this->Flash->render('reservationssDeleteManyErrors') ?>
    <?php $this->start('reservation_delete_many_dialog'); ?>
    <?= $this->Form->create(null, [
        'type' => 'post',
        'url' => [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'delete-many',
            '?' => [
                'search_payment_expired' => $searchPaymentExpiredFlg,
                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
            ],
        ],
        'idPrefix' => 'reservations-delete-many',
        'novalidate' => true,
        'class' => ['js_reservation_delete_many_form'],
    ]) ?>
    <p class="msg-error">
        <svg class="icon is-error">
            <use xlink:href="#icon_info"></use>
        </svg>
        削除したデータの復旧はできません。
    </p>
    <?= $this->element('Admin/Reservations/waiting_cancellation') ?>
    <?= $this->Form->hidden('checked', ['value' => $checked]); ?>
    <?= $this->Form->end() ?>
    <?php $this->end('reservation_delete_many_dialog'); ?>
    <div class="panel-show-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">削除対象件数</div>
                    </th>
                    <td>
                        <p class="cmn-txt"><?= h($reservations->count()) ?>件</p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">削除対象ダウンロード</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= $this->Html->link('削除対象を確認する', ['controller' => 'Reservations', 'action' => 'downloadChecked', 'prefix' => 'Admin']) ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
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


        <?= $this->Form->button('一括削除', [
            'type' => 'post',
            'class' => ['cmn-btn', 'is-pink', 'is-circle', 'js_post_confirm'],
            'data-confirm-message' => '上記の情報で該当データをまとめて削除します。\nよろしいでしょうか？',
            'data-confirm-title' => 'データの削除',
            'data-confirm-html' => $this->fetch('reservation_delete_many_dialog'),
            'data-confirm-form' => '.js_reservation_delete_many_form',
        ]) ?>
    </div>
</section>
