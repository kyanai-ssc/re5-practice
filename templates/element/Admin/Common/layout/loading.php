<?php $this->start('admin_common_layout_loading') ?>
    <div id="loading" class="is-active js_loading_container">
        <?= $this->Html->image('admin/loading.gif') ?>
    </div>
<?php $this->end('admin_common_layout_loading') ?>
<div class="hidden">
    <input type="hidden" class="js_loading_html" value="<?= h($this->fetch('admin_common_layout_loading')) ?>" />
</div>
