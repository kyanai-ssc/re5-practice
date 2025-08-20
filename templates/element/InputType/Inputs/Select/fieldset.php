<?php $this->start('inputTypeSelectFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'select',
            'options' => $formItem->getInputTypeItem()->getValueOptions(),
            'empty' => true,
            'class' => ['select'],
            'templateVars' => $this->InputType->renderFrontBackWord($formItem),
        ]) ?>
    </div>
<?php $this->end('inputTypeSelectFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeSelectFieldset'),
]) ?>
