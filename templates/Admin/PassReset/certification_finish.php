<?php
$this->assign('title', 'パスワードリセット');
$this->assign('loginClass', true);

use App\Model\Entity\AdminPassResetToken;

?>
<section class="form-login">
    <aside class="cmn-msg is-comp">
        <p>
            <svg class="icon is-msg">
                <use xlink:href="#icon_check"></use>
            </svg>
            パスワードリセットの案内メールを送信しました。<br>
            有効期限は登録から<?= h(AdminPassResetToken::EXPIRATION_ADD_HOUR) ?>時間です。<br>
            メールの内容に従ってパスワードのリセットを行ってください。<br>
        </p>
    </aside>
    <div class="desc-wrap">
        <p>メールが届かない場合、下記が考えられます。</p>
        <p>管理者ログインIDが違う</p>
        <p>他の管理者メールアドレス宛に届いている</p>
        <p>複数マスター管理者がいる場合、システム提供時に設定したマスター管理者は別の人</p>
        <p>別のマスター管理者にパスワード変更を依頼するか、管理者ログインIDとメールアドレスを確認してください。</p>
    </div>
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
