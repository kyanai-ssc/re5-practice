<?php
$this->assign('title', $this->Tr->t('pageTitle/userPasswordEdit'));

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userDetail'),
    ['prefix' => 'User', 'controller' => 'User', 'action' => 'detail', 'id' => $user->id,]
);
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/userPasswordEdit')
);
?>

<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/userPasswordEdit') ?></h3>
    <p class="cmn-txt fwb"><?= $this->Tr->nl2br('user/passwordEdit/message') ?></p>
    <?= $this->Flash->render('passwordEditErrors') ?>
    <?= $this->Form->create($passwordForm, [
        'type' => 'post',
        'class' => ['js_submit_once'],
        'url' => [
            'prefix' => 'User',
            'controller' => 'User',
            'action' => 'passwordEdit',
            'id' => $user->id,
        ],
        'idPrefix' => 'password-edit',
        'novalidate' => true,
        'data-confirm-message' => $this->Tr->t('user/passwordEdit/dialogMessage'),
        'data-confirm-title' => $this->Tr->t('user/passwordEdit/dialogTitle'),
    ]) ?>
    <fieldset class="input-info">
        <table class="input-box mgt-40">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap"><?= $this->Tr->h('user/passwordEdit/oldPassword') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('old_password', [
                        'type' => 'password',
                        'class' => ['text_w300'],
                        'value' => '',
                        'autocomplete' => 'new-password',
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap"><?= $this->Tr->h('user/passwordEdit/newPassword') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('password', [
                        'type' => 'password',
                        'class' => ['text_w300'],
                        'value' => '',
                        'autocomplete' => 'new-password',
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap"><?= $this->Tr->h('user/passwordEdit/newPasswordConf') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('password_confirm', [
                        'type' => 'password',
                        'class' => ['text_w300'],
                        'value' => '',
                        'autocomplete' => 'new-password',
                    ]) ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>

    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Html->link(
            $this->Tr->t('user/passwordEdit/backBtn'),
            [
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'detail',
                'id' => $user->id,
            ],
            ['class' => ['cmn-btn', 'is-gray', 'is-reset']]
        ); ?>

        <?= $this->Form->button($this->Tr->t('user/passwordEdit/nextBtn'), [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue']
        ]) ?>
    </p>
    <?= $this->Form->end(); ?>
</section>
