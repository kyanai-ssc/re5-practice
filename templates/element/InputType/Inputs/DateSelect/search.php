<?php $this->start('inputTypeDateSelectSearch'); ?>
<td>
    <div>
        <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey() . '.' . 'empty', [
            'type' => 'checkbox',
            'value' => $this->Configure->readOrFail('Master.common.flg.on'),
            'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '未選択'],
        ]) ?>
    </div>
    <div class="d-flex">
        <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.value.from', [
            'type' => 'text',
            'class' => ['js-datepicker-type-range-start'],
        ]) ?>
        <span class="txt mgl-10 mgr-10">～</span>
        <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.value.to', [
            'type' => 'text',
            'class' => ['js-datepicker-type-range-end'],
        ]) ?>
    </div>
</td>
<?php $this->end('inputTypeDateSelectSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeDateSelectSearch'),
]) ?>
