<?php $this->start('inputTypeMailConfirmFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'text',
        ]) ?>
    </div>
<?php $this->end('inputTypeMailConfirmFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeMailConfirmFieldset'),
]) ?>
