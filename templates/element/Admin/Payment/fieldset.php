<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
                <?php if ($this->Setting->getPaymentSetting()->isPaymentServiceGmo()) : ?>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                ショップID
                            </div>
                        </th>
                        <td>
                            <p class="cmn-txt">
                                <?= h($paymentSetting->get('shop_id')) ?>
                            </p>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                3Dセキュア利用
                                <?= $this->Template->isRequire('three_d_secure_flg') ?>
                            </div>
                        </th>
                        <td>
                            <?= $this->Template->radio('three_d_secure_flg', [
                                'type' => 'radio',
                                'class' => ['cmn-radio'],
                                'options' => $this->Configure->read('Master.payment.credit.3DSecure'),
                            ]) ?>
                            <div class="desc-wrap">
                                <p>
                                    ※3Dセキュア利用の場合は続けて予約は利用できません
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                処理区分
                            </div>
                        </th>
                        <td>
                            <p class="cmn-txt">
                                <?= h($this->Configure->read('Master.payment.credit.job.'.$paymentSetting->get('job_code'))) ?>
                            </p>
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
                            <div class="ttl-input-wrap">
                                3Dセキュア利用
                                <?= $this->Template->isRequire('three_d_secure_flg') ?>
                            </div>
                        </th>
                        <td>
                            <?= $this->Template->radio('three_d_secure_flg', [
                                'type' => 'radio',
                                'class' => ['cmn-radio'],
                                'options' => $this->Configure->read('Master.payment.credit.3DSecure'),
                            ]) ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            利用カードブランド
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($paymentSetting->get('card_name')) ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</div>
