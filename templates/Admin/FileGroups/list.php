<?php
$this->assign('title', 'ファイル管理');
$this->assign('headerType', 'data');
$this->Html->script('admin/file-groups/list', [
    'block' => true,
]);
$this->Breadcrumbs->add(
    'ファイル管理'
);
$this->assign('noNavi', $noNavi);

$this->Paginator->options(['url' => [
    'prefix' => 'Admin',
    'controller' => 'FileGroups',
    'action' => 'list',
    '?' => $this->Configure->read('Setting.searchInput.searchQuery') + $selectFile,
]])

?>

<?= $this->Flash->render('fileGroupsFinish') ?>
<?= $this->Flash->render('fileGroupsErrors') ?>
<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                    'controller' => 'FileGroups',
                    'action' => 'list',
                    '?' => $this->Configure->read('Setting.searchInput.searchQuery') + $selectFile,
                ],
            'idPrefix' => 'fileGroups_search',
            'novalidate' => true,
        ]) ?>

        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => 'グループ検索',
        ]) ?>

        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">グループ名</div>
                            </th>
                            <td>
                                <?= $this->Form->control('file_group_id', [
                                    'type' => 'select',
                                    'label' => false,
                                    'class' => ['select'],
                                    'options' => $valueOptions['fileGroups'],
                                    'empty' => '----'
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
                        'controller' => 'FileGroups',
                        'action' => 'list',
                        '?' => $selectFile,
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
            <?= $this->Form->button('グループ新規登録', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'FileGroups',
                    'action' => 'add',
                    '?' => $selectFile,
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
                                'controller' => 'FileGroups',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery') + $selectFile,
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($files) > 0): ?>
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
                                <span>グループ<br/>ID</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'file_group_id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>グループ名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'file_group_name',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <span>ファイル名</span>
                        </th>
                        <th>
                            <span>パス</span>
                        </th>
                        <th>
                            <span>説明文</span>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($files as $file): ?>
                    <tr class="parent">
                        <td class="reId">
                            <?= h($file->file_group->id) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($file->file_group_name, 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($file->file_name, 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?= $this->Html->link('/file/' . $file->file_group->directory . '/' . $file->full_file_name,
                                ['controller' => 'File', 'action' => 'index', 'prefix' => 'User', $file->file_group->directory, $file->full_file_name], ['target' => '_blank']);
                            ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($file->description, 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
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
            <?php foreach ($files as $file): ?>
                <tr>
                    <?php if (empty($selectFile)) : ?>
                        <td class="tool">
                            <ul>
                                <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', ['type' => 'button',
                                        'title' => '編集',
                                        'class' => ['js_change_url', 'btn-tool', 'is-editor'],
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'FileGroups',
                                            'action' => 'edit',
                                            'id' => $file->file_group->id,
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                </li>
                                <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', ['type' => 'button',
                                        'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                        'title' => '削除',
                                        'data-confirm-message' => 'ファイルの削除をおこなってよろしいですか？',
                                        'data-confirm-title' => 'ファイルの削除',
                                        'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'FileGroups',
                                            'action' => 'delete',
                                            'id' => $file->id,
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                </li>
                            </ul>
                        </td>
                    <?php else : ?>
                        <td class="w-180">
                            <?= $this->Form->button('このファイルを選択', [
                                'type' => 'button',
                                'class' => ['js_click_once', 'js_file_select', 'cmn-btn', 'is-blue', 'is-circle'],
                                'data-file' => '/file/' . $file->file_group->directory . '/' . $file->full_file_name,
                                'data-title' => 'ファイル管理から選択'
                            ]) ?>

                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <aside class="search-parts mgt-20 ">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>
