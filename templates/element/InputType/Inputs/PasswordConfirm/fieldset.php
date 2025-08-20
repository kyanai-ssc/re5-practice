<?php $this->start('inputTypePasswordConfirmFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'password',
            'value' => '',
            'autocomplete' => 'new-password',
        ]) ?>
    </div>
<?php $this->end('inputTypePasswordConfirmFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypePasswordConfirmFieldset'),
]) ?>
