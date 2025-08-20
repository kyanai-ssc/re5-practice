<?php
$this->assign('title', '管理者情報');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '管理者情報'
);
?>

<?= $this->Flash->render('adminsFinish') ?>
<?= $this->Flash->render('adminsErrors') ?>
<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'Admins',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'admins_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => '管理者検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">担当カテゴリー</div>
                            </th>
                            <td>
                                <?= $this->Label->renderSelect([
                                    'type' => $this->Configure->read('Master.label.type.other'),
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">管理者権限</div>
                            </th>
                            <td>
                                <?= $this->Template->checkbox('authority', [
                                    'type' => 'multicheckbox',
                                    'label' => false,
                                    'options' => $valueOptions['authority'],
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">管理者ログインID</div>
                            </th>
                            <td>
                                <?= $this->Form->control('login_id', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </div>
            <div class="btn-box mgt-20">
                <?= $this->Form->button('リセット', [
                    'type' => 'reset',
                    'class' => ['js_change_url', 'cmn-btn', 'is-reset'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'Admins',
                        'action' => 'list',
                    ], ['escape' => false]),
                ]) ?>
                <?= $this->Form->button('検索', ['type' => 'submit', 'class' => 'cmn-btn is-blue']) ?>
            </div>
        </div>
        <?= $this->Form->end(); ?>
    </div><!-- .panel-search-wrap -->
</section><!-- .panel-search -->
<section class="search-list mgt-20">
    <aside class="search-parts d-flex mgt-20">
        <div class="search-parts-left">
            <?= $this->element('Admin/Common/search/page_counter') ?>
            <?php if ($this->Authority->isAuthority(true, 'AdminAuthorities', 'list')) : ?>
                <?= $this->Form->button('利用許可画面のパターン設定', [
                    'type' => 'button',
                    'class' => ['js_change_url', 'cmn-btn', 'is-search-editor'],
                    'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'AdminAuthorities',
                    'action' => 'list',
                    ], ['escape' => false]),
                ]) ?>
            <?php endif; ?>
            <?php if ($canAddAdmin): ?>
                <?= $this->Form->button('新規登録', [
                    'type' => 'button',
                    'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'Admins',
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
                            'data-url' => $this->Url->build([
                                'prefix' => 'Admin',
                                'controller' => 'Admins',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($admins) > 0): ?>
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
                        <th>
                            <div class="sort_wrap">
                                <span>担当カテゴリー</span>
                                <?= $this->element('Admin/Common/search/sort', ['key' => 'label_id',]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>管理者権限</span>
                                <?= $this->element('Admin/Common/search/sort', ['key' => 'authority',]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>利用許可画面のパターン名</span>
                                <?= $this->element('Admin/Common/search/sort', ['key' => 'admin_authority_id',]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>管理者ログインID</span>
                                <?= $this->element('Admin/Common/search/sort', ['key' => 'login_id',]) ?>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr class="parent">
                        <td>
                            <?php if (isset($admin->label)): ?>
                                <?= $this->Text->truncate($admin->label->name, 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= h($this->Configure->read('Master.admin.authority.' . $admin->authority)) ?>
                        </td>
                        <td>
                            <?= h($admin->admin_authority->name) ?>
                        </td>
                        <td>
                            <?= h($admin->login_id) ?>
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
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td class="tool">
                        <ul>
                            <?php if ($admin->canEdit()): ?>
                                <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', ['type' => 'button',
                                        'title' => '編集',
                                        'class' => ['js_change_url', 'btn-tool', 'is-editor'],
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Admins',
                                            'action' => 'edit',
                                            'id' => $admin->id,
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($admin->canDelete()): ?>
                                <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', ['type' => 'button',
                                        'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                        'title' => '削除',
                                        'data-confirm-message' => '管理者の削除をおこなってよろしいですか？',
                                        'data-confirm-title' => '管理者の削除',
                                        'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                        'data-url' => $this->Url->build(['prefix' => 'Admin',
                                            'controller' => 'Admins',
                                            'action' => 'delete',
                                            'id' => $admin->id,
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                </li>
                            <?php endif; ?>
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
