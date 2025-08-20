<?php $this->Form->unlockField('event_holidays'); ?>
<div class="groupInput">
    <div class="js_event_holidays_container groupInput-box">
        <?php if (isset($eventHoliday)): ?>
            <?php foreach ($eventHoliday as $eventHolidayIndex => $eventHolidayData): ?>
                <?= $this->element('Admin/EventHolidays/fieldset_holidays', [
                    'eventHoliday' => $eventHoliday,
                    'eventHolidayIndex' => $eventHolidayIndex,
                    'eventHolidayData' => $eventHolidayData,
                ]) ?>
            <?php endforeach; ?>
            <?= $this->FormError->errorWithoutNested('event_holidays'); ?>
        <?php endif; ?>
    </div>

    <?= $this->Form->button('休業設定 追加', [
        'type' => 'button',
        'class' => ['js_add_input', 'cmn-btn', 'is-addCell', ' mgt-20', ' holidayAdd'],
        'id' => 'eventHolidaysAdd',
        'data-container' => '.js_event_holidays_container',
        'data-html' => $this->element('Admin/EventHolidays/fieldset_holidays', [
            'eventHoliday' => $eventHoliday,
            'eventHolidayIndex' => '%INDEX%',
            'eventHoliday' => null,
        ]),
        'data-index-element' => '.js_event_holidays_index',
        'data-index-replace' => '%INDEX%',
    ]) ?>
</div>
