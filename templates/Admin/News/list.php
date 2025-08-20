<?php

use App\Model\Entity\UserAuthority;
$this->assign('headerType', 'data');
$this->assign('title', 'お知らせ');
$this->Breadcrumbs->add(
    'お知らせ'
);
?>

<?= $this->Flash->render('newsFinish') ?>
<?= $this->Flash->render('newsErrors') ?>

<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'News',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'news_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => 'お知らせ検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">タイトル</div>
                            </th>
                            <td>
                                <?= $this->Form->control('title', [
                                    'type' => 'text',
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">カテゴリー</div>
                            </th>
                            <td>
                                <?= $this->Label->renderSelect([
                                    'type' => $this->Configure->read('Master.label.type.other')
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
                                    'options' => $valueOptions['userAuthorityId'],
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">掲載期間</div>
                            </th>
                            <td>
                                <div class="d-flex">
                                    <?= $this->Form->control('public_from', [
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => ['js-datepicker-time'],
                                    ]) ?>
                                    <span class="txt mgl-10 mgr-10">から</span>
                                    <?= $this->Form->control('public_to', [
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => ['js-datepicker-time',]
                                    ]) ?>
                                </div>
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
                        'controller' => 'News',
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
                    'controller' => 'News',
                    'action' => 'add',
                ], ['escape' => false]),
            ]) ?>

            <?= $this->Form->button('お知らせ表示設定', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'News',
                    'action' => 'setting',
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
                                'controller' => 'News',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($newsList) > 0): ?>
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
                        <th class="w-120 min-maxW-120">
                            <div class="sort_wrap">
                                <span>表示順</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'sort_no',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>カテゴリー </span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'label_id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>タイトル</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'title',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <span>閲覧権限</span>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>掲載期間 </span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'public_from',
                                ]) ?>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($newsList as $news): ?>
                    <tr class="parent">
                        <td class="w-120 min-maxW-120">
                            <?= h($news->sort_no) ?>
                        </td>
                        <td>
                            <?php if (isset($news->label->name)) : ?>
                                <?= h($news->label->name) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($news->title, 60, ['tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?php if (!empty($news->news_authorities)) : ?>
                                <?php foreach ($news->news_authorities as $newsAuthorities) : ?>
                                    <p><?= h($newsAuthorities->user_authority->name) ?></p>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <?= h($this->Configure->read('Master.userAuthority.selectAll.' . UserAuthority::SELECT_ALL)) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $this->Template->getFromToDisplay($this->Template->displayDayAndWeek($news->public_from), $this->Template->displayDayAndWeek($news->public_to), '～', true) ?>
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
            <?php foreach ($newsList as $news): ?>
                <tr>
                    <td class="tool">
                        <ul>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_preview"></use></svg>', [
                                    'type' => 'button',
                                    'title' => 'プレビュー',
                                    'class' => ['js_open_window', 'btn-tool', 'is-preview', 'tooltip'],
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'News',
                                        'action' => 'preview',
                                        'id' => $news->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', [
                                    'type' => 'button',
                                    'title' => '編集',
                                    'class' => ['js_change_url', 'btn-tool', 'is-editor'],
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'News',
                                        'action' => 'edit',
                                        'id' => $news->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                    'type' => 'button',
                                    'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                    'title' => '削除',
                                    'data-confirm-message' => 'お知らせの削除をおこなってよろしいですか？',
                                    'data-confirm-title' => 'お知らせの削除',
                                    'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'News',
                                        'action' => 'delete',
                                        'id' => $news->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table><!-- .result-table -->
        <aside class="search-parts mgt-20">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>
