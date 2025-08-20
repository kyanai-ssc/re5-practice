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
                            <div class="ttl-input-wrap">コマ割り（分数）</div>
                        </th>
                        <td>
                            <?= h($event['event_unit_time']) ?>分
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">予約可能時間選択肢</div>
                        </th>
                        <td>
                            <?php if ((string)$event['time_plan'] === (string)\App\Model\Entity\Event::MULTIPLE_TIME_PLAN_TYPE_SINGLE) : ?>
                                <?= $this->Form->control('previwe_usage_time', [
                                    'type' => 'select',
                                    'options' => $eventEntity->getUsageTimeValueOptions(false),
                                    'class' => 'select'
                                ]) ?>
                            <?php else : ?>
                                複数プラン
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>
    </section>
    <aside class="cmn-msg is-caution">
        <p>
            <svg class="icon is-msg">
                <use xlink:href="#icon_info"></use>
            </svg>
            プレビュー表示のため、日付の更新や表示タイプの変更などは実施できません。
        </p>
    </aside>

    <?= $this->element($calendar->getTemplatePath(), [
        'calendar' => $calendar,
        'valueOptions' => $valueOptions,
        'selectCalendar' => $selectCalendar,
    ]) ?>
<?php else: ?>
    <?= $this->Flash->render('eventsPreviewErrors') ?>

    <?= $this->Form->create($eventForm, [
        'idPrefix' => 'error_display',
        'novalidate' => true,
    ]) ?>
    <table>
        <?php if ($this->Form->isFieldError('time_from')) : ?>
            <tr>
                <td>
                    実施時間（FROM）
                </td>
                <td>
                    <?= $this->Form->error('time_from') ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php if ($this->Form->isFieldError('time_to')) : ?>
            <tr>
                <td>
                    実施時間（TO）
                </td>
                <td>
                    <?= $this->Form->error('time_to') ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php if ($this->Form->isFieldError('event_unit_time')) : ?>
            <tr>
                <td>
                    予約枠コマ割り
                </td>
                <td>
                    <?= $this->Form->error('event_unit_time') ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php if ($this->Form->isFieldError('usage_unit_time')) : ?>
            <tr>
                <td>
                    分単位の予約
                </td>
                <td>
                    <?= $this->Form->error('usage_unit_time') ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php if ($this->Form->isFieldError('usage_time_from')) : ?>
            <tr>
                <td>
                    予約可能範囲（FROM）
                </td>
                <td>
                    <?= $this->Form->error('usage_time_from') ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php if ($this->Form->isFieldError('usage_time_to')) : ?>
            <tr>
                <td>
                    予約可能範囲（TO）
                </td>
                <td>
                    <?= $this->Form->error('usage_time_to') ?>
                </td>
            </tr>
        <?php endif; ?>
    </table>
    <?= $this->Form->end(); ?>
<?php endif; ?>

<?php $this->end('ajax_preview_usage'); ?>

<?php
$this->assign('title', '予約枠時間設定プレビュー');
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

$this->assign('noNavi', true);
?>

<div class="noLoad js_calendar_container">
    <?= $this->fetch('ajax_preview_usage') ?>
</div>
