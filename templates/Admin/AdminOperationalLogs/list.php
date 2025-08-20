<?php
$this->assign('title', '操作ログ');
$this->assign('headerType', 'data');

$this->Breadcrumbs->add(
    '操作ログ'
);

$this->Html->script('admin/admin-operational-logs/list', [
    'block' => true,
]);
?>

<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'AdminOperationalLogs',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'adminOperationalLogs_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => '操作ログ検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">管理者ログインID</div>
                            </th>
                            <td>
                                <?= $this->Form->control('login_id', [
                                    'type' => 'text',
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
                                    'options' => $valueOptions['authority'],
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">操作機能</div>
                            </th>
                            <td>
                                <?= $this->Form->control('operated_function', [
                                    'type' => 'select',
                                    'multiple' => true,
                                    'label' => false,
                                    'options' => $valueOptions['function'],
                                    'class' => ['js_multiple_select'],
                                    'data-select-all-text' => '全て',
                                    'empty' => false,
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">操作時間</div>
                            </th>
                            <td class="d-flex">
                                <?= $this->Form->control('created_from', [
                                    'type' => 'text',
                                    'class' => ['js-datepicker-time']
                                ]) ?>
                                <span class="txt mgl-10 mgr-10">から</span>
                                <?= $this->Form->control('created_to', [
                                    'type' => 'text',
                                    'class' => ['js-datepicker-time']
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
                        'controller' => 'AdminOperationalLogs',
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
                                'controller' => 'AdminOperationalLogs',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
                <li>
                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_download"/></svg>', [
                        'type' => 'button',
                        'class' => ['js_post_link_plural', 'btn-list', 'fileDown', 'is-download'],
                        'title' => '操作ログダウンロード',
                        'aria-describedby' => 'ui-id-60',
                        'data-url' => $this->Url->build([
                            'prefix' => 'Admin',
                            'controller' => 'AdminOperationalLogs',
                            'action' => 'download',
                        ], ['escape' => false]),
                        'escapeTitle' => false,
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($adminOperationalLogs) > 0): ?>
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
                        <th class="w-200 min-maxW-200">
                            <div class="sort_wrap">
                                <span>操作時間</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'created',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>管理者ログインID</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'login_id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>操作機能</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'operated_function',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>操作内容</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'operated_type',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <span>操作データ</span>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($adminOperationalLogs as $adminOperationalLog): ?>
                    <tr class="parent">
                        <td class="w-200 min-maxW-200">
                            <?= h($this->Template->displayDayAndWeek($adminOperationalLog->created, 'H:i:s')) ?>
                        </td>
                        <td>
                            <?= h($adminOperationalLog->login_id) ?>
                        </td>
                        <td>
                            <?= h($this->Configure->read('Master.operation.function.' . $adminOperationalLog->operated_function)) ?>
                        </td>
                        <td>
                            <?= h($this->Configure->read('Master.operation.type.' . $adminOperationalLog->operated_type)) ?>
                        </td>
                        <td>
                            <?= h($adminOperationalLog->operated_data) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <table class="fixedTableLeft cmn-table status-table js_check_popup_table">
            <thead>
            <tr>
            </tr>
            </thead>
        </table>
        <aside class="search-parts mgt-20">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>
