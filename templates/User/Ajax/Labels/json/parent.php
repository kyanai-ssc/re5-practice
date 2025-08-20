<?php
$this->assign('ajax_list', null);
?>
<?php $this->start('ajax_parent'); ?>
    <?= $this->element('User/Labels/select', [
        'labelInputs' => $labelInputs,
        'formType' => $formType,
        'excludeId' => $excludeId,
        'isAjax' => true,
        'onlyPublic' => true,
    ]) ?>

<?php $this->end('ajax_parent'); ?>

<?= $this->Ajax->json([
    'result' => $checkResult,
    'html' => $this->fetch('ajax_parent'),
]) ?>
