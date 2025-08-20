<?php $this->start('inputTypePrefectureFieldset'); ?>
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'select',
            'options' => $formItem->getInputTypeItem()->getValueOptions(),
            'empty' => true,
            'class' => ['select'],
        ]) ?>
    </div>
<?php $this->end('inputTypePrefectureFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypePrefectureFieldset'),
]) ?>
