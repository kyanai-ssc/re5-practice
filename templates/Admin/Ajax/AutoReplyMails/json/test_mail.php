<?php
$this->assign('ajax_massage', null);
?>

<?php $this->start('ajax_massage'); ?>
<div class="popup-content">
    <?php if ($checkResult) : ?>
        <?= h(__('テストメールの送信が完了しました。')) ?>
    <?php else : ?>
        <?php foreach ($errors as $name => $error) : ?>
            <p><?= h($formName[$name]); ?>：<?= $this->Form->formatTemplate('error', ['content' => $error]) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php $this->end('ajax_massage'); ?>

<?= $this->Ajax->json([
    'result' => $checkResult,
    'message' => $this->fetch('ajax_massage'),
    'title' => __('テストメール送信')
]) ?>
