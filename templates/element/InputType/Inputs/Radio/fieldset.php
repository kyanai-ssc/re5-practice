<?php $this->start('inputTypeRadioFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Template->radio($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'radio',
            'options' => $formItem->getInputTypeItem()->getValueOptions(),
            'templateVars' => $this->InputType->renderFrontBackWord($formItem),
        ]) ?>
    </div>
<?php $this->end('inputTypeRadioFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeRadioFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
