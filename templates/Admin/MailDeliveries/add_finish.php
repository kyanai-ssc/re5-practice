<?php
$this->assign('title', 'メール配信登録完了');
$this->assign('headerType', 'data');
$this->Breadcrumbs->add(
    '顧客一覧',
    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'list']
);

$this->Breadcrumbs->add(
    'メール配信登録完了'
);

$this->Html->script('admin/mail-deliveries/fieldset', [
    'block' => true,
]);

?>
<section class="form-input">
    <?= $this->Flash->render('mailDeliveriesFinish') ?>
    <div class="btn-box tac">
        <p><?= $this->Html->link('メール配信履歴', ['controller' => 'MailDeliveries', 'action' => 'list', 'prefix' => 'Admin'], ['class' => ['cmn-btn', 'is-gray']]) ?></p>
    </div>
</section>
