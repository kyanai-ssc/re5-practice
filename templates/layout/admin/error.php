<?php
$this->extend('/layout/admin/default');
?>
<aside class="cmn-msg is-err">
    <p>
        <svg class="icon is-msg">
            <use xlink:href="#icon_clear"></use>
        </svg>
        <?= $this->fetch('content') ?>
    </p>
</aside>
<p class="cmn-txt mgt-40 tac mgb-20">
    <?php if (!empty($backUrl)) : ?>
        <?= $this->Form->button('戻る', [
            'type' => 'button',
            'class' => ['js_change_url', 'cmn-btn', 'is-gray'],
            'data-url' => $backUrl
        ]) ?>
    <?php else : ?>
        <?= $this->Form->button('戻る', [
            'type' => 'button',
            'class' => ['js_history_back', 'cmn-btn', 'is-gray'],
        ]) ?>
    <?php endif; ?>
</p>
