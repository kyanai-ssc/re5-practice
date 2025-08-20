<?php $this->start('inputTypeEventNameSearch'); ?>
    <td>
        <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey(), [
            'type' => 'text',
        ]) ?>
    </td>
<?php $this->end('inputTypeEventNameSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeEventNameSearch'),
]) ?>
