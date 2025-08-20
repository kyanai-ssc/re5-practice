<?php

use App\Model\Entity\AdminListItem;

$this->assign('title', '顧客一覧');
$this->assign('headerType', 'data');
$this->Breadcrumbs->add(
    '顧客一覧'
);

$this->Html->script('admin/users/list', [
    'block' => true,
]);

if ($selectUser) {
    $this->assign('noNavi', true);
}
?>
<?= $this->Flash->render('usersFinish') ?>
<?= $this->Flash->render('usersErrors') ?>
<section class="panel-show">
    <?= $this->element('Admin/Users/search', [
        'searchForm' => $searchForm,
        'selectUser' => $selectUser,
        'selectUserQuery' => $selectUserQuery,
    ]) ?>
</section><!-- .panel-show -->
<section class="search-list mgt-20">
    <aside class="search-parts d-flex mgt-20">
        <div class="search-parts-left">
            <?= $this->element('Admin/Common/search/page_counter') ?>
            <?php if (!$selectUser): ?>
                <?= $this->Authority->isAuthority($this->Form->button('メール配信', [
                    'type' => 'button',
                    'class' => ['js_save_check_data', 'cmn-btn', 'is-search-editor', 'is-blue'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'MailDeliveries',
                        'action' => 'add',
                    ], ['escape' => false]),
                ]), 'MailDeliveries', 'add'); ?>
                <?= $this->Form->button('新規登録', [
                    'type' => 'button',
                    'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'Users',
                        'action' => 'add',
                    ], ['escape' => false]),
                ]) ?>
            <?php endif; ?>
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
                                'controller' => 'Users',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery') + $selectUserQuery,
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
                <?php if (!$selectUser): ?>
                    <li>
                        <?= $this->Authority->isAuthority($this->element('Admin/Common/form/import', ['importController' => 'Users', 'uploadTitle' => '会員データアップロード']), 'Users', 'upload'); ?>
                    </li>
                    <li>
                        <?= $this->Authority->isAuthority($this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_download"/></svg>', [
                            'type' => 'button',
                            'class' => ['js_post_link_plural', 'btn-list', 'fileDown', 'is-download'],
                            'title' => '顧客データダウンロード',
                            'aria-describedby' => 'ui-id-60',
                            'data-url' => $this->Url->build([
                                'prefix' => 'Admin',
                                'controller' => 'Users',
                                'action' => 'download',
                            ], ['escape' => false]),
                            'escapeTitle' => false,
                        ]), 'Users', 'download'); ?>
                    </li>
                <?php endif; ?>
                <li>
                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_setting"/></svg>', [
                        'type' => 'button',
                        'title' => '一覧項目設定',
                        'class' => ['js_select_list_items', 'btn-edit', ' thEditBtn', 'tooltip'],
                        'data-type' => AdminListItem::TYPE_USER_LIST,
                        'data-title' => '一覧項目設定',
                        'escapeTitle' => false,
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($users) > 0): ?>
        <div class="fixedTable-option">
            <aside class="fixedTable-arrow">
                <button id="left-button" type="button" class="fixedTable-scroll-btn is-prev cmn-btn"></button>
                <button id="right-button" type="button" class="fixedTable-scroll-btn is-next cmn-btn"></button>
            </aside><!-- .search-parts -->
        </div>
        <div class="fixedTable-wrap">
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
                    <?php foreach ($users as $user): ?>
                        <tr class="parent">
                            <?= $this->element('Admin/AdminListItems/items_body', [
                                'listItems' => $searchForm->getListItems(),
                                'options' => [
                                    'mode' => 'user',
                                    'user' => $user,
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
                    <?php if (!$selectUser): ?>
                        <th class="checkbox fixedElm"><?= $this->element('Admin/Common/search/list_all_check') ?></th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php $duringReservation = $this->CommonData->duringContinueReservation() ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <?php if (!$selectUser): ?>
                            <td class="tool">
                                <ul>
                                    <?php if (!$user->isGuest()): ?>
                                        <li>
                                            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_info"></use></svg>', [
                                                'type' => 'button',
                                                'title' => '詳細',
                                                'class' => ['js_change_url', 'btn-tool', 'is-info'],
                                                'data-url' => $this->Url->build([
                                                    'prefix' => 'Admin',
                                                    'controller' => 'Users',
                                                    'action' => 'view',
                                                    'id' => $user->id,
                                                ], ['escape' => false]),
                                                'escapeTitle' => false,
                                            ]) ?>
                                        </li>
                                        <?php if (!$user->withdrew()): ?>
                                            <li>
                                                <?php if ($duringReservation) : ?>
                                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_reserve"></use></svg>', [
                                                        'type' => 'button',
                                                        'title' => '予約',
                                                        'class' => ['js_change_url_confirm', 'btn-tool', 'is-reserve'],
                                                        'data-confirm-message' => '現在保持している続けて予約情報が破棄されますがよろしいですか？',
                                                        'data-confirm-title' => 'この顧客で予約',
                                                        'data-url' => $this->Url->build([
                                                            'prefix' => 'Admin',
                                                            'controller' => 'Reservations',
                                                            'action' => 'calendar',
                                                            '?' => [
                                                                'user_id' => $user->id,
                                                                'initial' => true,
                                                            ],
                                                        ], ['escape' => false]),
                                                        'escapeTitle' => false,
                                                    ]) ?>
                                                <?php else : ?>
                                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_reserve"></use></svg>', [
                                                        'type' => 'button',
                                                        'title' => '予約',
                                                        'class' => ['js_change_url', 'btn-tool', 'is-reserve'],
                                                        'data-url' => $this->Url->build([
                                                            'prefix' => 'Admin',
                                                            'controller' => 'Reservations',
                                                            'action' => 'calendar',
                                                            '?' => [
                                                                'user_id' => $user->id,
                                                            ],
                                                        ], ['escape' => false]),
                                                        'escapeTitle' => false,
                                                    ]) ?>
                                                <?php endif; ?>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($user->canEdit()): ?>
                                            <li>
                                                <?php $editBtn = $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', [
                                                    'type' => 'button',
                                                    'title' => '編集',
                                                    'class' => ['js_change_url', 'btn-tool', 'is-editor'],
                                                    'data-url' => $this->Url->build([
                                                        'prefix' => 'Admin',
                                                        'controller' => 'Users',
                                                        'action' => 'edit',
                                                        'id' => $user->id,
                                                    ], ['escape' => false]),
                                                    'escapeTitle' => false,
                                                ]) ?>
                                                <?= $this->Authority->isAuthority($editBtn, 'Users', 'edit'); ?>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($user->canDelete()): ?>
                                            <li>
                                                <?php $deleteBtn = $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                                    'type' => 'button',
                                                    'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                                    'title' => '削除',
                                                    'data-confirm-message' => '顧客の削除をおこなってよろしいですか？',
                                                    'data-confirm-title' => '顧客の削除',
                                                    'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                                    'data-url' => $this->Url->build([
                                                        'prefix' => 'Admin',
                                                        'controller' => 'Users',
                                                        'action' => 'delete',
                                                        'id' => $user->id,
                                                    ], ['escape' => false]),
                                                    'escapeTitle' => false,
                                                ]) ?>
                                                <?= $this->Authority->isAuthority($deleteBtn, 'Users', 'delete'); ?>
                                            </li>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <li>
                                            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_info"></use></svg>', [
                                                'type' => 'button',
                                                'title' => '詳細',
                                                'class' => ['js_open_window', 'btn-tool', 'is-info'],
                                                'data-url' => $this->Url->build([
                                                    'prefix' => 'Admin',
                                                    'controller' => 'Reservations',
                                                    'action' => 'view',
                                                    'id' => $user->guest_reservation_id,
                                                ], ['escape' => false]),
                                                'escapeTitle' => false,
                                            ]) ?>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </td>
                        <?php else: ?>
                            <td class="w-120 min-maxW-120">
                                <?php if (!$user->isGuest() && !$user->withdrew()): ?>
                                    <?= $this->Form->button('この会員を選択', [
                                        'type' => 'button',
                                        'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_select_user'],
                                        'data-user-data' => json_encode([
                                            'id' => $user->id,
                                        ]),
                                    ]) ?>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <?php if (!$selectUser): ?>
                            <td class="checkbox">
                                <?= $this->element('Admin/Common/search/list_check', [
                                    'checkId' => $user->id,
                                ]) ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <aside class="search-parts mgt-20">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <?php if (!$this->fetch('noNavi')) : ?>
            <aside class="search-parts mgt-20 tar mgr-50">
                <?= $this->Form->button('一括削除', [
                    'type' => 'button',
                    'class' => ['cmn-btn', 'is-circle', 'is-pink', 'js_save_check_data_confirm'],
                    'data-confirm-message' => '該当データをまとめて削除します。\n次の確認画面で削除対象を確認してください\n※ゲスト予約で登録された「ゲスト」のデータは削除されません。',
                    'data-confirm-title' => 'データの削除',
                    'data-confirm-html' => h('削除したデータの復旧はできません。'),
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'Users',
                        'action' => 'deleteMany',
                    ], ['escape' => false]),
                ]) ?>
            </aside>
        <?php endif; ?>
    <?php else: ?>
        <?= $this->element('Admin/Common/search/no_result') ?>
    <?php endif; ?>
    <div class="hidden">
        <input type="hidden" class="js_save_check_url" value="<?= $this->Url->build([
            'prefix' => 'Admin/Ajax',
            'controller' => 'Users',
            'action' => 'save-check',
            '?' => $selectUserQuery,
        ]) ?>"/>
        <input type="hidden" class="js_reload_url" value="<?= $this->Url->build([
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery') + $selectUserQuery,
        ]) ?>"/>
        <?php if ($selectUser): ?>
            <input type="hidden" class="js_select_user_query" value="<?= h($selectUser) ?>"/>
        <?php endif; ?>
    </div>
</section>
