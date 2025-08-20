<div class="btn-inline tac js_mail_check_container mgt-20">
    <?= $this->Template->checkbox('waiting_cancellation', [
        'type' => 'checkbox',
        'class' => ['cmn-check'],
        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => 'キャンセル待ち通知を送信する'],
    ]) ?>
</div>
