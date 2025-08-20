<?php $this->start('inputTypeMailSearch'); ?>
    <td>
        <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey(), [
            'type' => 'text',
        ]) ?>
    </td>
<?php $this->end('inputTypeMailSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeMailSearch'),
]) ?>
