<?php

use App\Model\Entity\RecaptchaSetting;

$this->assign('title', $this->Tr->t('pageTitle/userAdd'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userAdd')
);

$this->Html->script('common/recaptcha', [
    'block' => true,
]);
?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'userAdd/stepTitle',
    'step1' => 'userAdd/step1',
    'step2' => 'userAdd/step2',
    'step3' => 'userAdd/step3',
    'stepNum' => 3,
    'currentStep' => 2,
]); ?>
<section class="contents-area l-main">
    <?= $this->Flash->render('usersError') ?>
    <?= $this->Form->create($userForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'User',
            'action' => 'add-conf',
        ],
        'idPrefix' => 'user-add-conf',
        'novalidate' => true,
        'class' => array_merge(['js_submit_once'], $this->Recaptcha->getFormClass()),
    ] + $this->Recaptcha->getFormAttribute(RecaptchaSetting::ACTION_USER)) ?>
        <?= $this->Token->getTokenTag() ?>
        <?= $this->Token->getTokenError() ?>
        <?= $this->Recaptcha->getTokenElement() ?>
        <?= $this->element('User/User/detail', [
            'userForm' => $userForm,
            'valueOptions' => $valueOptions,
            'mode' => 'addConf',
        ]) ?>
        <fieldset class="input-info">
            <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
                <?= $this->Html->link(
                    $this->Tr->t('userAdd/backBtn'),
                    [
                        'prefix' => 'User',
                        'controller' => 'User',
                        'action' => 'add',
                        '?' => $this->Configure->read('Setting.formInput.backQuery'),
                    ],
                    [
                        'class' => ['cmn-btn', 'is-gray'],
                    ]
                ); ?>
                <?= $this->Form->button($this->Tr->t('userAdd/finishBtn'), [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue']
                ]) ?>
            </p>
        </fieldset>
    <?= $this->Form->end() ?>
</section>
