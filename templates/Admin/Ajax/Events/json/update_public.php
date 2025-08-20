<?php
$this->assign('ajax_public_flg', null);
?>
<?php $this->start('ajax_public_flg'); ?>
<?php if($checkResult) : ?>
<?= $this->Form->control('public_flg_'.$event->id, [
    'type' => 'radio',
    'label' => false,
    'hiddenField' => false,
    'options' => $valueOptions['publicFlg'],
    'value' => $event->public_flg,
    'class' => ['js_public_flg_update'],
    'data-eventid' => $event->id,
    'data-before' => $event->public_flg,
]) ?>
<?php endif; ?>

<?php $this->end('ajax_public_flg'); ?>

<?= $this->Ajax->json([
    'result' => $checkResult,
    'html' => $this->fetch('ajax_public_flg'),
]) ?>
