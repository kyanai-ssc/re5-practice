<?php
$this->assign('title', $this->Tr->t('pageTitle/guestLogin'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/guestLogin')
);
?>

<section class="contents-area l-login">
    <?= $this->Form->create($loginForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'Guest',
            'action' => 'login',
        ],
        'idPrefix' => 'guest-login',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <?= $this->Flash->render('guestLoginErrors') ?>
    <fieldset class="input-password mgb-40 mgt-20">
        <p class="cmn-txt fwb mb20"><?= $this->Tr->nl2br('guest/login/message') ?></p>
        <dl class="cmn-dl">
            <dt><?= $this->Tr->h('guest/reservationId') ?></dt>
            <dd>
                <?= $this->Form->control('reservation_id', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['textbox_w300'],
                ]) ?>
            </dd>
        </dl>
        <dl class="cmn-dl mgb-40">
            <dt><?= $this->Tr->h('guest/mail') ?></dt>
            <dd>
                <?= $this->Form->control('mail', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['textbox_w300'],
                ]) ?>
            </dd>
        </dl>
        <div class="btn-wrap tac">
            <?= $this->Template->userTopBtn(true) ?>
            <?= $this->Form->button($this->Tr->t('guest/loginBtn'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue']
            ]) ?>
        </div>
    </fieldset>
    <?= $this->Form->end() ?>
</section>

