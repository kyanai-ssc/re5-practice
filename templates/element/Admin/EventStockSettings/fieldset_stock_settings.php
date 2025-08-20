<dl class="groupInput-detail js_event_stock_settings_container_<?= h($eventStockSettingIndex) ?>" <?php if (isset($eventStockSettingData->id)) : ?> id="eventStockSettings<?= h($eventStockSettingData->id) ?>"         <?php endif; ?>>
    <dt class="addIndex"><?= (is_numeric($eventStockSettingIndex) ? h($eventStockSettingIndex + 1) : h($eventStockSettingIndex)); ?></dt>
    <dd>
        <?= $this->FormError->errorWithoutNested('event_stock_settings.' . $eventStockSettingIndex . '._reserve'); ?>
        <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"></use></svg>', [
            'type' => 'button',
            'title' => '削除',
            'class' => ['js_remove_input', 'btn-input', 'is-delete', ' groupInputDel ', 'tooltip'],
            'data-selector' => '.js_event_stock_settings_container_' . $eventStockSettingIndex,
            'data-context' => '.js_event_stock_settings_container',
            'escapeTitle' => false,
        ]) ?>
        <dl class="groupInput-detail-in">
            <dt>適用期間</dt>
            <dd class="d-flex">
                <input type="hidden" class="js_event_stock_settings_index" value="<?= h($eventStockSettingIndex) ?>"/>
                <?= $this->Form->hidden('event_stock_settings.' . $eventStockSettingIndex . '.id') ?>

                <?= $this->Form->control('event_stock_settings.' . $eventStockSettingIndex . '.date_from', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['js-datepicker-type-range-start', 'w-180'],
                    'data-datepicker-dd' => 'on',
                ]) ?>
                <span class="txt mgl-10 mgr-10">から</span>
                <?= $this->Form->control('event_stock_settings.' . $eventStockSettingIndex . '.date_to', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['js-datepicker-type-range-end', 'w-180'],
                    'data-datepicker-dd' => 'on',
                ]) ?>
                <span class="txt mgl-10">の期間</span>
            </dd>
        </dl>
        <dl class="groupInput-detail-in holiday-wDay">
            <dt>適用曜日</dt>
            <dd class="d-flex">
                <?php $weekKey = 0; ?>
                <?= $this->FormError->errorWithoutNested('event_stock_settings.' . $eventStockSettingIndex . '.event_stock_setting_weeks') ?>
                <?php foreach ($valueOptions['week'] as $key => $week) : ?>
                    <?= $this->Template->checkbox('event_stock_settings.' . $eventStockSettingIndex . '.event_stock_setting_weeks.' . $weekKey . '.week', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => $week],
                        'value' => $key,
                        'id' => 'event_stock_settings-' . $eventStockSettingIndex . '-event_stock_setting_weeks-' . $key,
                    ]) ?>
                    <?php $weekKey++; ?>
                <?php endforeach; ?>
            </dd>
        </dl>
        <dl class="groupInput-detail-in">
            <dt>適用時間</dt>
            <dd class="d-flex">
                <?= $this->Form->control('event_stock_settings.' . $eventStockSettingIndex . '.time_from', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['js-timepicker'],
                ]) ?>
                <span class="txt mgl-10 mgr-10">から</span>
                <?= $this->Form->control('event_stock_settings.' . $eventStockSettingIndex . '.time_to', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['js-timepicker'],
                ]) ?>
                <span class="txt mgl-10">の時間</span>
            </dd>
        </dl>
        <dl class="groupInput-detail-in">
            <dt>在庫</dt>
            <dd class="d-flex">
                <?= $this->Form->control('event_stock_settings.' . $eventStockSettingIndex . '.stock', [
                    'type' => 'text',
                    'label' => false,
                ]) ?>
            </dd>
        </dl>
        <dl class="groupInput-detail-in">
            <dt>適用除外日</dt>
            <dd class="holidayEx-wrap">
                <div class="holidayEx js_event_stock_setting_exclude_dates_container_<?= h($eventStockSettingIndex) ?>">
                    <?php if (isset($eventStockSettingData->event_stock_setting_exclude_dates)): ?>
                        <?php foreach ($eventStockSettingData->event_stock_setting_exclude_dates as $excludeDatesIndex => $excludeDatesData): ?>
                            <?= $this->element('Admin/EventStockSettings/fieldset_exclude_dates', [
                                'eventStockSetting' => $eventStockSettingData,
                                'excludeDatesIndex' => $excludeDatesIndex,
                                'excludeDatesData' => $excludeDatesData,
                                'eventStockSettingIndex' => $eventStockSettingIndex,
                            ]) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?= $this->Form->error('event_stock_settings.' . $eventStockSettingIndex . '.event_stock_setting_exclude_dates'); ?>
                <?= $this->Form->button('適用除外日追加', [
                    'type' => 'button',
                    'class' => ['js_add_input', 'cmn-btn', 'is-addCell', ' mgt-20', ' holidayExAddBtn'],
                    'data-container' => '.js_event_stock_setting_exclude_dates_container_' . $eventStockSettingIndex,
                    'data-html' => $this->element('Admin/EventStockSettings/fieldset_exclude_dates', [
                        'eventStockSetting' => $eventStockSetting,
                        'excludeDatesIndex' => '%INDEX2%',
                        'excludeDatesData' => null,
                        'eventStockSettingIndex' => $eventStockSettingIndex,
                    ]),
                    'data-index-element' => '.js_event_stock_setting_exclude_dates_index',
                    'data-index-replace' => '%INDEX2%',
                ]) ?>
            </dd>
        </dl>
    </dd>
</dl>

