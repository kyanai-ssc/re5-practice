<?php

use App\Model\Entity\SiteSetting;

$this->assign('title', $this->Tr->t('pageTitle/login'));
$this->assign('loginClass', true);
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/login')
);
?>

<section class="contents-area l-login">
    <?= $this->Form->create($loginForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'Auth',
            'action' => 'login',
            '?' => [
                'redirect' => $this->getRequest()->getQuery('redirect'),
            ],
        ],
        'idPrefix' => 'auth-login',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/login') ?></h3>
    <?= $this->Flash->render('authErrors') ?>
    <fieldset class="input-password mgb-40 mgt-20">
        <?= $this->element('User/Auth/login') ?>
        <div class="btn-wrap tac">
            <?php if ($this->Setting->getSiteSetting()->login_required_flg !== SiteSetting::COMMON_USE_FLG_ON) : ?>
                <?= $this->Template->userTopBtn(true) ?>
            <?php endif; ?>
            <?= $this->Form->button($this->Tr->t('login/loginBtn'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue']
            ]) ?>
        </div>
    </fieldset>
    <div class="link-box">
        <?php if ($this->Setting->getSiteSetting()->id_reminder_flg === SiteSetting::COMMON_USE_FLG_ON
            && $this->Authority->isAuthority(true, 'Reminder', 'loginId')): ?>
            <p class="mgt-30">
                <?= $this->Html->link($this->Tr->t('login/idReminder'), [
                    'prefix' => 'User',
                    'controller' => 'Reminder',
                    'action' => 'loginId',
                ], [
                    'class' => ['link-txt'],
                ]) ?>
            </p>
        <?php endif; ?>
        <?php if ($this->Setting->getSiteSetting()->password_reminder_flg === SiteSetting::COMMON_USE_FLG_ON
            && $this->Authority->isAuthority(true, 'Reminder', 'password')): ?>
            <p class="mgt-10">
                <?= $this->Html->link($this->Tr->t('login/pwReminder'), [
                    'prefix' => 'User',
                    'controller' => 'Reminder',
                    'action' => 'password',
                ], [
                    'class' => ['link-txt'],
                ]) ?>
            </p>
        <?php endif; ?>
        <?php if ($this->Setting->getSiteSetting()->user_add_flg === SiteSetting::COMMON_USE_FLG_ON
            && $this->Setting->getSiteSetting()->login_display_add_user_flg === SiteSetting::COMMON_USE_FLG_ON
            && $this->Authority->isAuthority(true, 'User', 'add')): ?>
            <p class="mgt-10">
                <?= $this->Html->link($this->Tr->t('common/userAdd'), [
                    'prefix' => 'User',
                    'controller' => 'User',
                    'action' => 'add',
                ], [
                    'class' => ['link-txt'],
                ]) ?>
            </p>
        <?php endif; ?>

    </div><!-- .link-box -->
    <?= $this->Form->end() ?>
</section>

