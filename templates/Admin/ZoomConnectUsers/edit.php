<?php
$this->assign('title', 'Zoom連携ユーザー 編集');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('Zoom連携ユーザー管理', [
    'prefix' => 'Admin',
    'controller' => 'ZoomConnectUsers',
    'action' => 'view',
]);
$this->Breadcrumbs->add('編集');
?>
<section class="form-input">
    <?= $this->Flash->render('zoomConnectUsersErrors') ?>
    <?= $this->Form->create($zoomConnectUser, [
        'type' => 'post',
        'url' => [
            'controller' => 'ZoomConnectUsers',
            'action' => 'edit',
        ],
        'idPrefix' => 'zoom-connect-users-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => 'Zoom連携ユーザーの編集',
        'data-confirm-message' => 'Zoom連携ユーザーの編集をおこなってよろしいですか？',
    ]) ?>
        <?= $this->element('Admin/ZoomConnectUsers/fieldset', [
            'zoomConnectUser' => $zoomConnectUser,
            'mode' => 'edit',
        ]) ?>
        <div class="btn-box mgt-20">
            <?= $this->Html->link(
                '戻る',
                [
                    'prefix' => 'Admin',
                    'controller' => 'ZoomConnectUsers',
                    'action' => 'view',
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
