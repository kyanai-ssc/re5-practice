<?php
$this->assign('title', 'オプション設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    'オプション設定'
);
?>
<?= $this->Flash->render('optionsFinish') ?>
<?= $this->Flash->render('optionsErrors') ?>

<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'Options',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'options_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => 'オプション検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">オプション名</div>
                            </th>
                            <td>
                                <?= $this->Form->control('name', [
                                    'type' => 'text',
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">適用期間</div>
                            </th>
                            <td>
                                <?= $this->Form->control('date', [
                                    'type' => 'text',
                                    'class' => ['js-datepicker']
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
                        'controller' => 'Options',
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
                    'controller' => 'Options',
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
                                'controller' => 'Options',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($options) > 0): ?>
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
                                <span>オプション名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'name',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>適用期間</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'usage_timestamp_from',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>在庫数</span>
                            </div>
                        </th>
                        <th class="w-200 min-maxW-200">
                            <div>
                                <span>例外日設定（適用期間［在庫数]）</span>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($options as $option): ?>
                    <tr class="parent">
                        <td>
                            <?= h($option->name) ?>
                        </td>
                        <td>
                            <?= $this->Template->getFromToDisplay($this->Template->displayDayAndWeek($option->usage_timestamp_from, 'H:i'), $this->Template->displayDayAndWeek($option->usage_timestamp_to, 'H:i'), '～', true) ?>
                        </td>
                        <td>
                            <?= h($option->stock) ?>
                        </td>
                        <td class="w-200 min-maxW-200">
                            <ul>
                                <?php if (count($option->option_stock_settings) >= 1) : ?>
                                    <?php foreach ($option->option_stock_settings as $optionStockSetting) : ?>
                                        <li>
                                        <span>
                                            <?= h($this->Template->getFromToDisplay($this->Template->displayDayAndWeek($optionStockSetting->usage_timestamp_from, 'H:i'), $this->Template->displayDayAndWeek($optionStockSetting->usage_timestamp_to, 'H:i'), '～')) ?>
                                        </span>
                                            <span>[<?= h($optionStockSetting->stock) ?>]</span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
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
            <?php foreach ($options as $option): ?>
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
                                        'controller' => 'Options',
                                        'action' => 'edit',
                                        'id' => $option->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <?php if ($option->canDelete()) : ?>
                                <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                        'type' => 'button',
                                        'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                        'title' => '削除',
                                        'data-confirm-message' => 'オプションの削除をおこなってよろしいですか？',
                                        'data-confirm-title' => 'オプションの削除',
                                        'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Options',
                                            'action' => 'delete',
                                            'id' => $option->id,
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
        </table><!-- .result-table -->
        <aside class="search-parts mgt-20">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>
