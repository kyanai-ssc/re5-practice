<?php if (isset($detailValue)): ?>
    <?= $this->element('InputType/Inputs/detail', [
        'formItem' => $formItem,
        'detailValue' => $detailValue,
        'escape' => false,
        'divAddClass' => 'wysiwyg-area',
        'options' => $options,
    ]) ?>
<?php endif; ?>
