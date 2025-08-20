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

$this->Html->script('user/reservations/fieldset', [
    'block' => true,
]);
?>

<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'reservationsEdit/stepTitle',
    'step1' => 'reservationsEdit/step1',
    'step2' => 'reservationsEdit/step2',
    'step3' => 'reservationsEdit/step3',
    'stepNum' => 3,
    'currentStep' => 1,
]); ?>
<section class="contents-area l-main">
    <?= $this->Flash->render('reservationsError') ?>
    <?= $this->element('User/Reservations/form', [
        'reservationForm' => $reservationForm,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
        'formController' => 'Guest',
        'formAction' => 'reservationEdit',
    ]) ?>
</section>
