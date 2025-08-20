<?php
$this->assign('title', $this->Tr->t('pageTitle/userPasswordEdit/Finish'));

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userDetail'),
    ['prefix' => 'User', 'controller' => 'User', 'action' => 'detail', 'id' => $this->CommonData->getUserLoginData()->get('id'),]
);
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userPasswordEdit')
);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/userPasswordEdit/Finish') ?></h3>
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('user/passwordEditFinish/message') ?></p>
    <p class="cmn-txt mgt-40 tac mgb-20">
        <?= $this->Html->link(
            $this->Tr->t('user/passwordEdit/backBtn'),
            [
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'detail',
                'id' => $this->CommonData->getUserLoginData()->get('id'),
            ],
            ['class' => ['cmn-btn', 'is-blue']]
        ); ?>
    </p>
</section>
