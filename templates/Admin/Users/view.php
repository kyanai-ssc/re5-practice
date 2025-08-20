<?php
$this->assign('title', '会員 詳細');
$this->assign('headerType', 'data');
$this->Breadcrumbs->add(
    '顧客一覧',
    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '詳細'
);
?>
<section class="form-input">
    <?= $this->element('Admin/Users/detail', [
        'userForm' => $userForm,
        'valueOptions' => $valueOptions,
        'mode' => 'detail',
    ]) ?>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery')
        ], ['class' => ['cmn-btn', 'is-reset', 'is-gray']]) ?>
        <?php if ($userForm->getUserEntity()->canEdit()): ?>
            <?= $this->Html->link('編集', [
                'prefix' => 'Admin',
                'controller' => 'Users',
                'action' => 'edit',
                'id' => $userForm->getUserEntity()->get('id'),
            ], ['class' => ['cmn-btn', 'is-blue']]) ?>
        <?php endif; ?>
        <?php if ($userForm->getUserEntity()->canWithdraw()): ?>
            <?php $this->start('user_withdraw_dialog'); ?>
            <?= $this->Form->create(null, [
                'type' => 'post',
                'url' => [
                    'prefix' => 'Admin',
                    'controller' => 'Users',
                    'action' => 'withdraw',
                    'id' => $userForm->getUserEntity()->get('id'),
                ],
                'idPrefix' => 'reservations-cancel',
                'novalidate' => true,
                'class' => ['js_user_withdraw_form'],
            ]) ?>
            <?= $this->element('Admin/Common/fieldset/mail_check') ?>
            <?= $this->Form->end() ?>
            <?php $this->end('user_withdraw_dialog'); ?>
            <?= $this->Form->button('退会', [
                'class' => ['js_post_confirm', 'cmn-btn', 'is-pink', 'is-circle'],
                'data-confirm-message' => '会員の退会をおこなってよろしいですか？',
                'data-confirm-title' => '会員の退会',
                'data-confirm-html' => $this->fetch('user_withdraw_dialog'),
                'data-confirm-form' => '.js_user_withdraw_form',
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Users',
                    'action' => 'withdraw',
                    'id' => $userForm->getUserEntity()->get('id'),
                ], ['escape' => false]),
            ]) ?>
        <?php endif; ?>
        <?php if ($userForm->getUserEntity()->canDelete()): ?>
            <?= $this->Form->button('削除', [
                'class' => ['js_post_confirm', 'cmn-btn', 'is-pink', 'is-circle'],
                'data-confirm-message' => '会員の削除をおこなってよろしいですか？',
                'data-confirm-title' => '会員の削除',
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Users',
                    'action' => 'delete',
                    'id' => $userForm->getUserEntity()->get('id'),
                ], ['escape' => false]),
            ]) ?>
        <?php endif; ?>
    </div>
</section>

