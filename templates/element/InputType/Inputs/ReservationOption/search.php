<?php $this->start('inputTypeReservationOptionSearch'); ?>
    <td class="btn-inline">
        <div>
            <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey() . '.' . 'empty', [
                'type' => 'checkbox',
                'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '未選択'],
                'value' => $this->Configure->readOrFail('Master.common.flg.on'),
            ]) ?>
        </div>
        <div>
            <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey() . '.value', [
                'type' => 'multicheckbox',
                'options' => $formItem->getInputTypeItem()->getSearchValueOptions(),
            ]) ?>
        </div>
    </td>
<?php $this->end('inputTypeReservationOptionSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeReservationOptionSearch'),
]) ?>
