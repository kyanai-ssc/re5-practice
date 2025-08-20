<?php

$this->assign('title', $this->Tr->t('pageTitle/reservationsAdd'));

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/calendar'),
    ['prefix' => 'User', 'controller' => 'Reservations', 'action' => 'calendar']
);

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsAdd')
);

$this->Html->script('user/reservations/fieldset', [
    'block' => true,
]);
?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'reservationsAdd/stepTitle',
    'step1' => 'reservationsAdd/step1',
    'step2' => 'reservationsAdd/step2',
    'step3' => 'reservationsAdd/step3',
    'stepNum' => 3,
    'currentStep' => 1,
]); ?>
<section class="contents-area l-main">
    <?= $this->Flash->render('reservationsError') ?>
    <?php if (!$this->CommonData->existsUserLoginData() && $this->Setting->getSiteSetting()->isUseFlgOn('login_flg')): ?>
        <h3 class="ttl-sec mgt-20"><?= $this->Tr->h('reservationsAdd/loginTitle') ?></h3>
        <p class="cmn-txt fwb"><?= $this->Tr->nl2br('reservationsAdd/loginDescription') ?></p>
        <div class="js_login_error">
        </div>
        <?= $this->Form->create(null, [
            'type' => 'post',
            'url' => '.',
            'idPrefix' => 'reservations-login',
            'novalidate' => true,
            'class' => ['js_reservation_login_form'],
        ]) ?>
            <fieldset class="input-password mgb-40 mgt-20">
                <?= $this->element('User/Auth/login') ?>
                <p class="mgt-20">
                    <?= $this->Form->button($this->Tr->t('reservationsAdd/loginBtn'), [
                        'type' => 'submit',
                        'class' => ['cmn-btn', 'is-blue'],
                        'id' => 'loginBtn',
                    ]) ?>
                </p>
            </fieldset>
        <?= $this->Form->end() ?>
    <?php endif; ?>
    <?= $this->element('User/Reservations/form', [
        'reservationForm' => $reservationForm,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>
</section>
