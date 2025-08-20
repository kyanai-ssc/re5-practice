<?php
$this->assign('title', 'Akerun連携完了');
$this->assign('loginClass', true);
?>
<section class="form-login">
    <aside class="cmn-msg is-comp">
        <p>
            <svg class="icon is-msg">
                <use xlink:href="#icon_check"></use>
            </svg>
            Akerun（アケルン）との連携が完了しました
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
