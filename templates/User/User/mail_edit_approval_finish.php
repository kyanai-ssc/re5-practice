<?php
$this->assign('title', $this->Tr->t('pageTitle/userMailEdit/Finish'));

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userDetail'),
    ['prefix' => 'User', 'controller' => 'User', 'action' => 'detail', 'id' => $this->CommonData->getUserLoginData()->get('id'),]
);
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userMailEdit')
);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/userMailEdit/Finish') ?></h3>
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('user/mailEditApproval/message') ?></p>
    <p class="cmn-txt mgt-40 tac mgb-20">
        <?= $this->Template->userTopBtn() ?>
    </p>
</section>
