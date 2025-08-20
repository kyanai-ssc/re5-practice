<?php
$this->assign('title', 'パスワードリセット');
$this->assign('loginClass', true);
?>
<section class="form-login">
    <aside class="cmn-msg is-caution">
        <p>
            <svg class="icon is-msg">
                <use xlink:href="#icon_info"></use>
            </svg>
            管理者パスワードを初期パスワードへリセットします。<br>
        </p>
    </aside>
    <?= $this->Form->create($passResetForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'PassReset',
            'action' => 'approval',
        ],
        'idPrefix' => 'adminPassResetTokens-approval',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>

    <div class="btn-box mgt-20 tac">
        <?= $this->Form->button('リセット', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue', 'pdl-50', 'pdr-50']
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
