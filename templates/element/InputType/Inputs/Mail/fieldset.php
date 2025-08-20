<?php $this->start('inputTypeMailFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'text',
        ]) ?>
    </div>
<?php $this->end('inputTypeMailFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeMailFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
