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
    'currentStep' => 1,
]); ?>
<section class="contents-area l-main">
    <?= $this->Flash->render('usersError') ?>
    <?= $this->element('User/User/form', [
        'userForm' => $userForm,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
    ]) ?>
</section>
