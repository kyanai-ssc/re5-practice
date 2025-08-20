<?php
$this->assign('title', 'パスワードリセット完了');
$this->assign('loginClass', true);
?>
<section class="form-login">
    <aside class="cmn-msg is-comp">
        <p>
            <svg class="icon is-msg">
                <use xlink:href="#icon_check"></use>
            </svg>
            管理者パスワードを初期パスワードへリセットしました。<br>
            ログイン後、パスワードの再設定を行ってご利用ください。<br>
        </p>
    </aside>
    <div class="btn-box mgt-20 tac">
        <?= $this->Html->link('ログイン画面に戻る', [
            'prefix' => 'Admin',
            'controller' => 'Auth',
            'action' => 'login',
        ], [
            'class' => ['cmn-btn', 'is-reset', 'is-gray'],
        ]) ?>
    </div>
</section>
