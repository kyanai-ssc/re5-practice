<?php $this->start('user_common_error_dialog'); ?>
    <div>
        <div class="js_error_dialog_message">
        </div>
        <div>
            <button type="button" class="js_error_dialog_close"><?= $this->Tr->h('dialog/close') ?></button>
        </div>
    </div>
<?php $this->end('user_common_error_dialog'); ?>
<div class="hidden">
    <input type="hidden" class="js_error_dialog_html" value="<?= h($this->fetch('user_common_error_dialog')) ?>" />
</div>
