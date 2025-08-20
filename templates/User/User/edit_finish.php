<?php
$this->assign('title', $this->Tr->t('pageTitle/userEdit'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userEdit')
);
?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'userEdit/stepTitle',
    'step1' => 'userEdit/step1',
    'step2' => 'userEdit/step2',
    'step3' => 'userEdit/step3',
    'stepNum' => 3,
    'currentStep' => 3,
]); ?>
<section class="contents-area l-main">
    <p class="cmn-txt mgt-20"><?= $this->Tr->nl2br('userEdit/finishMessage') ?></p>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Template->userTopBtn() ?>
    </p>
</section>
