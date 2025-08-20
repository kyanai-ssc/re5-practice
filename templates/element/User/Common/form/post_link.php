<div class="hidden">
    <?= $this->Form->create(null, [
        'type' => 'post',
        'url' => '.',
        'idPrefix' => 'form-post-link',
        'novalidate' => true,
        'class' => ['js_submit_once', 'js_post_link_form'],
    ]) ?>

    <?= $this->Form->control('js_button_yes', [
        'type' => 'hidden',
        'value' => $this->Tr->t('dialog/yes'),
        'class' => ['js_button_yes'],
    ]) ?>

    <?= $this->Form->control('js_button_no', [
        'type' => 'hidden',
        'value' => $this->Tr->t('dialog/no'),
        'class' => ['js_button_no'],
    ]) ?>

    <?= $this->Form->end() ?>

</div>
