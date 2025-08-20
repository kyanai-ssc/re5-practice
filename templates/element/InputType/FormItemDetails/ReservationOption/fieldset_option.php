<div class="js_form_item_option_container_<?= h($inputType) ?>_<?= h($formItemOptionIndex) ?> multiAdd-wrap">
    <div class="ttl-detail-show">
        <span class="cmn-txt">オプション</span>
        <button type="button" class="showBtn"></button>
        <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"></use></svg>', [
            'type' => 'button',
            'title' => '削除',
            'class' => ['js_remove_input', 'btn-input', 'is-delete', 'formM'],
            'data-selector' => '.js_form_item_option_container_' . $inputType . '_' . $formItemOptionIndex,
            'data-context' => '.js_form_item_option_container_' . $inputType,
            'escapeTitle' => false,
        ]) ?>
    </div>
    <div class="showWrap">
        <input type="hidden" class="js_form_item_option_index" value="<?= h($formItemOptionIndex) ?>"/>
        <?= $this->Form->hidden('form_item_option_group.form_item_options.' . $formItemOptionIndex . '.id') ?>
        <dl class="contents-item d-flex">
            <dt class="w-200">オプション</dt>
            <dd>
                <?= $this->Form->control('form_item_option_group.form_item_options.' . $formItemOptionIndex . '.option_id', [
                    'type' => 'select',
                    'label' => false,
                    'class' => ['select'],
                    'options' => $valueOptions['formItemOptionGroups']['formItemOptions']['optionId'],
                    'empty' => true,
                ]) ?>
            </dd>
        </dl>
        <dl class="contents-item d-flex">
            <dt class="w-200">受付可能な予約数</dt>
            <dd>
                <div class="d-flex">
                    <?= $this->Form->control('form_item_option_group.form_item_options.' . $formItemOptionIndex . '.stock_range_from', [
                        'type' => 'text',
                        'class' => ['textbox_w70']
                    ]) ?>
                    <span class="txt mgl-10 mgr-10">～</span>
                    <?= $this->Form->control('form_item_option_group.form_item_options.' . $formItemOptionIndex . '.stock_range_to', [
                        'type' => 'text',
                        'class' => ['textbox_w70']
                    ]) ?>
                </div>
            </dd>
        </dl>
    </div>
</div>
