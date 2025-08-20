<?php $this->Form->unlockField('event_stock_settings'); ?>
<div class="groupInput">
    <div class="js_event_stock_settings_container groupInput-box">
    <?php if (isset($eventStockSetting)): ?>
        <?php foreach ($eventStockSetting as $eventStockSettingIndex => $eventStockSettingData): ?>
            <?= $this->element('Admin/EventStockSettings/fieldset_stock_settings', [
                'eventStockSetting' => $eventStockSetting,
                'eventStockSettingIndex' => $eventStockSettingIndex,
                'eventStockSettingData' => $eventStockSettingData,
            ]) ?>
        <?php endforeach; ?>
        <?= $this->FormError->errorWithoutNested('event_stock_settings'); ?>
    <?php endif; ?>
    </div>
    <?= $this->Form->button('例外日追加', [
        'type' => 'button',
        'class' =>['js_add_input', 'cmn-btn', 'is-addCell', ' mgt-20', ' holidayAdd'],
        'data-container' => '.js_event_stock_settings_container',
        'data-html' => $this->element('Admin/EventStockSettings/fieldset_stock_settings', [
            'eventStockSetting' => $eventStockSetting,
            'eventStockSettingIndex' => '%INDEX%',
            'eventStockSetting' => null,
        ]),
        'data-index-element' => '.js_event_stock_settings_index',
        'data-index-replace' => '%INDEX%',
    ]) ?>
</div>
