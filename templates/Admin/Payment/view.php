<?php
$this->assign('title', '決済情報');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add('決済情報');
?>


<section class="form-input">
    <?= $this->Flash->render('paymentSettingFinish') ?>
    <div class="panel-show-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <?php if ($this->Setting->getPaymentSetting()->isPaymentServiceGmo()) : ?>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">ショップID</div>
                        </th>
                        <td>
                            <p class="cmn-txt"><?= h($paymentSetting->shop_id) ?></p>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">3Dセキュア利用</div>
                        </th>
                        <td>
                            <p class="cmn-txt">
                                <?= h($this->Configure->read('Master.payment.credit.3DSecure.' . $paymentSetting->get('three_d_secure_flg'))) ?>
                            </p>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">処理区分</div>
                        </th>
                        <td>
                            <p class="cmn-txt"><?= h($this->Configure->read('Master.payment.credit.job.'.$paymentSetting->job_code)) ?></p>
                        </td>
                    </tr>
                <?php elseif($this->Setting->getPaymentSetting()->isPaymentServiceSb()) : ?>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">マーチャントID</div>
                        </th>
                        <td>
                            <p class="cmn-txt"><?= h($paymentSetting->merchant_id) ?></p>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">サービスID</div>
                        </th>
                        <td>
                            <p class="cmn-txt"><?= h($paymentSetting->service_id) ?></p>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">3Dセキュア利用</div>
                        </th>
                        <td>
                            <p class="cmn-txt">
                                <?= h($this->Configure->read('Master.payment.credit.3DSecure.' . $paymentSetting->get('three_d_secure_flg'))) ?>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">利用カードブランド</div>
                    </th>
                    <td>
                        <p class="cmn-txt"><?= h($paymentSetting->card_name) ?></p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">文言設定</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                        <?= $this->Html->link("決済方法文言設定", [
                            'prefix' => 'Admin',
                            'controller' => 'Words',
                            'action' => 'payment-method-word-edit',
                        ]) ?>
                        </p>
                        <p class="cmn-txt">
                        <?= $this->Html->link("決済ステータス文言設定", [
                            'prefix' => 'Admin',
                            'controller' => 'Words',
                            'action' => 'payment-status-word-edit',
                        ]) ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <div class="btn-box mgt-20">
        <?php if ($this->Setting->getPaymentSetting()->canEdit()): ?>
            <?= $this->Html->link('編集', [
                'prefix' => 'Admin',
                'controller' => 'Payment',
                'action' => 'edit',
            ], ['class' => ['cmn-btn', 'is-blue']]) ?>
        <?php endif; ?>
    </div>
</section>
