<?php
$this->assign('title', '予約台帳');
$this->assign('headerType', 'data');
$this->Html->script('admin/common/tableBtn_calendar_time', [
    'block' => true,
]);
$this->Html->script('admin/common/ieSticky_type_day', [
    'block' => true,
]);
$this->Html->script('admin/common/ieSticky_type_week', [
    'block' => true,
]);
$this->Html->script('admin/common/ieSticky_month', [
    'block' => true,
]);
$this->Html->script('admin/common/ieSticky_list', [
    'block' => true,
]);
$this->Html->script('admin/reservations/calendar', [
    'block' => true,
]);
$this->Breadcrumbs->add(
    '予約台帳'
);
if ($selectCalendar) {
    $this->assign('noNavi', true);
}
?>
<div class="js_calendar_container" data-search="<?= h(json_encode($calendarForm->getData())) ?>"<?php if ($selectCalendar): ?> data-select-calendar="<?= h($selectCalendar) ?>"<?php endif; ?><?php if (is_scalar($calendarForm->getData('edit_reservation_id'))): ?> data-edit-reservation-id="<?= h($calendarForm->getData('edit_reservation_id')) ?>"<?php endif; ?>>
</div>
