<?php $this->start('user_common_confirm_dialog'); ?>
    <div>
        <div class="js_confirm_dialog_message">
        </div>
        <div class="js_confirm_dialog_addition">
        </div>
    </div>
<?php $this->end('user_common_confirm_dialog'); ?>
<div class="hidden">
    <input type="hidden" class="js_confirm_dialog_html" value="<?= h($this->fetch('user_common_confirm_dialog')) ?>" />
    <button type="button" class="js_confirm_dialog_yes" value="<?= $this->Tr->h('dialog/yes') ?>"></button>
    <button type="button" class="js_confirm_dialog_no" value="<?= $this->Tr->h('dialog/no') ?>"></button>
</div>
