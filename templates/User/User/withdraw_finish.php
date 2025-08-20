<?php
$this->assign('title', $this->Tr->t('pageTitle/userWithdrawFinish'));

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userDetail')
);

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userWithdrawFinish')
);
?>

<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/userWithdrawFinish') ?></h3>
    <p class="cmn-txt fwb"><?= $this->Tr->nl2br('user/withdrawFinish/message') ?></p>

    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Template->userTopBtn() ?>
    </p>
    <?= $this->Form->end(); ?>
</section>
