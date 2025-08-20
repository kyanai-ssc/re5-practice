<?php
use \App\Model\Entity\AnalysisTag;

$this->assign('title', $this->Tr->t('pageTitle/reservationsAdd'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/calendar'),
    ['prefix' => 'User', 'controller' => 'Reservations', 'action' => 'calendar']
);

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsAdd')
);

$this->assign('analysisTag', $this->Setting->getAnalysisTagSetting(AnalysisTag::TYPE_RESERVATION_FINISH));
?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'reservationsAdd/stepTitle',
    'step1' => 'reservationsAdd/step1',
    'step2' => 'reservationsAdd/step2',
    'step3' => 'reservationsAdd/step3',
    'stepNum' => 3,
    'currentStep' => 3,
]); ?>
<section class="contents-area l-main">
    <?= $this->Flash->render('reservationsFinish') ?>
    <?= $this->Flash->render('reservationsError') ?>
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('reservationsAdd/finishMessage') ?></p>
    <p class="idBox">
        <?= $this->Tr->h('reservation/reservationId') ?>：<span class="idNum"><?= h(implode('、', $reservationIds)) ?></span>
    </p>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?php if ($remainReservations): ?>
            <?= $this->Html->link(
                $this->Tr->t('common/reserveContinue'), 
                '#',
                [
                    'class' => ['cmn-btn', 'is-blue', 'js_change_url'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'User',
                        'controller' => 'Reservations',
                        'action' => 'add-conf',
                    ], ['escape' => false]),
                ]
            ) ?>
        <?php endif; ?>
        <?= $this->Template->userTopBtn() ?>
    </p>
</section>
