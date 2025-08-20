<?php
$this->assign('title', $this->Tr->t('pageTitle/reservationsEdit'));

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/guestLogin'),
    ['prefix' => 'User', 'controller' => 'Guest', 'action' => 'login']
);

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/guestCode')
);

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsEdit')
);
?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'reservationsEdit/stepTitle',
    'step1' => 'reservationsEdit/step1',
    'step2' => 'reservationsEdit/step2',
    'step3' => 'reservationsEdit/step3',
    'stepNum' => 3,
    'currentStep' => 3,
]); ?>
<section class="contents-area l-main">
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('reservationsEdit/finishMessage') ?></p>
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
