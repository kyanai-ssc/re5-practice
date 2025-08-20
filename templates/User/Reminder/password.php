<?php
$this->assign('title', $this->Tr->t('pageTitle/pwReminder'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/pwReminder')
);
?>

<section class="contents-area l-remind">
    <h3 class="ttl-sec"><?= $this->Tr->t('pageTitle/pwReminder') ?></h3>
    <?= $this->Form->create($reminder, [
        'type' => 'post',
        'url' => [
            'controller' => 'Reminder',
            'action' => 'password',
        ],
        'idPrefix' => 'pw-reminder',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <?= $this->Flash->render('flash') ?>
    <fieldset class="input-remind mgb-40">
        <p class="mgb-10"><?= $this->Tr->nl2br('reminder/pw/1') ?></p>
        <dl class="mgb-40">
            <dt><?= $this->Tr->h('reminder/mail') ?></dt>
            <dd>
                <?= $this->Form->control('mail', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['textbox_w300'],
                ]) ?>
            </dd>
        </dl>
        <div class="btn-wrap tac">
            <?= $this->Html->link(
                $this->Tr->t('common/backBtn'),
                [
                    'prefix' => 'User',
                    'controller' => 'Auth',
                    'action' => 'login'
                ],
                ['class' => ['cmn-btn', 'is-gray', 'is-reset']]
            ); ?>
            <?= $this->Form->button($this->Tr->t('reminder/submitBtn'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue']
            ]) ?>
        </div>
    </fieldset>
    <?= $this->Form->end() ?>
</section>

