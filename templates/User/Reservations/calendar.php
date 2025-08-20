<?php
use App\Controller\User\ReservationsController;
use App\Locale\Message;

$this->assign('title', $this->Tr->t('pageTitle/calendar'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/calendar')
);
$this->Html->script('user/common/tableBtn', [
    'block' => true,
]);
$this->Html->script('user/common/schedule_common', [
    'block' => true,
]);
$this->Html->script('user/common/ieSticky_day_day', [
    'block' => true,
]);
$this->Html->script('user/common/ieSticky_day_week', [
    'block' => true,
]);
$this->Html->script('user/common/ieSticky_month', [
    'block' => true,
]);
$this->Html->script('user/common/ieSticky', [
    'block' => true,
]);
$this->Html->script('user/common/ieSticky_subject_week', [
    'block' => true,
]);
$this->Html->script('user/common/schedule_list', [
    'block' => true,
]);
$this->Html->script('user/reservations/calendar', [
    'block' => true,
]);
if ($selectCalendar || isset($calendarFrame)) {
    $this->assign('noNavi', true);
}
?>
<div
    class="js_calendar_container"
    data-search="<?= h(json_encode($calendarForm->getData())) ?>"
    <?php if (isset($calendarFrame)): ?>
        data-calendar-frame="<?= h($calendarFrame) ?>"
        <?php if ((string)$calendarFrame === (string)ReservationsController::CALENDAR_FRAME_NEW_WINDOW): ?>
            data-calendar-frame-new-window="<?= h($this->Configure->read('Master.common.flg.on')) ?>"
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($selectCalendar): ?>
        data-select-calendar="<?= h($selectCalendar) ?>"
    <?php endif; ?>
    <?php if (is_scalar($calendarForm->getData('edit_reservation_id'))): ?>
        data-edit-reservation-id="<?= h($calendarForm->getData('edit_reservation_id')) ?>"
    <?php endif; ?>
>
</div>
<div class="hidden">
    <?php if (isset($calendarFrame)): ?>
        <input type="hidden" class="js_waiting_cancellation_url" value="<?= $this->Url->build([
            'prefix' => 'User',
            'controller' => 'WaitingCancellation',
            'action' => 'add',
        ]) ?>"/>
    <?php endif; ?>
    <input type="hidden" class="js_not_available_alert" value="<?= $this->Tr->h(Message::ERROR_CANNOT_RESERVE_TIME) ?>"/>
    <?php $this->start('event_detail_iframe'); ?>
        <iframe class="event_detail_iframe">
        </iframe>
    <?php $this->end('event_detail_iframe'); ?>
    <input type="hidden" class="js_event_detail_iframe" value="<?= h($this->fetch('event_detail_iframe')) ?>"/>
</div>
