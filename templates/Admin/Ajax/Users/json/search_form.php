<?php $this->start('ajax_html'); ?>
    <?= $this->element('Admin/Users/search', [
        'searchForm' => $searchForm,
    ]) ?>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
]) ?>
