<?php $this->start('inputTypePhoneNumberFieldset'); ?>
<div class="telInput d-flex">
    <?php for ($i = 0; $i < $formItem->getInputTypeItem()->getFieldCount(); ++$i): ?>
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '.value_' . $i, [
            'type' => 'text',
            'class' => ['textbox_w70'] + $this->FormError->addFieldErrorClassWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()),
            'templates' => $this->InputType->renderErrorTemplates($formItem),
        ]) ?>
        <?php if ($i < $formItem->getInputTypeItem()->getFieldCount() - 1): ?>
            <span class="txt">-</span>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?= $this->FormError->errorWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()) ?>
<?php $this->end('inputTypePhoneNumberFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypePhoneNumberFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
