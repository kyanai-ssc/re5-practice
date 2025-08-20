<?php
$this->assign('title', '休業設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add('休業設定');
?>

<?= $this->Flash->render('eventHolidaysFinish') ?>
<?= $this->Flash->render('eventHolidaysErrors') ?>
<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'EventHolidays',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'eventHolidays_search',
            'novalidate' => true,
        ]) ?>
        <?php $this->Form->unlockField('id'); ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => '休業設定検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20"></legend>
                    <?= $this->element('Admin/Events/fieldset_list') ?>
                </fieldset>
            </div>
            <div class="btn-box mgt-20">
                <?= $this->Form->button('リセット', [
                    'type' => 'reset',
                    'class' => ['js_change_url', 'cmn-btn', 'is-reset'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'EventHolidays',
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
            <?= $this->Form->button('共通の休業設定追加', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'EventHolidays',
                    'action' => 'edit',
                    '#' => 'eventHolidaysAdd'
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
                                'controller' => 'EventHolidays',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($eventHolidays) > 0): ?>
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
                                    'key' => 'event_id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <span>予約枠名</span>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>適用期間</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'date_from',
                                ]) ?>

                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>適用時間</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'time_from',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <span>適用曜日</span>
                        </th>
                        <th>
                            <span>適用除外日</span>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($eventHolidays as $eventHoliday): ?>
                    <tr class="parent">
                        <td class="reId">
                            <?= h($eventHoliday->event_id) ?>
                        </td>
                        <td>
                            <?php if ($eventHoliday->event_id === null) : ?>
                                共通
                            <?php else: ?>
                                <?= $this->Text->truncate($eventHoliday->event->name, 30, ['ellipsis' => '...', 'tooltip' => true, 'escape' => true]) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $this->Template->getFromToDisplay($this->Template->displayDayAndWeek($eventHoliday->date_from), $this->Template->displayDayAndWeek($eventHoliday->date_to), '～', true) ?>
                        </td>
                        <td>
                            <?= h($this->Template->getFromToDisplay($eventHoliday->time_from, $eventHoliday->time_to)) ?>
                        </td>
                        <td>
                            <?= h($this->Template->viewArrayToStringForMaster($eventHoliday->event_holiday_weeks, 'week', $this->Configure->read('Master.event.week'))) ?>
                        </td>
                        <td>
                            <?php foreach ($eventHoliday->event_holiday_exclude_dates as $excludeDate) : ?>
                                <p><?= h($this->Template->displayDayAndWeek($excludeDate['date'])) ?></p>
                            <?php endforeach; ?>
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
            <?php foreach ($eventHolidays as $eventHoliday): ?>
                <tr>
                    <td class="tool">
                        <ul>
                            <li>
                                <?php if ($eventHoliday->event_id === null) : ?>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', [
                                        'type' => 'button',
                                        'title' => '編集',
                                        'class' => ['js_change_url', 'btn-tool', 'is-editor'],
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'EventHolidays',
                                            'action' => 'edit',
                                            "#" => "eventHolidays" . $eventHoliday->id,
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                <?php else: ?>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', [
                                        'type' => 'button',
                                        'title' => '編集',
                                        'class' => ['js_change_url', 'btn-tool', 'is-editor'],
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Events',
                                            'action' => 'edit',
                                            'id' => $eventHoliday->event_id,
                                            "#" => "event_holidays_tab",
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                <?php endif; ?>
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
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>

