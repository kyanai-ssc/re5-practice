<?php
$this->assign('title', '予約枠設定 プラン一覧');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '予約枠設定',
    ['controller' => 'Events', 'action' => 'list']
);
$this->Breadcrumbs->add(
    'プラン一覧'
);
?>

<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'Events',
                'action' => 'eventPlanList',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'eventPlans_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => 'プラン検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tbody>
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
                                    <div class="ttl-input-wrap">予約枠名</div>
                                </th>
                                <td>
                                    <?= $this->Form->control('event_name', [
                                        'type' => 'text',
                                        'label' => false,
                                    ]) ?>
                                </td>
                            </tr>
                            <tr class="field-input">
                                <th class="ttl-input">
                                    <div class="ttl-input-wrap">予約枠ID</div>
                                </th>
                                <td>
                                    <?= $this->Form->control('event_id', [
                                        'type' => 'text',
                                        'label' => false,
                                    ]) ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
            </div>
            <div class="btn-box mgt-20">
                <?= $this->Form->button('リセット', [
                    'type' => 'reset',
                    'class' => ['js_change_url', 'cmn-btn', 'is-reset'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'Events',
                        'action' => 'eventPlanList',
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
        </div>
        <div class="search-parts-center">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </div>
        <div class="search-parts-right">
            <ul class="d-flex">
                <li>
                    <?= $this->element('Admin/Common/search/limit', [
                        'options' => [
                            'class' => ['js_change_search_limit'],
                            'data-url' => $this->Url->build([
                                'prefix' => 'Admin',
                                'controller' => 'Events',
                                'action' => 'eventPlanList',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
                <li>
                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_download"/></svg>', [
                        'type' => 'button',
                        'class' => ['js_post_link_plural', 'btn-list', 'fileDown', 'is-download'],
                        'title' => 'プランダウンロード',
                        'aria-describedby' => 'ui-id-60',
                        'data-url' => $this->Url->build([
                            'prefix' => 'Admin',
                            'controller' => 'Events',
                            'action' => 'planDownload',
                        ], ['escape' => false]),
                        'escapeTitle' => false,
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($eventPlans) > 0): ?>
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
                                <span>プランID</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>プラン名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'name',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>予約枠ID</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'event_id'
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>予約枠名</span>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($eventPlans as $eventPlan): ?>
                    <tr class="parent">
                        <td class="reId left-line">
                            <?= h($eventPlan->id) ?>
                        </td>
                        <td>
                            <?= h($eventPlan->name) ?>
                        </td>
                        <td>
                            <?= h($eventPlan->event_id) ?>
                        </td>
                        <td>
                            <?= h($eventPlan->event->name) ?>
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
