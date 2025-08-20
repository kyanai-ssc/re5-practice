<?php

use Cake\I18n\FrozenTime;

?>
<span class="input">
<?php if ($adminFlg): ?>
    <?= $this->Form->button('カレンダーから日時を選択', [
        'type' => 'button',
        'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_select_calendar'],
        'data-title' => 'カレンダーから日時を選択'
    ]) ?>
<?php else: ?>
    <?php if ($this->Setting->getSiteSetting()->get('reservation_edit_event_flg') === \App\Model\Entity\SiteSetting::COMMON_USE_FLG_ON) : ?>
        <?= $this->Form->button($this->Tr->t('reservation/selectCalendarBtn'), [
            'type' => 'button',
            'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_select_calendar'],
            'data-title' => $this->Tr->t('reservation/selectCalendarBtn')
        ]) ?>
    <?php endif; ?>
<?php endif; ?>
</span>
<?php $this->start('select_calendar_iframe'); ?>
<?php $usageTimestampFrom = new FrozenTime($reservation->get('usage_timestamp_from')); ?>
<?php if ($adminFlg): ?>
    <iframe class="select_calendar_iframe" width="100%" src="<?= $this->Url->build([
        'prefix' => 'Admin',
        'controller' => 'Reservations',
        'action' => 'calendar',
        '?' => [
            'date' => $usageTimestampFrom->format('Y/m/d'),
            'user_id' => $reservation->get('user_id'),
            'edit_reservation_id' => $reservation->get('id'),
            'select_usage_timestamp_from' => $usageTimestampFrom->format('Y/m/d H:i'),
            'select_calendar' => $this->Configure->readOrFail('Master.common.flg.on'),
        ],
    ]) ?>">
    </iframe>
<?php else: ?>
    <iframe class="select_calendar_iframe" width="100%" src="<?= $this->Url->build([
        'prefix' => 'User',
        'controller' => 'Reservations',
        'action' => 'calendar',
        '?' => [
            'date' => $usageTimestampFrom->format('Y/m/d'),
            'edit_reservation_id' => $reservation->get('id'),
            'select_usage_timestamp_from' => $usageTimestampFrom->format('Y/m/d H:i'),
            'select_calendar' => $this->Configure->readOrFail('Master.common.flg.on'),
        ],
    ]) ?>">
    </iframe>
<?php endif; ?>
<?php $this->end('select_calendar_iframe'); ?>
<input type="hidden" class="js_select_calendar_iframe" value="<?= h($this->fetch('select_calendar_iframe')) ?>"/>
