<?php
$this->assign('title', '不達メール');
$this->assign('headerType', 'data');
$this->Breadcrumbs->add(
    '不達メール'
);
$this->Html->script('admin/bounce-mails/list', [
    'block' => true,
]);
?>

<?= $this->Flash->render('bounceMailsFinish') ?>
<?= $this->Flash->render('bounceMailsErrors') ?>

<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'BounceMails',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'bounceMails_search',
            'novalidate' => true,
        ]) ?>
        <?php $this->Form->unlockField('id'); ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => '不達メール検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">メール送信</div>
                            </th>
                            <td>
                                <?= $this->Template->checkbox('send_exclude_flg', [
                                    'type' => 'multicheckbox',
                                    'label' => false,
                                    'options' => $valueOptions['sendExclude'],
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">顧客ID</div>
                            </th>
                            <td>
                                <?= $this->Form->control('user_id', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">メールアドレス</div>
                            </th>
                            <td>
                                <?= $this->Form->control('mail', [
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
                        'controller' => 'BounceMails',
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
    <?php if (count($bounceMails) > 0): ?>
    <aside class="search-parts d-flex mgt-20">
        <div class="search-parts-left">
            <?= $this->element('Admin/Common/search/page_counter') ?>
            <?= $this->Form->button('「送信しない」へ一括編集', [
                'type' => 'button',
                'class' => ['js_save_check_data', 'cmn-btn', 'is-search-editor'],
                'data-method' => 'post',
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'BounceMails',
                    'action' => 'sendOff',
                ], ['escape' => false]),
            ]) ?>
            <?= $this->Form->button('「送信する」へ一括編集', [
                'type' => 'button',
                'class' => ['js_save_check_data', 'cmn-btn', 'is-search-editor'],
                'data-method' => 'post',
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'BounceMails',
                    'action' => 'sendOn',
                ], ['escape' => false]),
            ]) ?>
        </div>
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
                                'controller' => 'BounceMails',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
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
                                <span>送信対象</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'send_exclude_flg',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>メールアドレス</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'mail',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>送信しないへ自動切り換え</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'remaining_number',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>不達累積件数</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'total_number',
                                ]) ?>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($bounceMails as $bounceMail): ?>
                    <tr class="parent">
                        <td><?= h($valueOptions['sendExclude'][$bounceMail->send_exclude_flg]) ?></td>
                        <td><?= h($bounceMail->mail) ?></td>
                        <td>残<?= h($bounceMail->remaining_number) ?></td>
                        <td><?= h($bounceMail->total_number) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <table class="fixedTableLeft cmn-table status-table js_check_popup_table">
            <thead>
            <tr>
                <th class="checkbox fixedElm">
                    <?= $this->element('Admin/Common/search/list_all_check') ?>
                </th>
                <th class="tool fixedElm"><span>操作</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($bounceMails as $bounceMail): ?>
                <tr>
                    <td class="checkbox">
                        <?= $this->element('Admin/Common/search/list_check', [
                            'checkId' => $bounceMail->id,
                        ]) ?>
                    </td>
                    <td class="tool">
                        <ul>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_info"/></svg>', [
                                    'type' => 'button',
                                    'class' => ['js_change_url', 'btn-tool', 'is-info'],
                                    'title' => '詳細',
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'BounceMails',
                                        'action' => 'view',
                                        'id' => $bounceMail->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                    'type' => 'button',
                                    'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                    'title' => '削除',
                                    'data-confirm-message' => '不達メールの削除をおこなってよろしいですか？',
                                    'data-confirm-title' => '不達メールの削除',
                                    'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'BounceMails',
                                        'action' => 'delete',
                                        'id' => $bounceMail->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <aside class="search-parts mgt-20">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <aside class="search-parts mgt-20 tar mgr-50">
            <?= $this->Form->button('一括削除', [
                'type' => 'button',
                'class' => ['js_save_check_data_confirm', 'cmn-btn', 'is-circle', 'is-pink'],
                'data-method' => 'post',
                'data-confirm-message' => '不達メールの削除をおこなってよろしいですか？',
                'data-confirm-title' => '不達メールの削除',
                'data-confirm-html' => h('削除したデータの復旧はできません。'),
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'BounceMails',
                    'action' => 'togetherDelete',
                ], ['escape' => false]),
            ]) ?>
        </aside>
        <div class="hidden">
            <input type="hidden" class="js_save_check_url" value="<?= $this->Url->build([
                'prefix' => 'Admin/Ajax',
                'controller' => 'BounceMails',
                'action' => 'save-check',
            ]) ?>"/>
        </div>
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>
