<?php
$this->assign('title', '主催者設定');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('主催者設定');
?>
<?= $this->Flash->render('organizersFinish') ?>
<?= $this->Flash->render('organizersErrors') ?>
<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'Organizers',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'organizers_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => '主催者検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">主催者名</div>
                            </th>
                            <td>
                                <?= $this->Form->control('name', [
                                    'type' => 'text',
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">主催者ID</div>
                            </th>
                            <td>
                                <?= $this->Form->control('id', [
                                    'type' => 'text',
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">ビデオ会議種別</div>
                            </th>
                            <td>
                                <?= $this->Template->checkbox('video_meeting_type', [
                                    'type' => 'multicheckbox',
                                    'options' => $searchForm->getFieldValueOptions('videoMeetingType'),
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
                        'controller' => 'Organizers',
                        'action' => 'list',
                    ], ['escape' => false]),
                ]) ?>
                <?= $this->Form->button('検索', [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue'],
                ]) ?>
            </div>
        </div>
        <?= $this->Form->end(); ?>
    </div>
</section>
<section class="search-list mgt-20">
    <aside class="search-parts d-flex mgt-20">
        <div class="search-parts-left">
            <?= $this->element('Admin/Common/search/page_counter') ?>
            <?= $this->Form->button('新規登録', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-newCreate'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Organizers',
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
                        'valueOptions' => $searchForm->getFieldValueOptions(),
                        'options' => [
                            'data-url' => $this->Url->build([
                                'prefix' => 'Admin',
                                'controller' => 'Organizers',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div>
    </aside>
    <?php if (count($organizers) > 0): ?>
        <div class="fixedTable-option">
            <aside class="fixedTable-arrow">
                <button id="left-button" type="button" class="fixedTable-scroll-btn is-prev cmn-btn"></button>
                <button id="right-button" type="button" class="fixedTable-scroll-btn is-next cmn-btn"></button>
            </aside>
        </div>
        <div class="fixedTable-wrap">
            <div class="fixedTable-in">
                <div class="fixedTableHead">
                    <table class="cmn-table status-table">
                        <thead class="stickyTable">
                            <tr>
                                <th class="reId">
                                    <div class="sort_wrap">
                                        <span>主催者ID</span>
                                        <?= $this->element('Admin/Common/search/sort', [
                                            'key' => 'id',
                                        ]) ?>
                                    </div>
                                </th>
                                <th>
                                    <div class="sort_wrap">
                                        <span>主催者名</span>
                                        <?= $this->element('Admin/Common/search/sort', [
                                            'key' => 'name',
                                        ]) ?>
                                    </div>
                                </th>
                                <th>
                                    <div class="sort_wrap">
                                        <span>ビデオ会議種別</span>
                                        <?= $this->element('Admin/Common/search/sort', [
                                            'key' => 'video_meeting_type',
                                        ]) ?>
                                    </div>
                                </th>
                                <th>
                                    <div class="sort_wrap">
                                        <span>ビデオ会議のホスト(メールアドレス)</span>
                                        <?= $this->element('Admin/Common/search/sort', [
                                            'key' => 'zoom_host_email',
                                        ]) ?>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <table class="cmn-table status-table fixedTableBody">
                    <tbody>
                        <?php foreach ($organizers as $organizer): ?>
                            <tr class="parent">
                                <td class="reId">
                                    <?= h($organizer->get('id')) ?>
                                </td>
                                <td>
                                    <?= h($organizer->get('name')) ?>
                                </td>
                                <td>
                                    <?= h($this->Configure->read('Master.organizer.videoMeetingType.' . $organizer->get('video_meeting_type'))) ?>
                                </td>
                                <td>
                                    <?php if ((string)$organizer->get('zoom_host_email') !== ''): ?>
                                        <?= h($organizer->get('zoom_host_email')) ?>
                                    <?php else: ?>
                                        -
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
                    <?php foreach ($organizers as $organizer): ?>
                        <tr>
                            <td class="tool">
                                <ul>
                                    <li>
                                        <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', [
                                            'type' => 'button',
                                            'class' => ['js_change_url', 'btn-tool', 'is-editor'],
                                            'title' => '編集',
                                            'data-url' => $this->Url->build([
                                                'prefix' => 'Admin',
                                                'controller' => 'Organizers',
                                                'action' => 'edit',
                                                'id' => $organizer->get('id'),
                                            ], ['escape' => false]),
                                            'escapeTitle' => false,
                                        ]) ?>
                                    </li>
                                    <?php if ($organizer->canDelete()): ?>
                                        <li>
                                            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                                'type' => 'button',
                                                'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                                'title' => '削除',
                                                'data-confirm-message' => '主催者の削除をおこなってよろしいですか？',
                                                'data-confirm-title' => '主催者の削除',
                                                'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                                'data-url' => $this->Url->build([
                                                    'prefix' => 'Admin',
                                                    'controller' => 'Organizers',
                                                    'action' => 'delete',
                                                'id' => $organizer->get('id'),
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
