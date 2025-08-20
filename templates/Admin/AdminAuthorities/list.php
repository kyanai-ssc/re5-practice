<?php
$this->assign('title', '利用許可画面のパターン設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '管理者情報',
    ['prefix' => 'Admin', 'controller' => 'Admins', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '利用許可画面のパターン設定'
);
?>

<?= $this->Flash->render('adminAuthoritiesFinish') ?>
<?= $this->Flash->render('adminAuthoritiesErrors') ?>
<section class="search-list mgt-20">
    <aside class="search-parts d-flex mgt-20">
        <div class="search-parts-left">
            <?= $this->element('Admin/Common/search/page_counter') ?>
            <?= $this->Form->button('新規登録', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'AdminAuthorities',
                    'action' => 'add',
                ], ['escape' => false]),
            ]) ?>
        </div><!-- .search-counter -->
        <div class="search-parts-center">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </div>
        <div class="search-parts-right">
            <ul class="d-flex">
                <li>
                    <?= $this->element('Admin/Common/search/limit', [
                        'options' => [
                            'data-url' => $this->Url->build([
                                'prefix' => 'Admin',
                                'controller' => 'AdminAuthorities',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($adminAuthorities) > 0): ?>
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
                    <tr>
                        <th class="reId">
                            <div class="sort_wrap">
                                <span>設定ID</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>利用許可画面のパターン名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'name',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <span>利用許可画面（各種設定メニュー）</span>
                        </th>
                        <th>
                            <span>利用許可画面（運用メニュー）</span>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($adminAuthorities as $adminAuthority): ?>
                    <tr class="parent">
                        <td class="reId">
                            <?= h($adminAuthority->id) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($adminAuthority->name, 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?php $adminAuthorityString = [] ?>
                            <?php if (is_array($adminAuthority->access_setting)) : ?>
                                <?php foreach ($adminAuthority->access_setting as $access_setting) : ?>
                                    <?php if (!empty($valueOptions['accessSettingList'][$access_setting])) : ?>
                                        <?php $adminAuthorityString[] = $valueOptions['accessSettingList'][$access_setting]; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?= $this->Text->truncate(implode(',', $adminAuthorityString), 50, ['tooltip' => true, 'escape' => true]) ?>
                            <?php else : ?>
                                なし
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php $adminAuthorityString = [] ?>
                            <?php if (is_array($adminAuthority->access_operator)) : ?>
                                <?php foreach ($adminAuthority->access_operator as $access_operator) : ?>
                                    <?php $adminAuthorityString[] = $valueOptions['accessOperatorList'][$access_operator]; ?>
                                <?php endforeach; ?>
                                <?= $this->Text->truncate(implode(',', $adminAuthorityString), 50, ['tooltip' => true, 'escape' => true]) ?>
                            <?php else : ?>
                                なし
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <table class="fixedTableLeft cmn-table status-table js_check_popup_table">
            <thead>
            <tr>
                <th class="tool fixedElm"><span>操作</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($adminAuthorities as $adminAuthority): ?>
                <tr>
                    <td class="tool">
                        <ul>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', [
                                    'type' => 'button',
                                    'title' => '編集',
                                    'class' => ['js_change_url', 'btn-tool', 'is-editor'],
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'AdminAuthorities',
                                        'action' => 'edit',
                                        'id' => $adminAuthority->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_copy"></use></svg>', [
                                    'type' => 'button',
                                    'title' => '複製',
                                    'class' => ['js_change_url', 'btn-tool', 'is-copy'],
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'AdminAuthorities',
                                        'action' => 'copy',
                                        'id' => $adminAuthority->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li>
                                <?php if ($adminAuthority->cnaDelete()): ?>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                        'type' => 'button',
                                        'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                        'title' => '削除',
                                        'data-confirm-message' => '利用許可画面のパターン設定の削除をおこなってよろしいですか？',
                                        'data-confirm-title' => '利用許可画面のパターン設定の削除',
                                        'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'AdminAuthorities',
                                            'action' => 'delete',
                                            'id' => $adminAuthority->id,
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <aside class="search-parts mgt-20">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>
