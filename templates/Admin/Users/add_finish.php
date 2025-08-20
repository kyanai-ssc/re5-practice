<?php
$this->assign('title', '会員 登録完了');
$this->assign('headerType', 'data');

$this->Breadcrumbs->add(
    '顧客一覧',
    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '登録完了'
);
?>
<section class="form-input">
    <?= $this->Flash->render('usersFinish') ?>

    <div class="btn-box tac">
        <?= $this->Html->link('顧客一覧', [
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ],['class' => ['cmn-btn', 'is-gray']]) ?>
    </div>
</section>
