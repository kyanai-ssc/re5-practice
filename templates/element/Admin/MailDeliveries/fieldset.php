<?php

use App\Model\Entity\MailDelivery;
use App\Mailer\DefaultMailer;

?>
<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">メール配信先</div>
                </th>
                <td>
                    <p><?= h($sendUserCount) ?>件</p>
                    <?= $this->Html->link('送信対象を確認する', ['controller' => 'MailDeliveries', 'action' => 'sendUserDownload', 'prefix' => 'Admin']) ?>
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
                    <div class="ttl-input-wrap">メール配信方法<?= $this->Template->isRequire('send_type') ?></div>
                </th>
                <td>
                    <?= $this->Template->radio('send_type', [
                        'type' => 'radio',
                        'label' => false,
                        'options' => $valueOptions['sendType'],
                        'class' => ['js_send_type', 'cmn-radio'],
                        'default' => MailDelivery::SEND_TYPE_IMMEDIATELY,
                        'data-type' => MailDelivery::SEND_TYPE_RESERVE
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input js_send_datetime">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">配信予定日時<?= $this->Template->isRequire('send_date') ?></div>
                </th>
                <td>
                    <div class="d-flex">

                        <?= $this->Form->control('send_date', [
                            'type' => 'text',
                            'class' => ['js-datepicker'],
                        ]) ?>
                        <?= $this->Form->control('send_time', [
                            'type' => 'select',
                            'options' => $valueOptions['sendTime'],
                            'empty' => '----',
                            'class' => ['select'],
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">メール形式<?= $this->Template->isRequire('content_type') ?></div>
                </th>
                <td>
                    <?= $this->Template->radio('content_type', [
                        'type' => 'radio',
                        'label' => false,
                        'options' => $valueOptions['contentType'],
                        'class' => ['js_content_type'],
                        'data-text' => DefaultMailer::MAIL_FORMAT_CONTENTS_TEXT,
                        'data-html' => DefaultMailer::MAIL_FORMAT_CONTENTS_HTML,
                        'default' => DefaultMailer::MAIL_FORMAT_CONTENTS_TEXT,]) ?>
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
                    <div class="ttl-input-wrap">
                        本文<?= $this->Template->isRequire('contents') ?>
                    </div>
                    <div class="ttl-input-wrap">
                    <span class="link-txt is-window">
                        <?= $this->Html->link('置き換え文言', ['controller' => 'MailDeliveries', 'action' => 'replaceVars', 'prefix' => 'Admin'],
                            ['target' => '_blank']) ?>
                    </span>
                    </div>
                </th>
                <td>
                    <div class="js_contents_text">
                        <?= $this->Form->control('contents', [
                            'type' => 'textarea',
                            'label' => false,
                            'rows' => 30,
                            'id' => 'contents-text'
                        ]) ?>
                    </div>
                    <div class="js_contents_html">
                        <?= $this->Form->control('contents', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => ['wysiwyg'],
                            'rows' => 30,
                            'id' => 'contents-html'
                        ]) ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
