<?php $this->start('inputTypeExpirationDateSearch'); ?>
<td>
    <div class="d-flex">
        <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.from', [
            'type' => 'text',
            'class' => ['js-datepicker-type-range-start'],
        ]) ?>
        <span class="txt mgl-10 mgr-10">～</span>
        <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.to', [
            'type' => 'text',
            'class' => ['js-datepicker-type-range-end'],
        ]) ?>
    </div>
</td>
<?php $this->end('inputTypeExpirationDateSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeExpirationDateSearch'),
]) ?>
