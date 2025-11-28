<div class="hidden">
    <?php $this->start('attachmentFormHtml'); ?>
        <?= $this->Form->create(null, [
            'type' => 'post',
            'novalidate' => true,
            'class' => ['js_submit_once'],
        ]) ?>
            <?= $this->Token->getTokenTag() ?>
        <?= $this->Form->end() ?>
    <?php $this->end('attachmentFormHtml'); ?>
    <input type="hidden" class="js_attachment_form_html" value="<?= h($this->fetch('attachmentFormHtml')) ?>"/>
</div>
