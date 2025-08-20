<?php
$this->assign('title', $this->Tr->t('pageTitle/guestCode'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/guestCode')
);
?>

<section class="contents-area l-login">
    <?= $this->Form->create($codeForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'Guest',
            'action' => 'code',
            'id' => $codeForm->getReId(),
        ],
        'idPrefix' => 'guest-code',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <?= $this->Flash->render('guestLoginErrors') ?>
    <fieldset class="input-password mgb-40 mgt-20">
        <p class="cmn-txt fwb mb20"><?= $this->Tr->nl2br('guest/code/message') ?></p>
        <dl class="cmn-dl">
            <dt><?= $this->Tr->h('guest/reservationId') ?></dt>
            <dd>
                <?= h($codeForm->getReId()) ?>
            </dd>
        </dl>
        <dl class="cmn-dl mgb-40">
            <dt><?= $this->Tr->h('guest/code') ?></dt>
            <dd>
                <?= $this->Form->control('code', [
                    'type' => 'text',
                    'label' => false,
                    'class' => ['textbox_w300'],
                ]) ?>
            </dd>
        </dl>
        <div class="btn-wrap tac">
            <?= $this->Template->userTopBtn(true) ?>
            <?= $this->Form->button($this->Tr->t('guest/codeBtn'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue']
            ]) ?>
        </div>
    </fieldset>
    <?= $this->Form->end() ?>
</section>

