<?php
$this->assign('title', $this->Tr->t('pageTitle/reservationsCancelFinish'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsCancelFinish')
);
?>
<section class="contents-area l-main">
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('reservationsCancelFinish/finishMessage') ?></p>
    <p class="idBox">
        <?= $this->Tr->h('reservation/reservationId') ?>：<span class="idNum"><?= h($reservationId) ?></span>
    </p>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Html->link(
            $this->Tr->t('common/backBtn'),
            [
                'prefix' => 'User',
                'controller' => 'Guest',
                'action' => 'reservationDetail',
                'id' => $reservationId
            ],
            ['class' => ['cmn-btn', 'is-gray']]
        ); ?>
        <?= $this->Template->userTopBtn() ?>
    </p>
</section>
