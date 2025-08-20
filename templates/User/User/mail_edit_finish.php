<?php
$this->assign('title', $this->Tr->t('pageTitle/userMailEdit'));

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userDetail'),
    ['prefix' => 'User', 'controller' => 'User', 'action' => 'detail', 'id' => $user->id,]
);
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userMailEdit')
);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/userMailEdit') ?></h3>
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('user/mailEditFinish/message') ?></p>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Html->link(
            $this->Tr->t('user/mailEdit/backBtn'),
            [
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'detail',
                'id' => $user->id,
            ],
            ['class' => ['cmn-btn', 'is-blue']]
        ); ?>
    </p>
</section>
