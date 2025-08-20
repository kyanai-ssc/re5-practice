<dl class="js_form_item_choices_container_<?= h($inputType) ?>_<?= h($formItemChoiceIndex) ?> contents-item d-flex">
    <dt>
        選択肢
        <input type="hidden" class="js_form_item_choices_index" value="<?= h($formItemChoiceIndex) ?>"/>
    </dt>
    <dd class="d-flex">
        <?= $this->Form->hidden('form_item_choices.' . $formItemChoiceIndex . '.id') ?>
        <?= $this->Form->control('form_item_choices.' . $formItemChoiceIndex . '.name',[
            'type' => 'text',
        ]) ?>
        <?php if (!isset($formItem) || ((string)$formItem->default_flg) !== ((string)$this->Configure->read('Master.common.flg.on'))) : ?>
            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"></use></svg>', [
                'type' => 'button',
                'title' => '削除,',
                'class' => ['js_remove_input', 'btn-input', 'is-delete'],
                'data-selector' => '.js_form_item_choices_container_' . $inputType . '_' . $formItemChoiceIndex,
                'data-context' => '.js_form_item_choices_container_' . $inputType,
                'escapeTitle' => false,
            ]) ?>
    <?php endif; ?>
    </dd>
</dl>
