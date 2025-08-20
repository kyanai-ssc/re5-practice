<?php
$this->assign('title', $this->Tr->t('pageTitle/userMailAddFinish'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userMailAddFinish')
);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/userMailAddFinish') ?></h3>
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('userMailAdd/finishMessage') ?></p>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Template->userTopBtn() ?>
    </p>
</section>
