<?php
use Cake\Utility\Hash;
?>
<?= $this->element('InputType/Inputs/detail', [
    'formItem' => $formItem,
    'detailValue' => '********',
    'escape' => true,
    'additionHtml' => Hash::get($options, 'passwordEditButton'),
    'options' => $options,
]) ?>
