<dl class="groupInput-detail js_event_holidays_container_<?= h($eventHolidayIndex) ?>" <?php if (isset($eventHolidayData->id)) : ?> id="eventHolidays<?= h($eventHolidayData->id) ?>"         <?php endif; ?>>
    <dt class="addIndex"><?= (is_numeric($eventHolidayIndex) ? h($eventHolidayIndex + 1) : h($eventHolidayIndex));?></dt>
    <dd>
        <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"></use></svg>', [
            'type' => 'button',
            'title' => '削除',
            'class' => ['js_remove_input', 'btn-input', 'is-delete', ' groupInputDel ', 'tooltip'],
            'data-selector' => '.js_event_holidays_container_' . $eventHolidayIndex,
            'data-context' => '.js_event_holidays_container',
            'escapeTitle' => false,
        ]) ?>
        <dl class="groupInput-detail-in">
            <dt>適用期間</dt>
            <dd class="d-flex">
                <?= $this->FormError->errorWithoutNested('event_holidays.' . $eventHolidayIndex . '._reserve'); ?>
                <input type="hidden" class="js_event_holidays_index" value="<?= h($eventHolidayIndex) ?>"/>
                <?= $this->Form->hidden('event_holidays.' . $eventHolidayIndex . '.id') ?>

                <?= $this->Form->control('event_holidays.' . $eventHolidayIndex . '.date_from', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['js-datepicker-type-range-start', 'w-180'],
                    'id' => 'event_holidays.' . $eventHolidayIndex . '.date_from',
                    'data-datepicker-dd' => 'on',
                ]) ?>
                <span class="txt mgl-10 mgr-10">から</span>
                <?= $this->Form->control('event_holidays.' . $eventHolidayIndex . '.date_to', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['js-datepicker-type-range-end', 'w-180'],
                    'id' => 'event_holidays.' . $eventHolidayIndex . '.date_to',
                    'data-datepicker-dd' => 'on',
                ]) ?>
                <span class="txt mgl-10">の期間</span>
            </dd>
        </dl>
        <dl class="groupInput-detail-in holiday-wDay">
            <dt>適用曜日</dt>
            <dd class="d-flex">
                <?php $weekKey = 0; ?>
                <?= $this->FormError->errorWithoutNested('event_holidays.' . $eventHolidayIndex . '.event_holiday_weeks') ?>
                <?php foreach ($valueOptions['week'] as $key => $week) : ?>
                    <?= $this->Template->checkbox('event_holidays.' . $eventHolidayIndex . '.event_holiday_weeks.' . $weekKey . '.week', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => $week],
                        'value' => $key,
                        'id' => 'event_holidays-' . $eventHolidayIndex . '-event_holiday_weeks-' . $key,
                    ]) ?>
                    <?php $weekKey++; ?>
                <?php endforeach; ?>
            </dd>
        </dl>
        <dl class="groupInput-detail-in">
            <dt>適用時間</dt>
            <dd class="d-flex">
                <?= $this->Form->control('event_holidays.' . $eventHolidayIndex . '.time_from', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['js-timepicker'],
                    'id' => 'event_holidays.' . $eventHolidayIndex . '.time_from',
                ]) ?>
                <span class="txt mgl-10 mgr-10">から</span>
                <?= $this->Form->control('event_holidays.' . $eventHolidayIndex . '.time_to', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['js-timepicker'],
                    'id' => 'event_holidays.' . $eventHolidayIndex . '.time_to',
                ]) ?>
                <span class="txt mgl-10">の時間</span>
            </dd>
        </dl>
        <dl class="groupInput-detail-in">
            <dt>適用除外日</dt>
            <dd class="holidayEx-wrap">
                <div class="holidayEx js_event_holiday_exclude_dates_container_<?= h($eventHolidayIndex) ?>">
                    <?php if (isset($eventHolidayData->event_holiday_exclude_dates)): ?>
                        <?php foreach ($eventHolidayData->event_holiday_exclude_dates as $excludeDatesIndex => $excludeDatesData): ?>
                            <?= $this->element('Admin/EventHolidays/fieldset_exclude_dates', [
                                'eventHoliday' => $eventHolidayData,
                                'excludeDatesIndex' => $excludeDatesIndex,
                                'excludeDatesData' => $excludeDatesData,
                                'eventHolidayIndex' => $eventHolidayIndex,
                            ]) ?>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?= $this->Form->error('event_holidays.' . $eventHolidayIndex . '.event_holiday_exclude_dates'); ?>
                <?= $this->Form->button('適用除外日追加', [
                    'type' => 'button',
                    'class' => ['js_add_input', 'cmn-btn', 'is-addCell', ' mgt-20', ' holidayExAddBtn'],
                    'data-container' => '.js_event_holiday_exclude_dates_container_' . $eventHolidayIndex,
                    'data-html' => $this->element('Admin/EventHolidays/fieldset_exclude_dates', [
                        'eventHoliday' => $eventHoliday,
                        'excludeDatesIndex' => '%INDEX2%',
                        'excludeDatesData' => null,
                        'eventHolidayIndex' => $eventHolidayIndex,
                    ]),
                    'data-index-element' => '.js_event_holiday_exclude_dates_index',
                    'data-index-replace' => '%INDEX2%',
                ]) ?>
            </dd>
        </dl>
    </dd>
</dl>
