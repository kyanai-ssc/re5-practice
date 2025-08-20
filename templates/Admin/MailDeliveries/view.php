<?php

use App\Model\Entity\AdminListItem;

$this->assign('title', 'メール配信履歴 詳細');
$this->assign('headerType', 'data');

$this->Breadcrumbs->add(
    'メール配信履歴',
    ['prefix' => 'Admin', 'controller' => 'MailDeliveries', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '詳細'
);

$this->Html->script('admin/mail-deliveries/view', [
    'block' => true,
]);
?>

<?= $this->Flash->render('mailDeliveriesFinish') ?>
<?= $this->Flash->render('mailDeliveriesErrors') ?>
<section class="form-input">
    <div class="panel-show-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">FROMアドレス</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($mailDelivery->from_mail) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">返信先アドレス</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($mailDelivery->reply_to) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">配信者名</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($mailDelivery->from_mail_name) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">配信方法</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($this->Configure->read('Master.mailDelivery.sendType.' . $mailDelivery->send_type)) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">配信予定日時</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($this->Template->displayDayAndWeek($mailDelivery->send_datetime, 'H:i')) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">メール配信形式</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($this->Configure->read('Master.common.mailFormatName.' . $mailDelivery->content_type)) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">件名</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($mailDelivery->subject) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">本文</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= $this->Html->link('プレビュー', ['controller' => 'MailDeliveries', 'action' => 'preview', 'prefix' => 'Admin', $mailDelivery->id], ['target' => '_blank']) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">配信ステータス</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($this->Configure->read('Master.mailDelivery.status.' . $mailDelivery->send_status)) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">配信日時</div>
                    </th>
                    <td>
                        <?php if (!empty($mailDelivery->send_timestamp)) : ?>
                            <p class="cmn-txt">
                                <?= h($mailDelivery->send_timestamp->format('Y/m/d H:i:s')) ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">配信件数（成功数/配信数）</div>
                    </th>
                    <td>
                        <?php if (isset($mailDelivery->mail_delivery_histories->successCount)) : ?>
                            <p class="cmn-txt">
                                <?= h($mailDelivery->mail_delivery_histories->successCount) ?>
                                /<?= h($mailDelivery->mail_delivery_histories->sendCount) ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'MailDeliveries',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery')
        ], ['class' => ['cmn-btn', 'is-reset', 'is-gray']]) ?>
    </div>
    <?php if ($mailDelivery->canCancel()) : ?>
        <div class="btn-box mgt-20">
            <?= $this->Form->create($mailDelivery, [
                'type' => 'post',
                'url' => [
                    'controller' => 'MailDeliveries',
                    'action' => 'cancel',
                    'id' => $mailDelivery->get('id'),
                ],
                'idPrefix' => 'mailDeliveries-add',
                'novalidate' => true,
                'class' => ['js_submit_confirm'],
                'data-confirm-title' => 'メール配信キャンセル',
                'data-confirm-message' => 'メール配信をキャンセルしてもよろしいでしょうか？',
            ]) ?>
            <?= $this->Form->hidden('id') ?>
            <?= $this->Form->button('メール配信をキャンセル', [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-pink', 'is-circle'],
            ]) ?>
            <?= $this->Form->end() ?>
        </div>
    <?php endif; ?>
</section>
<section class="search-list mgt-20">
    <fieldset>
        <legend class="ttl-search mgb-20">
            <span class="ttl-s">メール配信先</span>
        </legend>
    </fieldset>
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
                                'action' => 'view',
                                'id' => $this->request->getParam('id'),
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
                <li>
                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_setting"></use></svg>', [
                        'type' => 'button',
                        'title' => '一覧項目設定',
                        'class' => ['js_select_list_items', 'btn-edit', ' thEditBtn', 'tooltip'],
                        'data-type' => AdminListItem::TYPE_MAIL_DELIVERIES,
                        'data-title' => '一覧項目設定',
                        'escapeTitle' => false,
                    ]) ?>
                </li>
                <li>
                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_download"/></svg>', [
                        'type' => 'button',
                        'class' => ['js_post_link_plural', 'btn-list', 'fileDown', 'is-download'],
                        'title' => 'メール配信先のダウンロード',
                        'aria-describedby' => 'ui-id-60',
                        'data-url' => $this->Url->build([
                            'prefix' => 'Admin',
                            'controller' => 'MailDeliveries',
                            'action' => 'sendUserDownload',
                            'id' => $mailDelivery->id,
                        ], ['escape' => false]),
                        'escapeTitle' => false,
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <?php if (count($users) > 0): ?>
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
                            <th>
                                <span>配信ステータス</span>
                            </th>
                            <?= $this->element('Admin/AdminListItems/items_header', [
                                'listItems' => $searchForm->getListItems(),
                            ]) ?>
                        </thead>
                    </table>
                </div>
                <table class="cmn-table status-table fixedTableBody">
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="parent">
                                <td>
                                    <?php foreach ((array)$user->get('mail_delivery_histories') as $mailDeliveryHistory): ?>
                                        <?= h($this->Configure->read('Master.mailDeliveryHistory.status.' . $mailDeliveryHistory->get('send_status'))) ?>
                                    <?php endforeach; ?>
                                </td>
                                <?= $this->element('Admin/AdminListItems/items_body', [
                                    'listItems' => $searchForm->getListItems(),
                                    'options' => [
                                        'mode' => 'user',
                                        'user' => $user,
                                        'notUpdateAuthrity' => true,
                                    ],
                                ]) ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table><!-- .result-table -->
            </div>
            <table class="fixedTableLeft cmn-table status-table">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table><!-- .result-table -->
        </div>
        <aside class="search-parts mgt-20">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <div class="hidden">
            <input type="hidden" class="js_reload_url" value="<?= $this->Url->build([
                'prefix' => 'Admin',
                'controller' => 'MailDeliveries',
                'action' => 'view',
                'id' => $this->request->getParam('id'),
            ]) ?>"/>
        </div>
    <?php else: ?>
        <?= $this->element('Admin/Common/search/no_result') ?>
    <?php endif; ?>
</section>

