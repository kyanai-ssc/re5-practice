<?php
$this->assign('title', '管理者情報 新規登録');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '管理者情報',
    ['prefix' => 'Admin', 'controller' => 'Admins', 'action' => 'list']
);

$this->Breadcrumbs->add(
    '管理者情報 新規登録'
);
?>
<section class="form-input">
    <?= $this->Flash->render('adminsErrors') ?>

    <?= $this->Form->create($admin, [
        'type' => 'post',
        'url' => [
            'controller' => 'Admins',
            'action' => 'add',
        ],
        'idPrefix' => 'admins-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '管理者の登録',
        'data-confirm-message' => '管理者の登録をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/Admins/fieldset', [
        'admin' => $admin,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Admins',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery')
        ],
            [
                'class' => ['cmn-btn', 'is-reset', 'is-gray'],
            ]) ?>

        <?= $this->Form->button('登録', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
