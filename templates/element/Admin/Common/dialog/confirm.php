<?php $this->start('admin_common_confirm_dialog'); ?>
    <div>
        <div class="js_confirm_dialog_message">
        </div>
        <div class="js_confirm_dialog_addition">
        </div>
    </div>
<?php $this->end('admin_common_confirm_dialog'); ?>
<div class="hidden">
    <input type="hidden" class="js_confirm_dialog_html" value="<?= h($this->fetch('admin_common_confirm_dialog')) ?>" />
    <button type="button" class="js_confirm_dialog_yes" value="はい"></button>
    <button type="button" class="js_confirm_dialog_no" value="いいえ"></button>
</div>
