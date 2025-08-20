<?php
$this->assign('title', $formTypeName);
$this->assign('headerType', 'master');

$this->Breadcrumbs->add($formTypeName);
?>

<?= $this->Flash->render('formPatternsFinish') ?>
<?= $this->Flash->render('formPatternsErrors') ?>

<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'FormPatterns',
                'action' => 'list',
                '_name' => $formTypeUrl,
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'formPatterns_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => $formTypeName . '検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">パターン名</div>
                            </th>
                            <td>
                                <?= $this->Form->control('name', [
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
                        'controller' => 'FormPatterns',
                        'action' => 'list',
                        '_name' => $formTypeUrl
                    ], ['escape' => false]),
                ]) ?>
                <?= $this->Form->button('検索', ['type' => 'submit', 'class' => 'cmn-btn is-blue']) ?>
            </div>
        </div>
        <?= $this->Form->end(); ?>
    </div><!-- .panel-search-wrap -->
</section><!-- .panel-search -->
<section class="search-list mgt-20">
    <?php $this->Paginator->options(['url' => [
        'prefix' => 'Admin',
        'controller' => 'FormPatterns',
        'action' => 'list',
        '_name' => $formTypeUrl,
        '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
    ]]) ?>
    <aside class="search-parts">

    </aside>
    <aside class="search-parts d-flex mgt-20">
        <div class="search-parts-left">
            <?= $this->element('Admin/Common/search/page_counter') ?>
            <?= $this->Form->button('一括編集', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-search-editor'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'FormPatterns',
                    'action' => 'togetherEdit',
                    '_name' => $formTypeUrl
                ], ['escape' => false]),
            ]) ?>
            <?= $this->Form->button('新規登録', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'FormPatterns',
                    'action' => 'add',
                    '_name' => $formTypeUrl
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
                                'controller' => 'FormPatterns',
                                'action' => 'list',
                                '_name' => $formTypeUrl,
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($formPatterns) > 0): ?>
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
                                <span>ID </span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>パターン名 </span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'name',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>備考</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'remark',
                                ]) ?>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($formPatterns as $formPattern): ?>
                    <tr class="parent">
                        <td class="reId">
                            <?= h($formPattern->id) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($formPattern->name, 60, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate((string)$formPattern->remark, 60, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
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
            <?php foreach ($formPatterns as $formPattern): ?>
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
                                        'controller' => 'FormPatterns',
                                        'action' => 'edit',
                                        'id' => $formPattern->id,
                                        '_name' => $formTypeUrl . 'Edit',
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
                                        'controller' => 'FormPatterns',
                                        'action' => 'copy',
                                        'id' => $formPattern->id,
                                        '_name' => $formTypeUrl . 'Edit',
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <?php if ($formPattern->canDelete()) : ?>
                                <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                        'type' => 'button',
                                        'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                        'title' => '削除',
                                        'data-confirm-message' => $formTypeName . 'の削除をおこなってよろしいですか？',
                                        'data-confirm-title' => $formTypeName . 'の削除',
                                        'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'FormPatterns',
                                            'action' => 'delete',
                                            'id' => $formPattern->id,
                                            '_name' => $formTypeUrl . 'Edit',
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
