<?php $this->start('inputTypeMultiTextboxFieldset'); ?>
<div class="cmnInput">
    <?php for ($i = 0; $i < $formItem->getInputTypeItem()->getFieldCount(); ++$i): ?>
        <div class="mgb-10">
            <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '.value_' . $i, [
                'type' => 'text',
                'templateVars' => $this->InputType->renderFrontBackWord($formItem, ['index' => $i]),
                'class' => $this->FormError->addFieldErrorClassWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()),
            ]) ?>
        </div>
    <?php endfor; ?>
</div>
<?= $this->FormError->errorWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()) ?>
<?php $this->end('inputTypeMultiTextboxFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeMultiTextboxFieldset'),
]) ?>
