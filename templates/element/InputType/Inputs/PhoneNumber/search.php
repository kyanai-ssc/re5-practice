<?php $this->start('inputTypePhoneNumberSearch'); ?>
    <td>
        <div>
            <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey() . '.' . 'empty', [
                'type' => 'checkbox',
                'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '未入力'],
                'value' => $this->Configure->readOrFail('Master.common.flg.on'),
            ]) ?>
        </div>
        <div>
            <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.' . 'value', [
                'type' => 'text',
                'label' => false,
            ]) ?>
        </div>
    </td>
<?php $this->end('inputTypePhoneNumberSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypePhoneNumberSearch'),
]) ?>
