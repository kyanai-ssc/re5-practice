<?php
$this->assign('title', '顧客の権限設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add('顧客の権限設定');
?>

<?= $this->Flash->render('userAuthoritiesFinish') ?>
<?= $this->Flash->render('userAuthoritiesErrors') ?>
<section class="search-list mgt-20">
    <aside class="search-parts d-flex mgt-20">
        <div class="search-parts-left">
            <?= $this->element('Admin/Common/search/page_counter') ?>
            <?= $this->Form->button('新規登録', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'UserAuthorities',
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
                                'controller' => 'UserAuthorities',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($userAuthorities) > 0): ?>
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
                                <span>権限ID</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>権限名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'name',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <span>利用許可画面</span>
                        </th>
                        <th>
                            <span>予約状況初期表示</span>
                        </th>
                        <th>
                            <span>ログイン時の<br>表示項目</span>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>顧客情報の表示パターン</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'form_pattern_id',
                                ]) ?>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($userAuthorities as $userAuthority): ?>
                    <tr class="parent">
                        <td class="reId">
                            <?= h($userAuthority->id) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($userAuthority->name, 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?php $userAuthorityString = [] ?>
                            <?php if (is_array($userAuthority->access)) : ?>
                                <?php foreach ($userAuthority->access as $access) : ?>
                                    <?php $userAuthorityString[] = $valueOptions['frontPageActions'][$access]; ?>
                                <?php endforeach; ?>
                                <?= $this->Text->truncate(implode(',', $userAuthorityString), 50, ['tooltip' => true, 'escape' => true]) ?>
                            <?php else : ?>
                                なし
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($valueOptions['calendarType'][$userAuthority->calendar_type_default], 100, ['tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?php if (!empty($userAuthority->login_name_form_item_id) && !empty($valueOptions['loginNameFormItemId'][$userAuthority->login_name_form_item_id])) : ?>
                                <?= $this->Text->truncate($valueOptions['loginNameFormItemId'][$userAuthority->login_name_form_item_id], 100, ['tooltip' => true, 'escape' => true]) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $this->Html->link($valueOptions['userFormPatterns'][$userAuthority->form_pattern_id],
                                ['controller' => 'formPatterns', 'action' => 'edit', '_name' => 'formPatternsUserEdit', 'id' => $userAuthority->form_pattern_id]);
                            ?>
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
            <?php foreach ($userAuthorities as $userAuthority): ?>
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
                                        'controller' => 'UserAuthorities',
                                        'action' => 'edit',
                                        'id' => $userAuthority->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li>
                                <?php if ($userAuthority->cnaDelete()): ?>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                        'type' => 'button',
                                        'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                        'title' => '削除',
                                        'data-confirm-message' => '顧客の権限設定の削除をおこなってよろしいですか？',
                                        'data-confirm-title' => '顧客の権限設定の削除',
                                        'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'UserAuthorities',
                                            'action' => 'delete',
                                            'id' => $userAuthority->id,
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
