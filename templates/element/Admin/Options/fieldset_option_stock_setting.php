<tr class="js_stock_setting_container_<?= h($optionStockSettingIndex) ?>">
    <td>
        <div class="d-flex">
            <?= $this->Form->control('option_stock_settings.' . $optionStockSettingIndex . '.usage_timestamp_from', [
                'type' => 'text',
                'label' => false,
                'class' => ['js-datepicker-time'],
            ]) ?>
            <span class="txt mgl-10 mgr-10">～</span>
            <?= $this->Form->control('option_stock_settings.' . $optionStockSettingIndex . '.usage_timestamp_to', [
                'type' => 'text',
                'label' => false,
                'class' => ['js-datepicker-time',]
            ]) ?>
        </div>
    </td>
    <td>
        <?= $this->Form->control('option_stock_settings.' . $optionStockSettingIndex . '.stock', [
            'type' => 'text',
            'label' => false,
        ]) ?>
    </td>
    <td>
        <input type="hidden" class="js_stock_setting_index" value="<?= h($optionStockSettingIndex) ?>"/>
        <?= $this->Form->hidden('option_stock_settings.' . $optionStockSettingIndex . '.id') ?>

        <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
            'type' => 'button',
            'title' => '削除',
            'class' => ['js_remove_input','btn-input','is-delete'],
            'data-selector' => '.js_stock_setting_container_' . $optionStockSettingIndex,
            'data-context' => '.js_stock_setting_container',
            'escapeTitle' => false,
        ]) ?>
    </td>
</tr>
