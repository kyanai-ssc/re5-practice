<?php $this->start('inputTypeAkerunUserIdSearch'); ?>
    <td>
        <div>
            <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey(), [
                'type' => 'text',
                'label' => false,
            ]) ?>
        </div>
    </td>
<?php $this->end('inputTypeAkerunUserIdSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeAkerunUserIdSearch'),
]) ?>
