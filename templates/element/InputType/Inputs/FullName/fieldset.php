<?php $this->start('inputTypeFullNameFieldset'); ?>
<div class="nameInput d-flex">
    <?php for ($i = 0; $i < $formItem->getInputTypeItem()->getFieldCount(); ++$i): ?>
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '.value_' . $i, [
            'type' => 'text',
            'templateVars' => $this->InputType->renderFrontBackWord($formItem, ['index' => $i]),
            'templates' => $this->InputType->renderErrorTemplates($formItem),
            'class' => ['textbox_w150'] + $this->FormError->addFieldErrorClassWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()),
        ]) ?>
    <?php endfor; ?>
</div>
<?= $this->FormError->errorWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()) ?>
<?php $this->end('inputTypeFullNameFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeFullNameFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
