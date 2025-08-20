<?php
$this->assign('title', '会員 登録確認');
$this->assign('headerType', 'data');

$this->Breadcrumbs->add(
    '顧客一覧',
    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'list']
);

$this->Breadcrumbs->add(
    '登録内容確認'
);

?>
<section class="form-input">
    <?= $this->Flash->render('usersError') ?>
    <?= $this->Form->create($userForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'Users',
            'action' => 'add-conf',
        ],
        'idPrefix' => 'users-add-conf',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <?= $this->Token->getTokenTag() ?>
    <?= $this->Token->getTokenError() ?>
    <?= $this->element('Admin/Users/detail', [
        'userForm' => $userForm,
        'valueOptions' => $valueOptions,
        'mode' => 'addConf',
    ]) ?>
    <?= $this->element('Admin/Common/fieldset/mail_check') ?>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'add',
            '?' => $this->Configure->read('Setting.formInput.backQuery'),
        ], [
            'class' => ['cmn-btn', 'is-reset', 'is-gray'],
        ]) ?>
        <?= $this->Form->button('登録', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
