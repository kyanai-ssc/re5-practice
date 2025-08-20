<?php $this->start('inputTypeAkerunUserIdFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'text',
            'templateVars' => $this->InputType->renderFrontBackWord($formItem),
        ]) ?>
    </div>
<?php $this->end('inputTypeAkerunUserIdFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeAkerunUserIdFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
