<?php $this->start('inputTypeExpirationDateFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '_from', [
            'type' => 'text',
            'class' => ['js-datepicker'],
            'templateVars' => ['backWord' => 'から']
        ]) ?>
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '_to', [
            'type' => 'text',
            'class' => ['js-datepicker'],
            'templateVars' => ['backWord' => 'の期間']
        ]) ?>
    </div>
<?php $this->end('inputTypeExpirationDateFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeExpirationDateFieldset'),
]) ?>
