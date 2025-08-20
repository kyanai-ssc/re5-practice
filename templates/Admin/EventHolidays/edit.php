<?php
$this->assign('title', '休業設定 編集');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '休業設定',
    ['prefix' => 'Admin', 'prefix' => 'Admin', 'controller' => 'EventHolidays', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '休業設定 編集'
);
?>
<section class="form-input">
    <?= $this->Flash->render('eventHolidaysErrors') ?>

    <?= $this->Form->create(new ArrayObject(['event_holidays' => $eventHolidays]), [
        'type' => 'post',
        'url' => [
            'controller' => 'EventHolidays',
            'action' => 'edit',
            'id' => $eventData['event_id'],
        ],
        'idPrefix' => 'eventHolidays-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'context' => ['table' => 'EventHolidays'],
        'data-confirm-title' => '休業設定の登録',
        'data-confirm-message' => '休業設定の登録をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/EventHolidays/fieldset', [
        'eventHoliday' => $eventHolidays,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
    ]) ?>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'EventHolidays',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery')
        ], ['class' => ['cmn-btn', 'is-reset', 'is-gray']]) ?>

        <?= $this->Form->button('登録', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
