<?php

$this->assign('title', '予約 登録完了');
$this->assign('headerType', 'data');

$this->Breadcrumbs->add(
    '予約台帳',
    ['prefix' => 'Admin', 'controller' => 'Reservations', 'action' => 'calendar']
);
$this->Breadcrumbs->add(
    '予約完了'
);
?>
<section class="form-input">
    <?= $this->Flash->render('reservationsFinish') ?>
    <div class="btn-box tac">
        <?= $this->Html->link('予約台帳', [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'calendar',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery') + [
                'user_id' => '',
            ],
        ], ['class' => ['cmn-btn', 'is-blue']]) ?>

        <?= $this->Html->link('予約一覧', [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ], ['class' => ['cmn-btn', 'is-gray']]) ?>

        <?= $this->Html->link('予約詳細', [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'view',
            'id' => $reservationIds[0],
        ], ['class' => ['cmn-btn', 'is-blue']]) ?>
    </div>
</section>
