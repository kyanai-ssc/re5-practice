<?php
$this->assign('ajax_preview_usage', null);
?>
<?php $this->start('ajax_preview_usage'); ?>
<?php if ($checkResult) : ?>
    <section class="form-input">
        <div class="btn-box mgt-20">
            <fieldset>
                <table class="input-box mgt-10">
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">予約単位</div>
                        </th>
                        <td>
                            <?= h($event['usage_unit_time']) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">予約可能時間</div>
                        </th>
                        <td>
                            <?= h($event['usage_time_from']) ?>～<?= h($event['usage_time_to']) ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>
    </section>

    <?= $this->element($calendar->getTemplatePath(), [
        'calendar' => $calendar,
        'valueOptions' => $valueOptions,
        'selectCalendar' => $selectCalendar,
    ]) ?>

<?php else: ?>
    <p>エラーが発生しました</p>
<?php endif; ?>

<?php $this->end('ajax_preview_usage'); ?>

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

<div class="noLoad" data-html=" <?= $this->fetch('ajax_preview_usage') ?>">
</div>
<div class="js_calendar_container"></div>
