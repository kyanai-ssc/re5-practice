<?php

use App\Model\Entity\AdminSearchItem;

?>
<div class="panel-show-wrap">
    <?= $this->Form->create($searchForm, [
        'type' => 'post',
        'url' => [
            'prefix' => 'Admin',
            'controller' => 'MailDeliveries',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ],
        'idPrefix' => 'mailDeliveries_search',
        'novalidate' => true,
        'class' => ['js_mail_deliveries_search_form'],
    ]) ?>
    <?= $this->element('Admin/Common/form/panel_show', [
        'panelTitle' => 'メール配信履歴検索',
    ]) ?>
    <div class="showWrap">
        <div class="panel-show-set">
            <fieldset class="mgt-20">
                <legend class="ttl-search mgb-20">
                    <span class="ttl-s">メール配信情報から検索</span>
                </legend>
                <table class="input-box">
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">配信方法</div>
                        </th>
                        <td>
                            <?= $this->Template->checkbox('send_type', [
                                'type' => 'multicheckbox',
                                'label' => false,
                                'options' => $valueOptions['sendType'],
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">配信予定日時</div>
                        </th>
                        <td class="d-flex">
                            <?= $this->Form->control('send_date_from', [
                                'type' => 'text',
                                'class' => ['js-datepicker-type-range-start']
                            ]) ?>
                            <?= $this->Form->control('send_time_from', [
                                'type' => 'select',
                                'class' => ['select'],
                                'options' => $valueOptions['sendTime'],
                                'empty' => '----',
                            ]) ?>
                            <span class="txt mgl-10 mgr-10">から</span>
                            <?= $this->Form->control('send_date_to', [
                                'type' => 'text',
                                'class' => ['js-datepicker-type-range-end']
                            ]) ?>
                            <?= $this->Form->control('send_time_to', [
                                'type' => 'select',
                                'class' => ['select'],
                                'options' => $valueOptions['sendTime'],
                                'empty' => '----',
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">FROMアドレス</div>
                        </th>
                        <td>
                            <?= $this->Form->control('from_mail', [
                                'type' => 'text',
                                'label' => false,
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">返信先アドレス</div>
                        </th>
                        <td>
                            <?= $this->Form->control('reply_to', [
                                'type' => 'text',
                                'label' => false,
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">配信者名</div>
                        </th>
                        <td>
                            <?= $this->Form->control('from_mail_name', [
                                'type' => 'text',
                                'label' => false,
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">件名</div>
                        </th>
                        <td>
                            <?= $this->Form->control('subject', [
                                'type' => 'text',
                                'label' => false,
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">配信ステータス</div>
                        </th>
                        <td>
                            <?= $this->Template->checkbox('send_status', [
                                'type' => 'multicheckbox',
                                'label' => false,
                                'options' => $valueOptions['sendStatus'],
                            ]) ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>
        <?= $this->element('Admin/AdminSearchItems/items_search', [
            'searchForm' => $searchForm,
            'searchItems' => $searchForm->getSearchItems(),
        ]) ?>
        <div class="btn-box mgt-20">
            <?= $this->Form->button('リセット', [
                'type' => 'reset',
                'class' => ['js_change_url', 'cmn-btn', 'is-reset'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'MailDeliveries',
                    'action' => 'list',
                ], ['escape' => false]),
            ]) ?>
            <?= $this->Form->button('検索', ['type' => 'submit', 'class' => 'cmn-btn is-blue']) ?>
        </div>
        <div class="tool-wrap">
            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_setting"/></svg>', [
                'type' => 'button',
                'title' => '検索項目設定',
                'class' => ['js_select_search_items', 'btn-setting', ' toolBtn', 'tooltip'],
                'data-title' => '検索項目設定',
                'data-type' => AdminSearchItem::TYPE_MAIL_DELIVERIES,
                'escapeTitle' => false,
            ]) ?>
        </div>
    </div>
    <?= $this->Form->end(); ?>
</div>
