<?php

use App\Model\Entity\AdminListItem;
use App\Model\Entity\User;

$this->assign('title', '予約 一覧');
$this->assign('headerType', 'data');

$this->Html->script('admin/reservations/list', [
    'block' => true,
]);
$this->Breadcrumbs->add(
    '予約 一覧'
);
$this->Html->script('admin/common/tableBtn', [
    'block' => true,
]);
?>
<?= $this->Flash->render('reservationsFinish') ?>
<?= $this->Flash->render('reservationsError') ?>
<section class="panel-show">
    <?= $this->element('Admin/Reservations/search', [
        'searchForm' => $searchForm,
    ]) ?>
</section><!-- .panel-show -->
<section class="search-list mgt-20">
    <aside class="search-parts d-flex mgt-20">
        <div class="search-parts-left">
            <?= $this->element('Admin/Common/search/page_counter') ?>
        </div><!-- .search-counter -->
        <div class="search-parts-center">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </div>
        <div class="search-parts-right">
            <ul class="d-flex">
                <li>
                    <?= $this->element('Admin/Common/search/limit', [
                        'options' => [
                            'class' => ['js_change_search_limit'],
                            'data-url' => $this->Url->build([
                                'prefix' => 'Admin',
                                'controller' => 'Reservations',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
                <li>
                    <?= $this->Authority->isAuthority($this->element('Admin/Common/form/import', ['importController' => 'Reservations', 'uploadTitle' => '予約データアップロード']), 'Reservations', 'upload'); ?>
                </li>
                <li>
                    <?= $this->Authority->isAuthority($this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_download"/></svg>', [
                        'type' => 'button',
                        'class' => ['js_post_link_plural', 'btn-list', 'fileDown', 'is-download'],
                        'title' => '予約データダウンロード',
                        'aria-describedby' => 'ui-id-60',
                        'data-url' => $this->Url->build([
                            'prefix' => 'Admin',
                            'controller' => 'Reservations',
                            'action' => 'download',
                        ], ['escape' => false]),
                        'escapeTitle' => false,
                    ]), 'Reservations', 'download'); ?>
                </li>
                <li>
                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_setting"/></svg>', [
                        'type' => 'button',
                        'title' => '一覧項目設定',
                        'class' => ['js_select_list_items', 'btn-edit', ' thEditBtn', 'tooltip'],
                        'data-type' => AdminListItem::TYPE_RESERVATION_LIST,
                        'data-title' => '一覧項目設定',
                        'escapeTitle' => false,
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($reservations) > 0): ?>
        <div class="fixedTable-option">
            <aside class="fixedTable-arrow">
                <button id="left-button" type="button" class="fixedTable-scroll-btn is-prev cmn-btn"></button>
                <button id="right-button" type="button" class="fixedTable-scroll-btn is-next cmn-btn"></button>
            </aside><!-- .search-parts -->
        </div>
        <div class="fixedTable-wrap mgb-10">
            <div class="fixedTable-in">
                <div class="fixedTableHead">
                    <table class="cmn-table status-table">
                        <thead class="stickyTable">
                        <?= $this->element('Admin/AdminListItems/items_header', [
                            'listItems' => $searchForm->getListItems(),
                        ]) ?>
                        </thead>
                    </table>
                </div>
                <table class="cmn-table status-table fixedTableBody">
                    <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr class="parent">
                            <?= $this->element('Admin/AdminListItems/items_body', [
                                'listItems' => $searchForm->getListItems(),
                                'options' => [
                                    'mode' => 'reservation',
                                    'event' => $reservation->event,
                                    'user' => $reservation->user,
                                    'reservation' => $reservation,
                                ],
                            ]) ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table><!-- .result-table -->
            </div>
            <table class="fixedTableLeft cmn-table status-table js_check_popup_table">
                <thead>
                <tr>
                    <th class="tool fixedElm"><span>操作</span></th>
                    <th class="checkbox fixedElm"><?= $this->element('Admin/Common/search/list_all_check') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td class="tool">
                            <ul>
                                <?php if ($reservation->canView()): ?>
                                    <li>
                                        <?php $detailBtn = $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_info"></use></svg>', [
                                            'type' => 'button',
                                            'title' => '詳細',
                                            'class' => ['js_change_url', 'btn-tool', 'is-info'],
                                            'data-url' => $this->Url->build([
                                                'prefix' => 'Admin',
                                                'controller' => 'Reservations',
                                                'action' => 'view',
                                                'id' => $reservation->id,
                                                '?' => [
                                                    'search_payment_expired' => $searchPaymentExpiredFlg,
                                                    'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
                                                ],
                                            ], ['escape' => false]),
                                            'escapeTitle' => false,
                                        ]) ?>
                                        <?= $this->Authority->isAuthority($detailBtn, 'Reservations', 'view'); ?>
                                    </li>
                                <?php endif; ?>
                                <?php if (isset($reservation->user->guest_flg) && $reservation->user->guest_flg === User::GUEST_FLG_OFF): ?>
                                    <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_reserve"></use></svg>', [
                                        'type' => 'button',
                                        'title' => '予約',
                                        'class' => ['js_change_url', 'btn-tool', 'is-reserve'],
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Reservations',
                                            'action' => 'calendar',
                                            '?' => [
                                                'user_id' => $reservation->user_id,
                                            ],
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                    </li>
                                <?php endif; ?>
                                <?php if ($reservation->canEdit()): ?>
                                    <li>
                                        <?php $editBtn = $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', [
                                            'type' => 'button',
                                            'title' => '編集',
                                            'class' => ['js_change_url', 'btn-tool', 'is-editor'],
                                            'data-url' => $this->Url->build([
                                                'prefix' => 'Admin',
                                                'controller' => 'Reservations',
                                                'action' => 'edit',
                                                'id' => $reservation->id,
                                                '?' => [
                                                    'search_payment_expired' => $searchPaymentExpiredFlg,
                                                    'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
                                                ],
                                            ], ['escape' => false]),
                                            'escapeTitle' => false,
                                        ]) ?>
                                        <?= $this->Authority->isAuthority($editBtn, 'Reservations', 'edit'); ?>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <?php $this->start('reservation_delete_dialog'); ?>
                                        <?= $this->Form->create(null, [
                                            'type' => 'post',
                                            'url' => [
                                                'prefix' => 'Admin',
                                                'controller' => 'Reservations',
                                                'action' => 'delete',
                                                'id' => $reservation->id,
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
                                                削除したデータの復旧はできません。
                                            </p>
                                            <?= $this->element('Admin/Reservations/waiting_cancellation') ?>
                                        <?= $this->Form->end() ?>
                                    <?php $this->end('reservation_delete_dialog'); ?>
                                    <?php $deleteMessage = 'データの削除をおこなってよろしいですか？'; ?>
                                    <?php if ($reservation->has('user') && $reservation->get('user')->isGuest()): ?>
                                        <?php $deleteMessage .= '\n※顧客一覧に登録されているデータも削除されます。'; ?>
                                    <?php endif; ?>
                                    <?php $deleteBtn = $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                        'type' => 'button',
                                        'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                        'title' => '削除',
                                        'data-confirm-message' => $deleteMessage,
                                        'data-confirm-title' => 'データの削除',
                                        'data-confirm-html' => $this->fetch('reservation_delete_dialog'),
                                        'data-confirm-form' => '.js_reservation_delete_form',
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Reservations',
                                            'action' => 'delete',
                                            'id' => $reservation->id,
                                            '?' => [
                                                'search_payment_expired' => $searchPaymentExpiredFlg,
                                                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg
                                            ],
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                    <?= $this->Authority->isAuthority($deleteBtn, 'Reservations', 'delete'); ?>
                                </li>
                            </ul>
                        </td>
                        <td class="checkbox">
                            <?= $this->element('Admin/Common/search/list_check', [
                                'checkId' => $reservation->id,
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <aside class="search-parts mgt-20">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <aside class="search-parts mgt-20 tar mgr-50">
            <?= $this->Form->button('一括削除', [
                'type' => 'button',
                'class' => ['cmn-btn', 'is-circle', 'is-pink', 'js_save_check_data_confirm'],
                'data-confirm-message' => '該当データをまとめて削除します。\n次の確認画面で削除対象を確認してください\n※ゲスト予約で登録された「ゲスト」のデータも削除されます。',
                'data-confirm-title' => 'データの削除',
                'data-confirm-html' => h('削除したデータの復旧はできません。'),
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Reservations',
                    'action' => 'deleteMany',
                    '?' => [
                        'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg,
                        'search_payment_expired' => $searchPaymentExpiredFlg,
                    ],
                ], ['escape' => false]),
            ]) ?>
        </aside>
    <?php else: ?>
        <?= $this->element('Admin/Common/search/no_result') ?>
    <?php endif; ?>
    <div class="hidden">
        <input type="hidden" class="js_save_check_url" value="<?= $this->Url->build([
            'prefix' => 'Admin/Ajax',
            'controller' => 'Reservations',
            'action' => 'save-check',
        ]) ?>"/>
        <input type="hidden" class="js_reload_url" value="<?= $this->Url->build([
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ]) ?>"/>
        <?php if ($searchPaymentExpiredFlg): ?>
            <input type="hidden" class="js_search_payment_expired_query" value="<?= h($searchPaymentExpiredFlg) ?>"/>
        <?php endif; ?>
        <?php if ($searchSmartLockUnlinkedFlg): ?>
            <input type="hidden" class="js_search_smart_lock_unlinked_query" value="<?= h($searchSmartLockUnlinkedFlg) ?>"/>
        <?php endif; ?>
    </div>
</section>

