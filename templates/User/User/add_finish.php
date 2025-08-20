<?php
$this->assign('title', $this->Tr->t('pageTitle/userAdd'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userAdd')
);
?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'userAdd/stepTitle',
    'step1' => 'userAdd/step1',
    'step2' => 'userAdd/step2',
    'step3' => 'userAdd/step3',
    'stepNum' => 3,
    'currentStep' => 3,
]); ?>
<section class="contents-area l-main">
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('userAdd/finishMessage') ?></p>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Template->userTopBtn() ?>
    </p>
</section>
