<?php
use App\Locale\Message;

$this->Form->unlockField('payment_tokens');
$this->Form->unlockField('payment_email_address');
$this->Form->unlockField('payment_phone_type');
$this->Form->unlockField('payment_phone_number');
?>
<div class="js_payment_fieldset hidden">
    <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('payment/cardInfo') ?></h3>
    <div class="mgb-10 hidden js_payment_error js_payment_error_other">
        <?= $this->Form->formatTemplate('error', ['content' => __(Message::ERROR_TOKEN_PAYMENT_OTHER)]) ?>
    </div>
    <fieldset class="input-info">
        <table class="input-box">
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('payment/cardNo') ?>
                        <?= $this->element('User/Common/form/require') ?>
                    </div>
                </th>
                <td>
                    <p class="cmn-txt">
                        <?= $this->Form->control('', [
                            'type' => 'text',
                            'id' => 'payment-expire-card-no',
                            'class' => ['textbox_w300', 'js_payment_input', 'js_payment_card_no'],
                            'maxlength' => 16,
                            'value' => '',
                            'secure' => $this->Form::SECURE_SKIP,
                        ]) ?>
                        <span class="hidden js_payment_error js_payment_error_card_no">
                            <?= $this->Form->formatTemplate('error', ['content' => __(Message::ERROR_TOKEN_PAYMENT_CARD_NO)]) ?>
                        </span>
                    </p>
                    <ul class="card-list">
                        <?php foreach ((array)$this->Setting->getPaymentSetting()->get('card_brand') as $brand): ?>
                            <li>
                                <?= $this->Html->image('vendor/card/' . $this->Configure->read('Master.payment.credit.brandImage.' . $brand)) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('payment/expire') ?>
                        <?= $this->element('User/Common/form/require') ?>
                    </div>
                </th>
                <td>
                    <p class="cmn-txt">
                        <?= $this->Form->control('', [
                            'type' => 'select',
                            'id' => 'payment-expire-month',
                            'class' => ['select', 'js_payment_input', 'js_payment_expire_month'],
                            'options' => $this->Setting->getPaymentSetting()->getExpireMonthValueOptions(),
                            'empty' => true,
                            'value' => '',
                            'secure' => $this->Form::SECURE_SKIP,
                        ]) ?>
                        /
                        <?= $this->Form->control('', [
                            'type' => 'select',
                            'id' => 'payment-expire-year',
                            'class' => ['select', 'js_payment_input', 'js_payment_expire_year'],
                            'options' => $this->Setting->getPaymentSetting()->getExpireYearValueOptions(),
                            'empty' => true,
                            'value' => '',
                            'secure' => $this->Form::SECURE_SKIP,
                        ]) ?>
                        <span class="hidden js_payment_error js_payment_error_expire">
                            <?= $this->Form->formatTemplate('error', ['content' => __(Message::ERROR_TOKEN_PAYMENT_EXPIRE)]) ?>
                        </span>
                    </p>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('payment/securityCode') ?>
                        <?= $this->element('User/Common/form/require') ?>
                    </div>
                </th>
                <td>
                    <p class="cmn-txt">
                        <?= $this->Form->control('', [
                            'type' => 'text',
                            'id' => 'payment-security-code',
                            'class' => ['textbox_w150', 'js_payment_input', 'js_payment_security_code'],
                            'maxlength' => 4,
                            'value' => '',
                            'secure' => $this->Form::SECURE_SKIP,
                        ]) ?>
                        <span class="hidden js_payment_error js_payment_error_security_code">
                            <?= $this->Form->formatTemplate('error', ['content' => __(Message::ERROR_TOKEN_PAYMENT_SECURITY_CODE)]) ?>
                        </span>
                    </p>
                </td>
            </tr>
            <?php if ($this->Setting->requiresKycGmo()): ?>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('payment/name') ?>
                            <?= $this->element('User/Common/form/require') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= $this->Form->control('', [
                                'type' => 'text',
                                'id' => 'payment-name',
                                'class' => ['textbox_w300', 'js_payment_input', 'js_payment_name'],
                                'maxlength' => 50,
                                'value' => '',
                                'secure' => $this->Form::SECURE_SKIP,
                            ]) ?>
                            <span class="hidden js_payment_error js_payment_error_name">
                                <?= $this->Form->formatTemplate('error', ['content' => __(Message::ERROR_TOKEN_PAYMENT_NAME)]) ?>
                            </span>
                        </p>
                    </td>
                </tr>
            <?php elseif ($this->Setting->requiresKycSb()): ?>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('payment/name') ?>
                            <?= $this->element('User/Common/form/require') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= $this->Form->control('', [
                                'type' => 'text',
                                'id' => 'payment-name-sb',
                                'class' => ['textbox_w300', 'js_payment_input', 'js_payment_name_sb'],
                                'maxlength' => 45,
                                'value' => '',
                                'secure' => $this->Form::SECURE_SKIP,
                            ]) ?>
                            <div class="mgt-10">
                                <?= $this->Tr->h('payment/sb/name/message') ?>
                            </div>
                            <span class="hidden js_payment_error js_payment_error_name_sb">
                                <?= $this->Form->formatTemplate('error', ['content' => __(Message::ERROR_TOKEN_PAYMENT_NAME)]) ?>
                            </span>
                        </p>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </fieldset>
    <div class="hidden">
        <span
            class="js_payment_setting"
            data-setting="<?= h(json_encode($this->Payment->getSettingForJs())) ?>"
            data-token-name="<?= h($paymentTokenName) ?>"
            data-token-number="<?= h($paymentTokenNumber) ?>"
        >
        </span>
        <?php foreach ($this->Payment->getTokenErrorCodes() as $errors): ?>
            <?php foreach ($errors['code'] as $code): ?>
                <?= $this->Form->hidden('', [
                    'class' => ['js_payment_result_code_' . $code],
                    'value' => $errors['type'],
                    'secure' => $this->Form::SECURE_SKIP,
                ]) ?>
            <?php endforeach; ?>
            <?php foreach ($errors['error'] as $error): ?>
                <?= $this->Form->hidden('', [
                    'class' => ['js_payment_result_error_' . $error],
                    'value' => $errors['type'],
                    'secure' => $this->Form::SECURE_SKIP,
                ]) ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php if ($this->Setting->requiresKycGmo()): ?>
    <div class="js_payment_kyc_fieldset hidden">
        <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('payment/kycInfo') ?></h3>
        <p class="cmn-txt fwb mgb-10"><?= $this->Tr->nl2br('payment/kycInfo/message') ?></p>
        <?= $this->Flash->render('reservationsError') ?>
        <?php if (!is_null($this->Payment->getKycError($continuousForm))) : ?>
            <div class="mgb-10">
                <?= $this->Form->formatTemplate('error', ['content' => $this->Payment->getKycError($continuousForm)]) ?>
            </div>
        <?php endif; ?>
        <fieldset class="input-info">
            <table class="input-box">
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('payment/emailAddress') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= $this->Form->control('payment_email_address', [
                                'type' => 'text',
                                'class' => ['textbox_w300', 'js_payment_email_address'],
                            ]) ?>
                            <div class="mgt-10">
                                <?= $this->Tr->h('payment/emailAddress/message') ?>
                            </div>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('payment/phoneNumber') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <dl>
                                <dd>
                                    <?= $this->Template->radio('payment_phone_type', [
                                        'type' => 'radio',
                                        'class' => ['js_payment_phone_type'],
                                        'options' => $valueOptions['paymentPhoneType'],
                                    ]) ?>
                                </dd>
                                <dd>
                                    <?= $this->Form->control('payment_phone_number', [
                                        'type' => 'text',
                                        'class' => ['textbox_w300', 'js_payment_phone_number'],
                                    ]) ?>
                                </dd>
                            </dl>
                            <div class="mgt-10">
                                <?= $this->Tr->h('payment/phoneNumber/message') ?>
                            </div>
                        </p>
                    </td>
                </tr>
            </table>
        </fieldset>
    </div>
<?php elseif ($this->Setting->requiresKycSb()): ?>
    <div class="js_payment_kyc_fieldset hidden">
        <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('payment/kycInfo') ?></h3>
        <p class="cmn-txt fwb mgb-10"><?= $this->Tr->nl2br('payment/kycInfo/message') ?></p>
        <div class="mgb-10 hidden js_payment_error js_payment_error_kyc">
            <?= $this->Form->formatTemplate('error', ['content' => __(Message::ERROR_PAYMENT_KYC, __('payment/emailAddress'), __('payment/phoneNumber'))]) ?>
        </div>
        <fieldset class="input-info">
            <table class="input-box">
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('payment/emailAddress') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= $this->Form->control('', [
                                'type' => 'text',
                                'id' => 'payment-email-address-sb',
                                'class' => ['textbox_w300', 'js_payment_email_address_sb'],
                                'value' => '',
                                'secure' => $this->Form::SECURE_SKIP,
                            ]) ?>
                            <div class="mgt-10">
                                <?= $this->Tr->h('payment/emailAddress/message') ?>
                            </div>
                            <span class="hidden js_payment_error js_payment_error_email_address_sb">
                                <?= $this->Form->formatTemplate('error', ['content' => __(Message::ERROR_MAIL_ADDRESS)]) ?>
                            </span>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('payment/phoneNumber') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= $this->Form->control('', [
                                'type' => 'text',
                                'id' => 'payment-phone-number-sb',
                                'class' => ['textbox_w300', 'js_payment_phone_number_sb'],
                                'placeholder' => $this->Tr->t('payment/sb/phoneNumber/placeholder'),
                                'value' => '',
                                'secure' => $this->Form::SECURE_SKIP,
                            ]) ?>
                            <div class="mgt-10">
                                <?= $this->Tr->h('payment/sb/phoneNumber/message') ?>
                            </div>
                        </p>
                        <span class="hidden js_payment_error js_payment_error_phone_number_sb">
                            <?= $this->Form->formatTemplate('error', ['content' => __(Message::ERROR_INVALID_PHONE_NUMBER)]) ?>
                        </span>
                    </td>
                </tr>
            </table>
        </fieldset>
    </div>
<?php endif; ?>
