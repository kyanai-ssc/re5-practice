<?php if (isset($options['mode']) && ($options['mode'] === 'detail' || $options['mode'] === 'guestDetail')): ?>
    <?= $this->element('InputType/Inputs/detail', [
        'formItem' => $formItem,
        'detailValue' => $detailValue,
        'escape' => true,
        'options' => $options,
    ]) ?>
<?php endif; ?>
