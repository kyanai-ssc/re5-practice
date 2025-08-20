<?php $this->start('inputTypeLoginIdSearch'); ?>
    <td>
        <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey(), [
            'type' => 'text',
        ]) ?>
    </td>
<?php $this->end('inputTypeLoginIdSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeLoginIdSearch'),
]) ?>
