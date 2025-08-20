<?php
$this->assign('title', '絞り込みキーワード設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '絞り込みキーワード設定'
);
?>
<?= $this->Flash->render('tagsFinish') ?>
<?= $this->Flash->render('tagsErrors') ?>
<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'Tags',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'tags_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => '絞り込みキーワード検索',
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
                                <?= $this->Form->control('name', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">公開設定</div>
                            </th>
                            <td>
                                <?= $this->Template->checkbox('public_flg', [
                                    'type' => 'multicheckbox',
                                    'label' => '',
                                    'options' => $valueOptions['publicFlg'],
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
                        'controller' => 'Tags',
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
            <?= $this->Form->button('新規登録', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Tags',
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
                                'controller' => 'Tags',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($tags) > 0): ?>
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
                                <span>グループ名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'name',
                                ]) ?>
                            </div>
                        </th>
                        <th class="w-120 min-maxW-120">
                            <div class="sort_wrap">
                                <span>表示順</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'sort_no',
                                ]) ?>
                            </div>
                        </th>
                        <th class="w-120 min-maxW-120">
                            <div class="sort_wrap">
                                <span>公開設定 </span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'public_flg',
                                ]) ?>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($tags as $tag): ?>
                    <tr class="parent">
                        <td>
                            <?= $this->Text->truncate($tag->name, 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td class="w-120 min-maxW-120">
                            <?= h($tag->sort_no) ?>
                        </td>
                        <td class="w-120 min-maxW-120">
                            <?= h($this->Configure->read('Master.label.publicFlg.' . $tag->public_flg)) ?>
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
            <?php foreach ($tags as $tag): ?>
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
                                        'controller' => 'Tags',
                                        'action' => 'edit',
                                        'id' => $tag->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                    'type' => 'button',
                                    'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                    'title' => '削除',
                                    'data-confirm-message' => '絞り込みキーワードの削除をおこなってよろしいですか？',
                                    'data-confirm-title' => '絞り込みキーワードの削除',
                                    'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'Tags',
                                        'action' => 'delete',
                                        'id' => $tag->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tbody>
        </table><!-- .result-table -->
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>
