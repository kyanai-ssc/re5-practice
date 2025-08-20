<?php
$this->assign('title', '利用許可画面のパターン設定 新規登録');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '管理者情報',
    ['prefix' => 'Admin', 'controller' => 'Admins', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '利用許可画面のパターン設定',
    ['controller' => 'AdminAuthorities', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '新規登録'
);
?>
<section class="form-input">
    <?= $this->Flash->render('adminAuthoritiesErrors') ?>

    <?= $this->Form->create($adminAuthority, [
        'type' => 'post',
        'url' => [
            'controller' => 'AdminAuthorities',
            'action' => 'add',
        ],
        'idPrefix' => 'adminAuthorities-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '利用許可画面のパターン設定の登録',
        'data-confirm-message' => '利用許可画面のパターン設定の登録をおこなってよろしいですか？',
    ]) ?>

    <?= $this->Flash->render('adminAuthoritiesErrors') ?>
    <?= $this->element('Admin/AdminAuthorities/fieldset', [
        'adminAuthority' => $adminAuthority,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'AdminAuthorities',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ], [
            'class' => ['cmn-btn', 'is-reset', 'is-gray'],
        ]) ?>
        <?= $this->Form->button('登録', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
