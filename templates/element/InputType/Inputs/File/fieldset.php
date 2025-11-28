<?php $this->start('inputTypeSampleFileAttachedFieldset'); ?>
    <?= $this->element('InputType/Inputs/File/fieldset_content', [
        'formItem' => $formItem,
    ]) ?>
<?php $this->end('inputTypeSampleFileAttachedFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeSampleFileAttachedFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
