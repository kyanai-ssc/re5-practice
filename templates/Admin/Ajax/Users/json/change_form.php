<?php $this->start('ajax_html'); ?>
    <?= $this->element('Admin/Users/form', [
        'userForm' => $userForm,
        'valueOptions' => $valueOptions,
        'mode' => $mode,
    ]) ?>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
]) ?>
