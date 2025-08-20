<?php $this->start('inputTypeCheckboxFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Template->checkbox($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'multicheckbox',
            'options' => $formItem->getInputTypeItem()->getValueOptions(),
            'templateVars' => $this->InputType->renderFrontBackWord($formItem),
        ]) ?>
    </div>
<?php $this->end('inputTypeCheckboxFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeCheckboxFieldset'),
]) ?>
