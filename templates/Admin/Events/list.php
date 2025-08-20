<?php

use \App\Model\Entity\Event;

$this->assign('title', '予約枠設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '予約枠設定'
);
?>

<?= $this->Html->script('admin/events/list'); ?>

<?= $this->Flash->render('eventsFinish') ?>
<?= $this->Flash->render('eventsErrors') ?>


<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'Events',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'events_search',
            'novalidate' => true,
        ]) ?>
        <?php $this->Form->unlockField('id'); ?>

        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => '予約枠検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <?= $this->element('Admin/Events/fieldset_list') ?>
                </fieldset>
            </div>
            <div class="btn-box mgt-20">
                <?= $this->Form->button('リセット', [
                    'type' => 'reset',
                    'class' => ['js_change_url', 'cmn-btn', 'is-reset'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'Events',
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
            <?= $this->Form->button('一括編集', [
                'type' => 'button',
                'class' => ['js_save_check_data', 'cmn-btn', 'is-search-editor'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Events',
                    'action' => 'together-edit',
                ], ['escape' => false]),
            ]) ?>
            <?= $this->Form->button('新規登録', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Events',
                    'action' => 'add',
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
                            'class' => ['js_change_search_limit'],
                            'data-url' => $this->Url->build([
                                'prefix' => 'Admin',
                                'controller' => 'Events',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
                <li>
                    <?php
                    $importNote = null;
                    if ($this->Setting->getSystemSetting()->useSmartLock()) {
                        $importNote = $this->SmartLock->getName() . 'と連携した予約データの一括アップロードは1分間に60件を超えるとエラーになる可能性があります。';
                    }
                    ?>
                    <?= $this->element('Admin/Common/form/import', [
                        'importController' => 'Events',
                        'uploadTitle' => '予約枠アップロード',
                        'importNote' => $importNote
                    ]) ?>
                </li>
                <li>
                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_download"/></svg>', [
                        'type' => 'button',
                        'class' => ['js_post_link_plural', 'btn-list', 'fileDown', 'is-download'],
                        'title' => '予約枠ダウンロード',
                        'aria-describedby' => 'ui-id-60',
                        'data-url' => $this->Url->build([
                            'prefix' => 'Admin',
                            'controller' => 'Events',
                            'action' => 'download',
                        ], ['escape' => false]),
                        'escapeTitle' => false,
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($events) > 0): ?>
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
                                <span>予約枠ID</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>カテゴリー</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'label_id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>予約枠名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'name',
                                ]) ?>
                            </div>
                        </th>
                        <?php if ($this->Setting->getSystemSetting()->canCoordinateVideoMeeting()): ?>
                            <th>
                                <div class="sort_wrap">
                                    <span>ビデオ会議主催者</span>
                                    <?= $this->element('Admin/Common/search/sort', [
                                        'key' => 'organizer_id',
                                    ]) ?>
                                </div>
                            </th>
                        <?php endif; ?>
                        <?php if ($this->Setting->getSystemSetting()->useSmartLock()) : ?>
                            <th>
                                <div class="sort_wrap">
                                    <span>
                                        <?php if ($this->SmartLock->useRemoteLock()) : ?>
                                            デバイスキー
                                        <?php elseif ($this->SmartLock->useAkerun()) : ?>
                                            Akerun ID
                                        <?php endif; ?>
                                    </span>
                                    <?= $this->element('Admin/Common/search/sort', [
                                        'key' => 'smart_lock_device_key',
                                    ]) ?>
                                </div>
                            </th>
                        <?php endif; ?>
                        <th>
                            <div class="sort_wrap">
                                <span>利用期間</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'date_from',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <span>利用曜日</span>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>実施時間</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'time_from',
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
                        <th class="status">
                            <div class="sort_wrap">
                                <span>公開設定</span>
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
                <?php foreach ($events as $event): ?>
                    <tr class="parent">
                        <td class="reId">
                            <?= h($event->id) ?>
                        </td>
                        <td>
                            <?php if ($event->label_id !== null): ?>
                                <?= $this->Text->truncate($event->label->name, 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($event->name, 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <?php if ($this->Setting->getSystemSetting()->canCoordinateVideoMeeting()): ?>
                            <td>
                                <?php if ($event->has('organizer')): ?>
                                    <?= h($event->get('organizer')->createDisplayName()); ?>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <?php if ($this->Setting->getSystemSetting()->useSmartLock()) : ?>
                            <td>
                                <?php if ($event->has('event_smart_lock')): ?>
                                    <?= h($event->get('event_smart_lock')->get('smart_lock_device_key')); ?>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            <?= $this->Template->getFromToDisplay(
                                $this->Template->displayDayAndWeek($event->date_from), $this->Template->displayDayAndWeek($event->date_to), '～', true)
                            ?>
                        </td>
                        <td>
                            <?php if (!empty($event->event_weeks)): ?>
                                <?php foreach ($event->event_weeks as $eventWeek) : ?>
                                    <?= h($this->Configure->read('Master.event.week.' . $eventWeek->week)); ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                全曜日
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $this->Template->getFromToDisplay($event->time_from, $event->time_to) ?>
                        </td>
                        <td class="w-120 min-maxW-120">
                            <?= h($event->sort_no) ?>
                        </td>
                        <td class="status">
                            <?php
                            if ($event->public_flg === Event::COMMON_FLG_ON) {
                                $public_flg_on_class = '';
                                $public_flg_off_class = ' hidden';
                            } else {
                                $public_flg_on_class = ' hidden';
                                $public_flg_off_class = '';
                            }
                            ?>
                            <div class="is-release<?= h($public_flg_on_class) ?>">
                                <button type="button"
                                        class="is-release cmn-btn is-blue status-change-btn1 js_public_flg_update"
                                        data-eventId="<?= h($event->id) ?>" data-title="公開設定更新"
                                        data-change="<?= h(Event::COMMON_FLG_OFF) ?>"><?= h($valueOptions['publicFlg'][Event::COMMON_FLG_ON]) ?></button>
                            </div>
                            <div class="is-private<?= h($public_flg_off_class) ?>">
                                <button type="button"
                                        class="is-private cmn-btn is-gray status-change-btn1 js_public_flg_update"
                                        data-eventId="<?= h($event->id) ?>" data-title="公開設定更新"
                                        data-change="<?= h(Event::COMMON_FLG_ON) ?>"><?= h($valueOptions['publicFlg'][Event::COMMON_FLG_OFF]) ?></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <table class="fixedTableLeft cmn-table status-table js_check_popup_table">
            <thead>
            <tr>
                <th class="tool fixedElm">
                    <span>操作</span>
                </th>
                <th class="checkbox fixedElm">
                    <?= $this->element('Admin/Common/search/list_all_check') ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event): ?>
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
                                        'controller' => 'Events',
                                        'action' => 'edit',
                                        'id' => $event->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li class="bdr">
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_copy"></use></svg>', [
                                    'type' => 'button',
                                    'title' => '複製',
                                    'class' => ['js_change_url', 'btn-tool', 'is-copy'],
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'Events',
                                        'action' => 'copy',
                                        'id' => $event->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_syuku"></use></svg>', [
                                    'type' => 'button',
                                    'class' => ['js_change_url', 'btn-tool', 'is-syuku'],
                                    'title' => '個別休業設定',
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'Events',
                                        'action' => 'edit',
                                        'id' => $event->id,
                                        "#" => "event_holidays_tab"
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li class="bdr">
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_irregular"></use></svg>', [
                                    'type' => 'button',
                                    'class' => ['js_change_url', 'btn-tool', 'is-irregular'],
                                    'title' => '例外日設定',
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'Events',
                                        'action' => 'edit',
                                        'id' => $event->id,
                                        "#" => "event_stock_settings_tab"
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                            <li>
                                <?php if ($event->canDelete()) : ?>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                        'type' => 'button',
                                        'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                        'title' => '削除',
                                        'data-confirm-message' => '予約枠の削除をおこなってよろしいですか？',
                                        'data-confirm-title' => '予約枠の削除',
                                        'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Events',
                                            'action' => 'delete',
                                            'id' => $event->id,
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </td>
                    <td class="checkbox">
                        <?= $this->element('Admin/Common/search/list_check', [
                            'checkId' => $event->id,
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <aside class="search-parts mgt-20">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <div class="hidden">
            <input type="hidden" class="js_save_check_url" value="<?= $this->Url->build([
                'prefix' => 'Admin/Ajax',
                'controller' => 'Events',
                'action' => 'save-check',
            ]) ?>"/>
        </div>
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>

<div class="hidden">
    <div class='js_public_flg_update_message_open'>
        <div class="popup-content">
            <p>公開設定を<?= h($valueOptions['publicFlg'][Event::COMMON_FLG_ON]) ?>に変更致します。</p>
            <p>よろしいでしょうか？</p>
        </div>
    </div>

    <div class='js_public_flg_update_message_private'>
        <div class="popup-content">
            <p>公開設定を<?= h($valueOptions['publicFlg'][Event::COMMON_FLG_OFF]) ?>に変更致します。</p>
            <p>よろしいでしょうか？</p>
        </div>
    </div>

    <?= $this->Form->control('js_button_yes', [
        'type' => 'hidden',
        'value' => 'はい',
        'class' => ['js_button_yes'],
    ]) ?>

    <?= $this->Form->control('js_button_no', [
        'type' => 'hidden',
        'value' => 'いいえ',
        'class' => ['js_button_no'],
    ]) ?>

</div>

