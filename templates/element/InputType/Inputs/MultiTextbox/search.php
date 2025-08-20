<?php $this->start('inputTypeMultiTextboxSearch'); ?>
    <td>
        <div>
            <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey() . '.' . 'empty', [
                'type' => 'checkbox',
                'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '未入力'],
                'value' => $this->Configure->readOrFail('Master.common.flg.on'),
            ]) ?>
        </div>
        <div>
            <?php for ($i = 0; $i < $formItem->getInputTypeItem()->getFieldCount(); ++$i): ?>
                <span class="txt"><?= h($formItem->getInputTypeItem()->getFrontWord($i)) ?></span>
                <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.value.value_' . $i, [
                    'type' => 'text',
                    'class' => ['textbox_w150'],
                ]) ?>
            <?php endfor; ?>
        </div>
    </td>
<?php $this->end('inputTypeMultiTextboxSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeMultiTextboxSearch'),
]) ?>
