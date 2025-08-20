<?php $this->start('inputTypeReservationNumberFieldset'); ?>
    <div class="cmnInput">
        <?php if (!$formItem->getInputTypeItem()->isFixedValueOptions()): ?>
                <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
                    'type' => 'select',
                    'options' => $formItem->getInputTypeItem()->getValueOptions(),
                    'empty' => true,
                    'class' => ['select']
                ]) ?>
        <?php else: ?>
            <?php foreach ($formItem->getInputTypeItem()->getValueOptions() as $key => $value): ?>
                <span><?= h($value) ?></span>
                <?= $this->Form->hidden($formItem->getInputTypeItem()->getFieldsetInputKey(), [
                    'value' => $key,
                ]) ?>
            <?php endforeach; ?>
            <?= $this->FormError->errorWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()) ?>
        <?php endif; ?>
    </div>
<?php $this->end('inputTypeReservationNumberFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeReservationNumberFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
