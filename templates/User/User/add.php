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
    'currentStep' => 1,
]); ?>
<section class="contents-area l-main">
    <?= $this->Flash->render('usersError') ?>
    <?= $this->element('User/User/form', [
        'userForm' => $userForm,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>
</section>
