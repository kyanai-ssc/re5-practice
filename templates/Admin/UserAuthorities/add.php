<?php
$this->assign('title', '顧客の権限設定 新規登録');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '顧客の権限設定',
    ['controller' => 'UserAuthorities', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '新規登録'
);
?>
<section class="form-input">
    <?= $this->Flash->render('userAuthoritiesErrors') ?>

    <?= $this->Form->create($userAuthority, [
        'type' => 'post',
        'url' => [
            'controller' => 'UserAuthorities',
            'action' => 'add',
        ],
        'idPrefix' => 'userAuthorities-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '顧客の権限設定の登録',
        'data-confirm-message' => '顧客の権限設定の登録をおこなってよろしいですか？',
    ]) ?>

    <?= $this->Flash->render('userAuthoritiesErrors') ?>
    <?= $this->element('Admin/UserAuthorities/fieldset', [
        'userAuthority' => $userAuthority,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'UserAuthorities',
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
