<?php if (isset($detailValue)): ?>
    <?= $this->element('InputType/Inputs/detail', [
        'formItem' => $formItem,
        'detailValue' => $detailValue,
        'escape' => true,
        'options' => $options,
    ]) ?>
<?php endif; ?>
