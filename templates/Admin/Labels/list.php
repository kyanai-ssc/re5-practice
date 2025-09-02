<?php
$this->assign('title', 'カテゴリー設定');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add(
    'カテゴリー設定'
);
?>

<?= $this->Flash->render('labelsFinish') ?>
<?= $this->Flash->render('labelsErrors') ?>
<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'Labels',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'labels_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => 'カテゴリー検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">上位カテゴリー</div>
                            </th>
                            <td>
                                <?= $this->Label->renderSelect([
                                    'type' => $this->Configure->read('Master.label.type.other'),
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">階層表示</div>
                            </th>
                            <td>
                                <?= $this->Template->radio('display_level', [
                                    'type' => 'radio',
                                    'label' => false,
                                    'class' => ['cmn-radio'],
                                    'options' => $valueOptions['displayLevel'],
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">カテゴリー名</div>
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
                                    'label' => false,
                                    'options' => $valueOptions['publicFlg'],
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">閲覧権限</div>
                            </th>
                            <td>
                                <?= $this->Template->checkbox('user_authority_id', [
                                    'type' => 'multicheckbox',
                                    'label' => false,
                                    'options' => $valueOptions['userAuthorityId'],
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
                        'controller' => 'Labels',
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
            <?php if (empty($lowerLabelData)): ?>
                <?= $this->Form->button('新規登録', [
                    'type' => 'button',
                    'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'Labels',
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
                                'controller' => 'Labels',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($labels) > 0): ?>
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
                                <span>カテゴリーID</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>上位カテゴリー </span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'parent_id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>カテゴリー名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'name',
                                ]) ?>
                            </div>
                        </th>
                        <th class="min-maxW-120 w-120">
                            <div class="sort_wrap">
                                <span>表示順</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'sort_no',
                                ]) ?>
                            </div>
                        </th>
                        <th class="min-maxW-120 w-120">
                            <div class="sort_wrap">
                                <span>公開設定</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'public_flg',
                                ]) ?>
                            </div>
                        </th>
                        <th class="min-maxW-120 w-120">
                            <div class="sort_wrap">
                                <span>閲覧権限</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'user_authority_id',
                                ]) ?>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($labels as $label): ?>
                    <tr class="parent">
                        <td class="reId">
                            <?= h($label->data['id']) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate((string)$this->Label->getParentName($label->data['parent']), 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($label->data['name'], 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td class="min-maxW-120 w-120">
                            <?= h($label->data['sort_no']) ?>
                        </td>
                        <td class="min-maxW-120 w-120">
                            <?= h($valueOptions['publicFlg'][$label->data['public_flg']]) ?>
                        </td>
                        <td class="min-maxW-120 w-120">
                            <?php if (!empty($label->label_authorities)) : ?>
                                <?php foreach ($label->label_authorities as $labelAuthorities) : ?>
                                        <p><?= h($this->Label->getUserAuthorityName($labelAuthorities->user_authority_id)) ?></p>
                                <?php endforeach; ?>
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
            <?php foreach ($labels as $label): ?>
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
                                        'controller' => 'Labels',
                                        'action' => 'edit',
                                        'id' => $label->data['id'],
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <?php if ($label->canDelete()) : ?>
                                <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                        'type' => 'button',
                                        'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                        'title' => '削除',
                                        'data-confirm-message' => 'カテゴリーの削除をおこなってよろしいですか？',
                                        'data-confirm-title' => 'カテゴリーの削除',
                                        'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Labels',
                                            'action' => 'delete',
                                            'id' => $label->data['id'],
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
