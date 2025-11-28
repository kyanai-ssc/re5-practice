<?php $this->start('inputTypeFileSearch'); ?>
    <td>
        <div>
            <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey(), [
                'type' => 'multicheckbox',
                'label' => false,
                'options' => $formItem->getInputTypeItem()->getValueOptions(),
            ]) ?>
        </div>
    </td>
<?php $this->end('inputTypeFileSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeFileSearch'),
]) ?>
