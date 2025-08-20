<?php $this->start('inputTypePasswordFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'password',
            'value' => '',
            'autocomplete' => 'new-password',
        ]) ?>
    </div>
<?php $this->end('inputTypePasswordFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypePasswordFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
