<div class="hidden">
    <?= $this->Form->create(null, [
        'type' => 'post',
        'url' => '.',
        'idPrefix' => 'form-post-link',
        'novalidate' => true,
        'class' => ['js_submit_once js_post_link_form'],
    ]) ?>
    <?= $this->Form->end() ?>
</div>
