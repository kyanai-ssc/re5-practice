<div class="btn-inline tac js_mail_check_container mgt-20">
    <?= $this->Template->checkbox('mail_check', [
        'type' => 'checkbox',
        'class' => ['cmn-check'],
        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '予約者と管理者に自動返信メールを送る'],
    ]) ?>
</div>
