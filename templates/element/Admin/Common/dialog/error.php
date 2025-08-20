<?php $this->start('admin_common_error_dialog'); ?>
    <div>
        <div class="js_error_dialog_message">
        </div>
        <div>
            <button type="button" class="js_error_dialog_close">閉じる</button>
        </div>
    </div>
<?php $this->end('admin_common_error_dialog'); ?>
<div class="hidden">
    <input type="hidden" class="js_error_dialog_html" value="<?= h($this->fetch('admin_common_error_dialog')) ?>" />
</div>
