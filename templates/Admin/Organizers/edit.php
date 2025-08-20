<?php
$this->assign('title', '主催者 編集');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('主催者設定', [
    'prefix' => 'Admin',
    'controller' => 'Organizers',
    'action' => 'list',
]);
$this->Breadcrumbs->add('編集');
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
            'action' => 'edit',
            'id' => $organizer->get('id'),
        ],
        'idPrefix' => 'organizers-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '主催者の編集',
        'data-confirm-message' => '主催者の編集をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/Organizers/fieldset', [
        'organizer' => $organizer,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
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
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
