<?php
$this->assign('title', 'メール配信履歴');
$this->assign('headerType', 'data');
$this->Breadcrumbs->add(
    'メール配信履歴'
);
$this->Html->script('admin/mail-deliveries/list', [
    'block' => true,
]);
?>
<?= $this->Flash->render('mailDeliveriesFinish') ?>
<?= $this->Flash->render('mailDeliveriesErrors') ?>
<section class="panel-show">
    <?= $this->element('Admin/MailDeliveries/search', [
        'valueOptions' => $valueOptions,
    ]) ?>
</section><!-- .panel-search -->
<section class="search-list mgt-20">
    <?php if (count($mailDeliveries) > 0): ?>
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
                                'controller' => 'MailDeliveries',
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
                                <span>配信方法</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'send_type',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>配信予定日時 </span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'send_timestamp',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>FROMアドレス</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'from_mail',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>返信先アドレス</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'reply_to',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>件名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'subject',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <span>配信件数(成功件数/総数)</span>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>配信ステータス</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'send_status',
                                ]) ?>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($mailDeliveries as $mailDelivery): ?>
                    <tr class="parent">
                        <td>
                            <?= h($valueOptions['sendType'][$mailDelivery->send_type]) ?>
                        </td>
                        <td>
                            <?= h($this->Template->displayDayAndWeek($mailDelivery->send_datetime, 'H:i')) ?>
                        </td>
                        <td>
                            <?= h($mailDelivery->from_mail) ?>
                        </td>
                        <td>
                            <?= h($mailDelivery->reply_to) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($mailDelivery->subject, 30, ['tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?php if (isset($mailDelivery->mail_delivery_histories->successCount)) : ?>
                                <?= h($mailDelivery->mail_delivery_histories->successCount) ?>
                                /<?= h($mailDelivery->mail_delivery_histories->sendCount) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= h($valueOptions['sendStatus'][$mailDelivery->send_status]) ?>
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
            <?php foreach ($mailDeliveries as $mailDelivery): ?>
                <tr>
                    <td class="tool">
                        <ul>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_info"></use></svg>', [
                                    'type' => 'button',
                                    'title' => '詳細',
                                    'class' => ['js_change_url', 'btn-tool', 'is-info'],
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'MailDeliveries',
                                        'action' => 'view',
                                        'id' => $mailDelivery->id,
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
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>
