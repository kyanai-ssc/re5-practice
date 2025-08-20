<?php
$this->Form->unlockField('recaptcha_token');
?>
<?= $this->Form->hidden('recaptcha_token', [
    'class' => ['js_recaptcha_token'],
    'value' => '',
    'secure' => $this->Form::SECURE_SKIP,
]) ?>
