<?php $this->start('inputTypeTextareaFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'textarea',
            'templateVars' => $this->InputType->renderFrontBackWord($formItem),
        ]) ?>
    </div>
<?php $this->end('inputTypeTextareaFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeTextareaFieldset'),
]) ?>
