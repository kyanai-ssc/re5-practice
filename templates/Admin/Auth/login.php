<?php
$this->assign('title', 'ログイン');
$this->assign('loginClass', true);
?>

<section class="form-login">
    <h3 class="ttl-s">ログイン</h3>
    <?= $this->Flash->render('authErrors') ?>
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

    <dl class="loginInput">
        <dt>管理者ログインID</dt>
        <dd>
            <?= $this->Form->control('login_id', [
                'type' => 'text',
                'label' => false,
                'class' => ['textbox_wFull'],
            ]) ?>
        </dd>
        <dt>管理者パスワード</dt>
        <dd>
            <?= $this->Form->control('password', [
                'type' => 'password',
                'label' => false,
                'value' => '',
                'class' => ['textbox_wFull']
            ]) ?>
        </dd>
    </dl>
    <div class="btn-box mgt-20 tac">
        <?= $this->Form->button('ログイン', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue', 'pdl-50', 'pdr-50']
        ]) ?>
    </div>
    <?php if ((string)$this->Configure->read('Env.customerPortal') !== '') : ?>
        <p class="cmn-txt tac mgt-30">
            <a href="<?= h($this->Configure->read('Env.customerPortal')) ?>" target="_blank" class="link-txt is-window">
                カスタマーポータルサイトはこちら
            </a>
        </p>
    <?php endif; ?>
    <p class="cmn-txt tac mgt-10">
        <?= $this->Html->link(
            'パスワードを忘れた方はこちら',
            ['controller' => 'PassReset', 'action' => 'certification'],
            ['class' => 'ink-txt']
        ); ?>
    </p>
    <?= $this->Form->end() ?>
</section>

