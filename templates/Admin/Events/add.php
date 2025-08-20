<?php
$this->assign('title', '予約枠設定 新規登録');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add(
    '予約枠設定',
    ['controller' => 'Events', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '新規登録'
);
?>

<section class="form-input">
    <?= $this->Flash->render('eventsErrors') ?>

    <?= $this->Form->create($event, [
        'type' => 'post',
        'url' => [
            'controller' => 'Events',
            'action' => 'add',
        ],
        'idPrefix' => 'events-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '予約枠の登録',
        'data-confirm-message' => '予約枠の登録をおこなってよろしいですか？',
    ]) ?>

    <?= $this->element('Admin/Events/fieldset', [
        'event' => $event,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>


    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Events',
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
