<?php $this->start('inputTypeRadioSearch'); ?>
    <td class="btn-inline">
        <div>
            <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey() . '.' . 'empty', [
                'type' => 'checkbox',
                'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '未選択'],
                'value' => $this->Configure->readOrFail('Master.common.flg.on'),
            ]) ?>
        </div>
        <div>
            <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey() . '.' . 'value', [
                'type' => 'multicheckbox',
                'label' => false,
                'options' => $formItem->getInputTypeItem()->getValueOptions(),
            ]) ?>
        </div>
    </td>
<?php $this->end('inputTypeRadioSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeRadioSearch'),
]) ?>
