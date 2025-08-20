<?php
$this->assign('title', $this->Tr->t('pageTitle/userWithdraw'));

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userDetail'),
    ['prefix' => 'User', 'controller' => 'User', 'action' => 'detail', 'id' => $user->id,]
);
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userWithdraw')
);
?>

<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/userWithdraw') ?></h3>
    <p class="cmn-txt fwb"><?= $this->Tr->nl2br('user/withdraw/message') ?></p>
    <?= $this->Flash->render('withdrawErrors') ?>
    <?= $this->Form->create(null, [
        'type' => 'post',
        'class' => ['js_submit_confirm'],
        'url' => [
            'prefix' => 'User',
            'controller' => 'User',
            'action' => 'withdraw',
            'id' => $user->id,
        ],
        'idPrefix' => 'withdraw',
        'novalidate' => true,
        'data-confirm-message' => $this->Tr->t('user/withdraw/dialogMessage'),
        'data-confirm-title' => $this->Tr->t('user/withdraw/dialogTitle'),
    ]) ?>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Html->link(
            $this->Tr->t('user/withdraw/backBtn'),
            [
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'detail',
                'id' => $user->id,
            ],
            ['class' => ['cmn-btn', 'is-gray']]
        ); ?>

        <?= $this->Form->button($this->Tr->t('user/withdraw/withdrawBtn'), [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue']
        ]) ?>
    </p>
    <?= $this->Form->end(); ?>
</section>
