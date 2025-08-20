<p class="holidayEx-in d-flex js_event_stock_setting_exclude_dates_container_<?= h($eventStockSettingIndex) ?>_<?= h($excludeDatesIndex) ?>">
    <input type="hidden" class="js_event_stock_setting_exclude_dates_index" value="<?= h($excludeDatesIndex) ?>"/>
    <?= $this->Form->hidden('event_stock_settings.' .$eventStockSettingIndex .'.event_stock_setting_exclude_dates.'. $excludeDatesIndex .'.id') ?>
    <?= $this->Form->control('event_stock_settings.' .$eventStockSettingIndex .'.event_stock_setting_exclude_dates.'. $excludeDatesIndex .'.date', [
        'type' => 'text',
        'label' => false,
        'class' => ['js-datepicker'],
    ]) ?>

    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"></use></svg>', [
        'type' => 'button',
        'class' => ['js_remove_input','btn-input',' is-delete',' holidayExDel',' tooltip'],
        'title' => '削除',
        'data-selector' => '.js_event_stock_setting_exclude_dates_container_' .$eventStockSettingIndex .'_' . $excludeDatesIndex,
        'data-context' => '.js_event_stock_setting_exclude_dates_container_' . $eventStockSettingIndex,
        'escapeTitle' => false,
    ]) ?>
</p>




