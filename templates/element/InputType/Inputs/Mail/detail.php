<?php
use Cake\Utility\Hash;
?>
<?= $this->element('InputType/Inputs/detail', [
    'formItem' => $formItem,
    'detailValue' => $detailValue,
    'escape' => true,
    'additionHtml' => Hash::get($options, 'mailEditButton'),
    'options' => $options,
]) ?>
