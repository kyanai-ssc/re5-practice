<?php $this->start('inputTypeUsageTimeDetail'); ?>
    <?= $detailValue ?>
<?php $this->end('inputTypeUsageTimeDetail'); ?>
<?= $this->element('InputType/Inputs/detail', [
    'formItem' => $formItem,
    'detailValue' => $this->fetch('inputTypeUsageTimeDetail'),
    'escape' => false,
    'divAddClass' => 'fwb',
    'options' => $options,
]) ?>
