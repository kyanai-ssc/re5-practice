<?php
$this->assign('ajax_message', null);
?>
<?php $this->start('ajax_message'); ?>
<?php if ($finish) : ?>
    <p class="cmn-txt">アップロード完了後、アップロードを行った管理者宛に結果通知メールが送信されます。</p>
<?php else : ?>
    <?php foreach ($errorMessage as $key => $message) : ?>
        <?= $this->Form->formatTemplate('error', ['content' => $message]) ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php $this->end('ajax_message'); ?>

<?= $this->Ajax->json([
    'result' => $finish,
    'message' => $this->fetch('ajax_message'),
    'title' => 'アップロード 受付',
]) ?>
