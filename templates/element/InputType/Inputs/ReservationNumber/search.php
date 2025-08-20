<?php $this->start('inputTypeReservationNumberSearch'); ?>
    <td class="d-flex">
        <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.from', [
            'type' => 'text',
        ]) ?>
        <span class="txt mgl-10 mgr-10">～</span>
        <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.to', [
            'type' => 'text',
        ]) ?>
    </td>
<?php $this->end('inputTypeReservationNumberSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeReservationNumberSearch'),
]) ?>
