<?php $this->start('inputTypeDateSelectFieldset'); ?>
    <?php $defaultDate = $formItem->getInputTypeItem()->getDefaultValue(); ?>
    <?php if (isset($defaultDate)): ?>
        <?php $defaultDate = $defaultDate->format('Y/m/d'); ?>
    <?php endif; ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'text',
            'class' => ['js-datepicker'],
            'data-start' => $formItem->getInputTypeItem()->getLowerLimit()->format('Y/m/d'),
            'data-end' => $formItem->getInputTypeItem()->getUpperLimit()->format('Y/m/d'),
            'default' => $defaultDate,
        ]) ?>
    </div>
<?php $this->end('inputTypeDateSelectFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeDateSelectFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
