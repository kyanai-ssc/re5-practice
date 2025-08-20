<?php
$this->assign('title', '顧客の権限設定 編集');

$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '顧客の権限設定',
    ['prefix' => 'Admin', 'controller' => 'UserAuthorities', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '編集'
);
?>
<section class="form-input">
    <?= $this->Flash->render('userAuthoritiesErrors') ?>
    <?= $this->Form->create($userAuthority, [
        'type' => 'post',
        'url' => [
            'controller' => 'UserAuthorities',
            'action' => 'edit',
            'id' => $userAuthority->get('id'),
        ],
        'idPrefix' => 'userAuthorities-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '顧客の権限設定の編集',
        'data-confirm-message' => '顧客の権限設定の編集をおこなってよろしいですか？',
    ]) ?>

    <?= $this->Flash->render('userAuthoritiesErrors') ?>
    <?= $this->element('Admin/UserAuthorities/fieldset', [
        'userAuthority' => $userAuthority,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
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
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
