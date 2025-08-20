<?php
$this->assign('title', '決済エラー回数一覧');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '決済エラー回数一覧'
);
?>
<?= $this->Flash->render('paymentErrorsFinish') ?>
<?= $this->Flash->render('paymentErrorsErrors') ?>

<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'PaymentErrors',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'paymentErrors_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => '決済エラー回数一覧検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">IPアドレス</div>
                            </th>
                            <td>
                                <?= $this->Form->control('ip_address', [
                                    'type' => 'text',
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">最終エラー日時</div>
                            </th>
                            <td>
                                <div class="d-flex">
                                    <?= $this->Form->control('last_error_timestamp_from', [
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => ['js-datepicker-time'],
                                    ]) ?>
                                    <span class="txt mgl-10 mgr-10">から</span>
                                    <?= $this->Form->control('last_error_timestamp_to', [
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => ['js-datepicker-time',]
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">ステータス</div>
                            </th>
                            <td>
                                <?= $this->Template->checkbox('lock_timestamp', [
                                    'type' => 'multicheckbox',
                                    'options' => $valueOptions['status'],
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
                        'controller' => 'PaymentErrors',
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
                                'controller' => 'PaymentErrors',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($paymentErrors) > 0): ?>
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
                                <span>IPアドレス</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'ip_address',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>最終エラー日時</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'last_error_timestamp',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>累積エラー回数</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'all_error_count',
                                ]) ?>
                            </div>
                        </th>
                        <th class="sort_wrap">
                            <div>
                                <span>ステータス</span>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($paymentErrors as $paymentError): ?>
                    <tr class="parent">
                        <td>
                            <?= h($paymentError->ip_address) ?>
                        </td>
                        <td>
                            <?= $this->Template->displayDayAndWeek($paymentError->last_error_timestamp, 'H:i:s') ?>
                        </td>
                        <td>
                            <?= h($paymentError->all_error_count) ?>回
                        </td>
                        <td>
                            <?php if($paymentError->isLock()): ?>
                                ロック
                            <?php else: ?>
                                ロック解除
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
            <?php foreach ($paymentErrors as $paymentError): ?>
                <tr>
                    <td class="tool">
                        <ul>
                            <?php if ($paymentError->isLock()) : ?>
                                <li>
                                    <?= $this->Form->button('解除', [
                                        'type' => 'button',
                                        'class' => ['js_post_confirm', 'cmn-btn', 'is-blue', 'unlock'],
                                        'title' => '解除',
                                        'data-confirm-message' => 'ロックの解除をおこなってよろしいですか？',
                                        'data-confirm-title' => 'ロック解除',
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'PaymentErrors',
                                            'action' => 'unlock',
                                            'id' => $paymentError->id,
                                        ], ['escape' => false]),
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
