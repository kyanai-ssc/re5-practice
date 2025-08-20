<?php

use App\Model\Entity\MailDelivery;

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
            <?php if (MailDelivery::SEND_TYPE_RESERVE === $mailDelivery->send_type) : ?>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">配信予定日時</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($this->Template->displayDayAndWeek($mailDelivery->send_date)) ?>
                            <?php if ($mailDelivery->send_time instanceof \DateTimeInterface): ?>
                                <?= h($mailDelivery->send_time->format('H:i')) ?>
                            <?php else: ?>
                                <?= h($mailDelivery->send_time) ?>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
            <?php endif; ?>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">メール形式</div>
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
                        <?= $this->Html->link('プレビュー', ['controller' => 'MailDeliveries', 'action' => 'preview', 'prefix' => 'Admin'], ['target' => '_blank']) ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
