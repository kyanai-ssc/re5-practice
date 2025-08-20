<?php $this->start('inputTypeUserAuthorityFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'select',
            'options' => $formItem->getInputTypeItem()->getValueOptions(false),
            'class' => ['js_user_authority_id', 'select'],
        ]) ?>
    </div>
<?php $this->end('inputTypeUserAuthorityFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeUserAuthorityFieldset'),
]) ?>
