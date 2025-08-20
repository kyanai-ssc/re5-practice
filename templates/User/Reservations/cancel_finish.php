<?php
$this->assign('title', $this->Tr->t('pageTitle/reservationsCancelFinish'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsCancelFinish')
);
?>
<section class="contents-area l-main">
    <?= $this->Flash->render('reservationsFinish') ?>
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('reservationsCancelFinish/finishMessage') ?></p>
    <p class="idBox">
        <?= $this->Tr->h('reservation/reservationId') ?>：<span class="idNum"><?= h($reservationId) ?></span>
    </p>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Template->userTopBtn() ?>
    </p>
</section>
