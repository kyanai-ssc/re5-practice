<?php
$this->assign('title', $this->Tr->t('pageTitle/userMailEdit'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userDetail'),
    ['prefix' => 'User', 'controller' => 'User', 'action' => 'detail', 'id' => $user->id,]
);
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userMailEdit')
);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/userMailEdit') ?></h3>
    <p class="cmn-txt fwb"><?= $this->Tr->nl2br('user/mailEdit/message') ?></p>
    <?= $this->Flash->render('mailEditErrors') ?>
    <?= $this->Form->create($optinToken, [
        'type' => 'post',
        'class' => ['js_submit_once'],
        'url' => [
            'prefix' => 'User',
            'controller' => 'User',
            'action' => 'mailEdit',
            'id' => $user->id,
        ],
        'idPrefix' => 'mail-edit',
        'novalidate' => true,
    ]) ?>
    <fieldset class="input-info">
        <table class="input-box mgt-40">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap"><?= $this->Tr->h('user/mailEdit/newMail') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('mail', [
                        'type' => 'text',
                        'class' => ['text_w300'],
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap"><?= $this->Tr->h('user/mailEdit/newMailConf') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('mail_confirm', [
                        'type' => 'text',
                        'class' => ['text_w300'],
                    ]) ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>

    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Html->link(
            $this->Tr->t('user/mailEdit/backBtn'),
            [
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'detail',
                'id' => $user->id,
            ],
            ['class' => ['cmn-btn', 'is-gray', 'is-reset']]
        ); ?>

        <?= $this->Form->button($this->Tr->t('user/mailEdit/nextBtn'), [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue']
        ]) ?>
    </p>
    <?= $this->Form->end(); ?>
</section>
