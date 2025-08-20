<?php

use App\Model\Entity\AutoReplyMail;
use App\Mailer\DefaultMailer;

?>
<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">メール種別<?= $this->Template->isRequire('type') ?></div>
                </th>
                <td>
                    <?php if (!$autoReplyMail->isTypeDisplayText()) : ?>
                        <?= $this->Form->control('type', [
                            'type' => 'select',
                            'label' => false,
                            'options' => $valueOptions['type'],
                            'class' => ['js_type', 'select'],
                        ]) ?>
                    <?php else : ?>
                        <?= h($this->Configure->read('Master.autoReplyMail.type.' . $autoReplyMail->type)) ?>
                        <?= $this->Form->hidden('type') ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input js_user_authority_area" id="dispUserAuthorityIds"
                data-ids="<?= h(implode(',', $valueOptions['canSetting']['userAuthority'])) ?>">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">送信対象<?= $this->Template->isRequire('user_authority_id') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('user_authority_id', [
                        'type' => 'select',
                        'label' => false,
                        'class' => ['select'],
                        'options' => $valueOptions['userAuthorityId'],
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input js_reservation_status js_reservation_status_<?= h(AutoReplyMail::TYPE_RESERVE_CANCEL) ?>">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        キャンセル前の予約ステータス<?= $this->Template->isRequire('auto_reply_mail_statuses', ['always' => 'true']) ?></div>
                </th>
                <td>
                    <?php $index = 0 ?>
                    <?php foreach ($valueOptions['reservationStatusCancel'] as $fromStatus => $item) : ?>
                        <?php $key = $fromStatus . '-' . $valueOptions['cancelStatusId'] ?>
                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.id') ?>
                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.type',
                            ['value' => AutoReplyMail::TYPE_RESERVE_CANCEL]) ?>

                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.reservation_status_to_id',
                            ['value' => $valueOptions['cancelStatusId']]) ?>

                        <?= $this->Template->checkbox('auto_reply_mail_statuses.' . $key . '.reservation_status_from_id', [
                            'type' => 'checkbox',
                            'label' => ['class' => ['cmn-check'], 'text' => $item],
                            'value' => $fromStatus,
                        ]) ?>
                        <?= $this->Form->error('auto_reply_mail_statuses.' . $index . '.reservation_status_from_id') ?>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                    <?= $this->FormError->errorWithoutNested('auto_reply_mail_statuses'); ?>
                </td>
            </tr>
            <tr class="field-input js_reservation_status js_reservation_status_<?= h(AutoReplyMail::TYPE_RESERVE_ADD) ?>">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        登録時の予約ステータス<?= $this->Template->isRequire('auto_reply_mail_statuses', ['always' => 'true']) ?></div>
                </th>
                <td>
                    <?php foreach ($valueOptions['reservationStatusAdd'] as $toStatus => $item) : ?>
                        <?php $key = null . '-' . $toStatus ?>
                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.id') ?>
                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.type',
                            ['value' => AutoReplyMail::TYPE_RESERVE_ADD]) ?>
                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.reservation_status_from_id',
                            ['value' => null]) ?>
                        <?= $this->Template->checkbox('auto_reply_mail_statuses.' . $key . '.reservation_status_to_id', [
                            'type' => 'checkbox',
                            'label' => ['class' => ['cmn-check'], 'text' => $item],
                            'value' => $toStatus,
                        ]) ?>
                        <?= $this->Form->error('auto_reply_mail_statuses.' . $index . '.reservation_status_to_id') ?>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                    <?= $this->FormError->errorWithoutNested('auto_reply_mail_statuses'); ?>
                </td>
            </tr>
            <tr class="field-input js_reservation_status js_reservation_status_<?= h(AutoReplyMail::TYPE_RESERVE_REMINDER) ?>">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        ステータス<?= $this->Template->isRequire('auto_reply_mail_statuses', ['always' => 'true']) ?></div>
                </th>
                <td>
                    <?php foreach ($valueOptions['reservationStatusReminder'] as $fromStatus => $item) : ?>
                        <?php $key = $fromStatus . '-' . $fromStatus . '-' . h(AutoReplyMail::TYPE_RESERVE_REMINDER) ?>
                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.id') ?>
                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.type',
                            ['value' => AutoReplyMail::TYPE_RESERVE_REMINDER]) ?>

                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.reservation_status_to_id',
                            ['value' => $fromStatus]) ?>

                        <?= $this->Template->checkbox('auto_reply_mail_statuses.' . $key . '.reservation_status_from_id', [
                            'type' => 'checkbox',
                            'label' => ['class' => ['cmn-check'], 'text' => $item],
                            'value' => $fromStatus,
                        ]) ?>
                        <?= $this->Form->error('auto_reply_mail_statuses.' . $index . '.reservation_status_from_id') ?>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                    <?= $this->FormError->errorWithoutNested('auto_reply_mail_statuses'); ?>
                </td>
            </tr>
            <tr class="field-input js_reservation_status js_reservation_status_<?= h(AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE) ?>">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        ステータス<?= $this->Template->isRequire('auto_reply_mail_statuses', ['always' => 'true']) ?></div>
                </th>
                <td>
                    <?php foreach ($valueOptions['reservationStatusReminder'] as $fromStatus => $item) : ?>
                        <?php $key = $fromStatus . '-' . $fromStatus. '-' . h(AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE) ?>
                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.id') ?>
                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.type',
                            ['value' => AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE]) ?>

                        <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.reservation_status_to_id',
                            ['value' => $fromStatus]) ?>

                        <?= $this->Template->checkbox('auto_reply_mail_statuses.' . $key . '.reservation_status_from_id', [
                            'type' => 'checkbox',
                            'label' => ['class' => ['cmn-check'], 'text' => $item],
                            'value' => $fromStatus,
                        ]) ?>
                        <?= $this->Form->error('auto_reply_mail_statuses.' . $index . '.reservation_status_from_id') ?>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                    <?= $this->FormError->errorWithoutNested('auto_reply_mail_statuses'); ?>
                </td>
            </tr>
            <tr class="field-input js_reservation_status js_reservation_status_<?= h(AutoReplyMail::TYPE_STATUS_UPDATE) ?>">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        ステータス<?= $this->Template->isRequire('auto_reply_mail_statuses', ['always' => 'true']) ?></div>
                </th>
                <td>
                    <div class="pliceInput-box">
                        <table class="pliceInput-detail" id="pliceTable">
                            <?php foreach ($valueOptions['reservationStatusUpdate'] as $fromStatus => $item) : ?>
                                <tr>
                                    <th><?= h($valueOptions['reservationStatusUpdateAll'][$fromStatus]) ?>から</th>
                                    <td class="btn-inline">
                                        <?php foreach ($item as $statusId => $statusName) : ?>
                                            <?php $key = $fromStatus . '-' . $statusId ?>
                                            <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.id') ?>
                                            <?= $this->Form->hidden('auto_reply_mail_statuses.' . $key . '.type',
                                                ['value' => AutoReplyMail::TYPE_STATUS_UPDATE]) ?>
                                            <?= $this->Form->control('auto_reply_mail_statuses.' . $key . '.reservation_status_from_id',
                                                [
                                                    'type' => 'hidden',
                                                    'value' => $fromStatus,
                                                ]) ?>
                                            <?= $this->Template->checkbox('auto_reply_mail_statuses.' . $key . '.reservation_status_to_id',
                                                [
                                                    'type' => 'checkbox',
                                                    'label' => ['class' => ['cmn-check'], 'text' => $statusName],
                                                    'value' => $statusId,
                                                ]) ?>
                                            <?= $this->Form->error('auto_reply_mail_statuses.' . $index . '.reservation_status_to_id') ?>
                                            <?php $index++; ?>
                                        <?php endforeach; ?>
                                        <?= $this->FormError->errorWithoutNested('auto_reply_mail_statuses'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </td>
            </tr>
            <tr class="field-input js_label_area" id="dispLabelIds"
                data-ids="<?= h(implode(',', $valueOptions['canSetting']['label'])) ?>">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">カテゴリー<?= $this->Template->isRequire('label_id') ?></div>
                </th>
                <td>
                    <?= $this->Label->renderSelect([
                        'type' => $this->Configure->read('Master.label.type.other'),
                        'labelId' => $autoReplyMail->label_id,
                    ]) ?>

                    <?= $this->Form->control('except_sub_label_flg', [
                        'type' => 'checkbox',
                        'class' => ['btn-tool', 'cmn-check'],
                        'label' => ['class' => 'cmn-check btn-tool', 'text' => 'このカテゴリーのみ'],
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">配信者名<?= $this->Template->isRequire('from_mail_name') ?></div>
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
                    <div class="ttl-input-wrap">FROMアドレス<?= $this->Template->isRequire('from_mail') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('from_mail', [
                        'type' => 'text',
                        'label' => false,
                        'default' => $this->CommonData->existsDefaultFromAddressOnEnv() ? $this->CommonData->getDefaultFromAddress() : null,
                    ]) ?>
                    <?php if ($this->CommonData->existsDefaultFromAddressOnEnv()): ?>
                        <div class="desc-wrap">
                            <p>
                                なりすましメール対策のため、<?= $this->CommonData->getDefaultFromAddress(true) ?>ドメインの利用に固定されております。（[自由入力]<?= $this->CommonData->getDefaultFromAddress(true) ?>）
                            </p>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">返信先アドレス<?= $this->Template->isRequire('reply_to') ?></div>
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
                    <div class="ttl-input-wrap">メール配信形式<?= $this->Template->isRequire('content_type') ?></div>
                </th>
                <td>
                    <?= $this->Template->radio('content_type', [
                        'type' => 'radio',
                        'label' => false,
                        'options' => $valueOptions['contentType'],
                        'class' => ['js_content_type', 'cmn-checkbox'],
                        'data-text' => DefaultMailer::MAIL_FORMAT_CONTENTS_TEXT,
                        'data-html' => DefaultMailer::MAIL_FORMAT_CONTENTS_HTML,
                        'default' => DefaultMailer::MAIL_FORMAT_CONTENTS_TEXT,
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">件名<?= $this->Template->isRequire('subject') ?></div>
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
                    <div class="ttl-input-wrap">挨拶文<?= $this->Template->isRequire('header') ?></div>
                </th>
                <td>
                    <div class="js_contents_text">
                        <?= $this->Form->control('header', [
                            'type' => 'textarea',
                            'label' => false,
                            'rows' => 20,
                            'id' => 'autoReplyMails-header-text'
                        ]) ?>
                    </div>
                    <div class="js_contents_html">
                        <?= $this->Form->control('header', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => ['wysiwyg'],
                            'rows' => 20,
                            'id' => 'autoReplyMails-header-html'
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">本文<?= $this->Template->isRequire('contents') ?></div>
                </th>
                <td>
                    <div class="js_contents_text">
                        <?= $this->Form->control('contents', [
                            'type' => 'textarea',
                            'label' => false,
                            'rows' => 20,
                            'id' => 'autoReplyMails-contents-text'
                        ]) ?>
                    </div>
                    <div class="js_contents_html">
                        <?= $this->Form->control('contents', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => ['wysiwyg'],
                            'rows' => 20,
                            'id' => 'autoReplyMails-contents-html'
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">署名<?= $this->Template->isRequire('footer') ?></div>
                </th>
                <td>
                    <div class="js_contents_text">
                        <?= $this->Form->control('footer', [
                            'type' => 'textarea',
                            'label' => false,
                            'rows' => 20,
                            'id' => 'autoReplyMails-footer-text'
                        ]) ?>
                    </div>
                    <div class="js_contents_html">
                        <?= $this->Form->control('footer', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => ['wysiwyg'],
                            'rows' => 20,
                            'id' => 'autoReplyMails-footer-html'
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        管理者操作時のメール<?= $this->Template->isRequire('admin_operation_mail_flg') ?></div>
                </th>
                <td>
                    <?= $this->Template->checkbox('admin_operation_mail_flg', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check'], 'text' => '管理画面からの操作時に別メールを送る'],
                        'templates' => ['nestingLabel' => '{{hidden}}{{input}}<label{{attrs}}>{{text}}</label>',],
                    ]) ?>
                </td>
            </tr>
            </tbody>
            <tbody class="adminSend">
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">管理者用挨拶文<?= $this->Template->isRequire('header_admin') ?></div>
                </th>
                <td>
                    <div class="js_contents_text">
                        <?= $this->Form->control('header_admin', [
                            'type' => 'textarea',
                            'label' => false,
                            'rows' => 20,
                            'id' => 'autoReplyMails-header_admin-text'
                        ]) ?>
                    </div>
                    <div class="js_contents_html">
                        <?= $this->Form->control('header_admin', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => ['wysiwyg'],
                            'rows' => 20,
                            'id' => 'autoReplyMails-header_admin-html'
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        管理者用本文<?= $this->Template->isRequire('contents_admin', ['always' => 'true']) ?></div>
                </th>
                <td>
                    <div class="js_contents_text">
                        <?= $this->Form->control('contents_admin', [
                            'type' => 'textarea',
                            'label' => false,
                            'rows' => 20,
                            'id' => 'autoReplyMails-contents_admin-text'
                        ]) ?>
                    </div>
                    <div class="js_contents_html">
                        <?= $this->Form->control('contents_admin', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => ['wysiwyg'],
                            'rows' => 20,
                            'id' => 'autoReplyMails-contents_admin-html'
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">管理者用署名<?= $this->Template->isRequire('footer_admin') ?></div>
                </th>
                <td>
                    <div class="js_contents_text">
                        <?= $this->Form->control('footer_admin', [
                            'type' => 'textarea',
                            'label' => false,
                            'rows' => 20,
                            'id' => 'autoReplyMails-footer_admin-text'
                        ]) ?>
                    </div>
                    <div class="js_contents_html">
                        <?= $this->Form->control('footer_admin', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => ['wysiwyg'],
                            'rows' => 20,
                            'id' => 'autoReplyMails-footer_admin-html'
                        ]) ?>
                    </div>
                </td>
            </tr>
            </tbody>
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">置き換え文言</div>
                </th>
                <td>
                    <?php foreach (array_keys($valueOptions['type']) as $type) : ?>
                        <span class="js_replace_vars js_replace_vars_<?= h($type) ?> link-txt is-window">
                    <?= $this->Html->link('置き換え文言', ['controller' => 'AutoReplyMails', 'action' => 'replaceVars', 'prefix' => 'Admin', 'type' => $type], ['target' => '_blank']) ?>
                    </span>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">テスト</div>
                </th>
                <td>
                    <?= $this->Form->control('test_mail', [
                        'type' => 'text',
                        'label' => false,
                    ]) ?>
                    <?= $this->Form->button('左のメールアドレス宛にテストメール送信', [
                        'type' => 'button',
                        'class' => ['js_send_test_mail', 'cmn-btn', 'is-blue', 'is-circle', 'mgl-10'],
                    ]) ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
