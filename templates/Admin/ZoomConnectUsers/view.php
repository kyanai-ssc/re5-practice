<?php
$this->assign('title', 'Zoom連携ユーザー 詳細');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('Zoom連携ユーザー 詳細');
?>
<section class="form-input">
    <?= $this->Flash->render('zoomConnectUsersFinish') ?>
    <?= $this->Flash->render('zoomConnectUsersErrors') ?>
    <?= $this->element('Admin/ZoomConnectUsers/detail', [
        'zoomConnectUser' => $zoomConnectUser,
    ]) ?>
    <div class="btn-box mgt-20">
        <?php if (!isset($zoomConnectUser)): ?>
            <?= $this->Html->link(
                '連携',
                [
                    'prefix' => 'Admin',
                    'controller' => 'ZoomConnectUsers',
                    'action' => 'add',
                ],
                [
                    'class' => ['cmn-btn', 'is-blue'],
                ])
            ?>
        <?php else: ?>
            <?= $this->Html->link(
                '編集',
                [
                    'prefix' => 'Admin',
                    'controller' => 'ZoomConnectUsers',
                    'action' => 'edit',
                ],
                [
                    'class' => ['cmn-btn', 'is-blue'],
                ])
            ?>
            <?= $this->Form->button('連携解除', [
                'class' => ['js_post_confirm', 'cmn-btn', 'is-pink', 'is-circle'],
                'data-confirm-message' => 'Zoom連携ユーザーの削除をおこなってよろしいですか？',
                'data-confirm-title' => 'Zoom連携ユーザーの削除',
                'data-confirm-html' => h('削除したデータの復旧はできません。'),
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'ZoomConnectUsers',
                    'action' => 'delete',
                ], ['escape' => false]),
            ]) ?>
        <?php endif; ?>
    </div>
</section>
