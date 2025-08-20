<?php
$this->assign('title', 'パスワードリセット');
$this->assign('loginClass', true);
?>
<section class="form-login">
    <aside class="cmn-msg is-caution">
        <p>
            管理者パスワードをリセットします。<br>
            システム提供時に設定したマスター管理者のみリセットできます。<br>
            ※他管理者は、マスター管理者にリセットを依頼ください。<br>
        </p>
    </aside>
    <?= $this->Form->create($passResetForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'PassReset',
            'action' => 'certification',
        ],
        'idPrefix' => 'adminPassResetTokens',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <dl class="loginInput">
        <dt>管理者ログインID</dt>
        <dd>
            <?= $this->Form->control('login_id', [
                'type' => 'text',
                'label' => false,
            ]) ?>
            <div class="desc-wrap">
                <p>登録されている管理者メールアドレス宛にメールを送ります。</p>
                <p>※管理者メールアドレスが複数設定されている場合、1番目に登録されている管理者メールアドレスにメールが届きます。</p>
            </div>
        </dd>
    </dl>
    <div class="btn-box mgt-20 tac">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Auth',
            'action' => 'login',
        ], [
            'class' => ['cmn-btn', 'is-reset', 'is-gray'],
        ]) ?>
        <?= $this->Form->button('送信', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue', 'pdl-50', 'pdr-50']
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
