<?php $this->start('ajax_file'); ?>
    <?= $this->element('InputType/Inputs/File/fieldset_content', [
        'formItem' => $uploadForm->getFormItem(),
    ]) ?>
<?php $this->end('ajax_file'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_file'),
]) ?>