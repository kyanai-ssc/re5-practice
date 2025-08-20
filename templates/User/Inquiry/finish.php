<?php
$this->assign('title', $this->Tr->t('pageTitle/inquiry'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/inquiry')
);
?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'inquiry/stepTitle',
    'step1' => 'inquiry/step1',
    'step2' => 'inquiry/step2',
    'step3' => 'inquiry/step3',
    'stepNum' => 3,
    'currentStep' => 3,
]); ?>
<section class="contents-area l-main">
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('inquiry/finishMessage') ?></p>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Template->userTopBtn() ?>
    </p>
</section>
