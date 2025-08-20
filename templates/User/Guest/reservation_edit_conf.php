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

$this->Html->script('user/common/continue', [
    'block' => true,
]);
$this->Html->script('user/reservations/confirm', [
    'block' => true,
]);
?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'reservationsEdit/stepTitle',
    'step1' => 'reservationsEdit/step1',
    'step2' => 'reservationsEdit/step2',
    'step3' => 'reservationsEdit/step3',
    'stepNum' => 3,
    'currentStep' => 2,
]); ?>
<section class="contents-area l-main">
    <?= $this->Flash->render('reservationsError') ?>
    <?= $this->Form->create($reservationForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'Guest',
            'action' => 'reservationEditConf',
            'id' => $reservationForm->getReservationEntity()->get('id'),
        ],
        'idPrefix' => 'reservations-edit-conf',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
        <?= $this->Token->getTokenTag() ?>
        <?= $this->Token->getTokenError() ?>
        <?= $this->element('User/Reservations/detail', [
            'reservationForm' => $reservationForm,
            'valueOptions' => $valueOptions,
            'mode' => 'editConf',
            'requiredChargeBreakdownAll' => false,
        ]) ?>
        <?= $this->element('User/Reservations/sctl', [
            'reservationForm' => $reservationForm,
        ]) ?>
        <fieldset class="input-info">
            <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
                <?= $this->Html->link(
                    $this->Tr->t('reservationsEdit/backBtn'),
                    [
                        'prefix' => 'User',
                        'controller' => 'Guest',
                        'action' => 'reservationEdit',
                        'id' => $reservationForm->getReservationEntity()->get('id'),
                        '?' => $this->Configure->read('Setting.formInput.backQuery'),
                    ],
                    [
                        'class' => ['cmn-btn', 'is-reset', 'is-gray'],
                    ]
                ) ?>
                <?= $this->Form->button($this->Tr->t('reservationsEdit/finishBtn'), [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue']
                ]) ?>
            </p>
        </fieldset>
    <?= $this->Form->end() ?>
</section>
