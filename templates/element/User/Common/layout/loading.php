<?php $this->start('user_common_layout_loading') ?>
    <div id="loading" class="is-active js_loading_container">
        <?= $this->Html->image('user/loading.gif') ?>
    </div>
<?php $this->end('user_common_layout_loading') ?>
<div class="hidden">
    <input type="hidden" class="js_loading_html" value="<?= h($this->fetch('user_common_layout_loading')) ?>" />
</div>
