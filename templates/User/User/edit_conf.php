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
    'currentStep' => 2,
]); ?>
<section class="contents-area l-main">
    <?= $this->Flash->render('usersError') ?>
    <?= $this->Form->create($userForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'User',
            'action' => 'edit-conf',
            'id' => $userForm->getUserEntity()->get('id'),
        ],
        'idPrefix' => 'user-edit-conf',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
        <?= $this->Token->getTokenTag() ?>
        <?= $this->Token->getTokenError() ?>
        <?= $this->element('User/User/detail', [
            'userForm' => $userForm,
            'valueOptions' => $valueOptions,
            'mode' => 'editConf',
        ]) ?>
        <fieldset class="input-info">
            <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
                <?= $this->Html->link(
                    $this->Tr->t('userEdit/backBtn'),
                    [
                        'prefix' => 'User',
                        'controller' => 'User',
                        'action' => 'edit',
                        'id' => $userForm->getUserEntity()->get('id'),
                        '?' => $this->Configure->read('Setting.formInput.backQuery'),
                    ],
                    [
                        'class' => ['cmn-btn', 'is-gray'],
                    ]
                ); ?>
                <?= $this->Form->button($this->Tr->t('userEdit/finishBtn'), [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue']
                ]) ?>
            </p>
        </fieldset>
    <?= $this->Form->end() ?>
</section>
