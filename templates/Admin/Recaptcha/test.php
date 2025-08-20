<?php

use App\Model\Entity\RecaptchaSetting;

$this->assign('title', 'reCAPTCHAv3設定テスト');
$this->assign('noNavi', true);

$this->Html->script('https://www.google.com/recaptcha/api.js?render=' . rawurlencode($this->Recaptcha->getData()->get('site_key')), [
    'block' => true,
]);
$this->Html->script('common/recaptcha', [
    'block' => true,
]);
?>
<?= $this->Form->create(null, [
    'type' => 'post',
    'class' => ['js_submit_once', 'js_recaptcha_form'],
    'novalidate' => true,
] + $this->Recaptcha->getFormAttribute(RecaptchaSetting::ACTION_TEST)) ?>
<h1>reCAPTCHAv3設定テスト</h1>
<?= $this->Flash->render('recaptchaTestFinish') ?>
<?= $this->Flash->render('recaptchaTestErrors') ?>
<section>
    <h3>「確認」ボタンを押すと、設定したサイトキー、シークレットキーを利用してreCAPTCHAv3の動作確認を行います。</h3>
</section>
<?= $this->Recaptcha->getTokenElement() ?>
<div class="btn-box mgt-20">
    <?= $this->Form->button('閉じる', [
        'type' => 'button',
        'class' => ['cmn-btn', 'is-gray', 'js_close_window'],
    ]); ?>
    <?= $this->Form->button('確認', [
        'type' => 'submit',
        'class' => ['cmn-btn', 'is-blue'],
    ]) ?>
</div>
<?= $this->Form->end() ?>
