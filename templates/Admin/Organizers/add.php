<?php
$this->assign('title', '主催者 新規登録');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('主催者設定', [
    'prefix' => 'Admin',
    'controller' => 'Organizers',
    'action' => 'list',
]);
$this->Breadcrumbs->add('新規登録');
$this->Html->script('admin/organizers/fieldset', [
    'block' => true,
]);
?>
<section class="form-input">
    <?= $this->Flash->render('organizersErrors') ?>
    <?= $this->Form->create($organizer, [
        'type' => 'post',
        'url' => [
            'controller' => 'Organizers',
            'action' => 'add',
        ],
        'idPrefix' => 'organizers-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '主催者の登録',
        'data-confirm-message' => '主催者の登録をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/Organizers/fieldset', [
        'organizer' => $organizer,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>
    <div class="btn-box mgt-20">
        <?= $this->Html->link(
            '戻る',
            [
                'prefix' => 'Admin',
                'controller' => 'Organizers',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            [
                'class' => ['cmn-btn', 'is-reset', 'is-gray'],
            ])
        ?>
        <?= $this->Form->button('登録', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
