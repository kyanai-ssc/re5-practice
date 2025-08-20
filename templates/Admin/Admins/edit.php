<?php

use \App\Model\Entity\Admin;

$this->assign('title', '管理者情報 編集');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add(
    '管理者情報',
    ['prefix' => 'Admin', 'controller' => 'Admins', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '編集'
);

?>

<section class="form-input">
    <?= $this->Flash->render('adminsErrors') ?>

    <?php if ($admin->isPassReset() === Admin::LIMIT_TIME_RESET) : ?>
        <?= $this->element('Admin/Common/layout/caution', [
            'caution' => 'パスワードの更新期限が過ぎています。パスワードを更新してください。'
        ]) ?>
    <?php elseif ($admin->isPassReset() === Admin::FIRST_TIME_RESET) : ?>
        <?= $this->element('Admin/Common/layout/caution', [
            'caution' => '管理者パスワードと管理者メールアドレスを設定ください。'
        ]) ?>
    <?php endif; ?>

    <?= $this->Form->create($admin, [
        'type' => 'post',
        'url' => [
            'controller' => 'Admins',
            'action' => 'edit',
            'id' => $admin->get('id'),
        ],
        'idPrefix' => 'admins-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '管理者の編集',
        'data-confirm-message' => '管理者の編集をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/Admins/fieldset', [
        'admin' => $admin,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
    ]) ?>
    <div class="btn-box mgt-20">
        <?php if ($admin->isPassReset() === false) : ?>
            <?= $this->Html->link('戻る', [
                'prefix' => 'Admin',
                'controller' => 'Admins',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
                [
                    'class' => ['cmn-btn', 'is-reset', 'is-gray'],
                ]) ?>
        <?php endif; ?>
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
