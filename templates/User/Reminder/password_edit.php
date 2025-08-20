<?php
$this->assign('title', $this->Tr->t('pageTitle/pwReminderEdit'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/pwReminderEdit')
);
?>

<section class="contents-area l-remind">
    <h3 class="ttl-sec"><?= $this->Tr->t('pageTitle/pwReminderEdit') ?></h3>
    <?= $this->Form->create($passwordForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'Reminder',
            'action' => 'passwordEdit',
        ],
        'idPrefix' => 'pw-reminder',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <?= $this->Flash->render('reminderPwEditErrors') ?>
    <fieldset class="input-remind mgb-40">
        <p class="mgb-10"><?= $this->Tr->h('reminder/pwEdit/1') ?></p>
        <dl>
            <dt><?= $this->Tr->h('reminder/input/loginId') ?></dt>
            <dd>
                <?= $this->Form->control('login_id', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['textbox_w300'],
                ]) ?>
            </dd>
        </dl>
        <dl>
            <dt><?= $this->Tr->h('reminder/input/password') ?></dt>
            <dd>
                <?= $this->Form->control('password', [
                    'type' => 'password',
                    'label' => false,
                    'class' => ['textbox_w300'],
                ]) ?>
            </dd>
        </dl>
        <dl class="mgb-40">
            <dt><?= $this->Tr->h('reminder/input/passwordConfirm') ?></dt>
            <dd>
                <?= $this->Form->control('password_confirm', [
                    'type' => 'password',
                    'label' => false,
                    'class' => ['textbox_w300'],
                ]) ?>
            </dd>
        </dl>
        <div class="btn-wrap tac">
            <?= $this->Form->button($this->Tr->t('reminder/pwEdit/submitBtn'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue']
            ]) ?>
        </div>
    </fieldset>
    <?= $this->Form->end() ?>
</section>

