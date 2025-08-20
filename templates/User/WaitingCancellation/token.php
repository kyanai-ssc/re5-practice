<?php
    $this->assign('title', $this->Tr->t('pageTitle/waitingCancellation/Token'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/waitingCancellation/Token')
);
?>

<section class="contents-area l-remind">
    <h3 class="ttl-sec"><?= $this->Tr->t('pageTitle/waitingCancellation/Token') ?></h3>
    <?= $this->Form->create($waiting, [
        'type' => 'post',
        'url' => [
            'controller' => 'WaitingCancellation',
            'action' => 'token',
        ],
        'idPrefix' => 'waitingCancellation-token',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <?= $this->Flash->render('waitingCancellationErrors') ?>
    <fieldset class="input-remind mgb-40">
        <p class="mgt-10"><?= $this->Tr->nl2br('waitingCancellation/token/1') ?></p>
        <dl class="mgb-40">
            <dt><?= $this->Tr->h('waitingCancellation/mail') ?></dt>
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
            <?= $this->Form->button($this->Tr->t('waitingCancellation/submitBtn'), [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue']
            ]) ?>
        </div>
    </fieldset>
    <?= $this->Form->end() ?>
</section>

