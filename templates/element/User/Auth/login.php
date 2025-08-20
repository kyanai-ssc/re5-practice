<div class="js_login_fieldset">
    <dl>
        <dt>
            <?= $this->Tr->h('login/loginId') ?>
        </dt>
        <dd>
            <?= $this->Form->control('login_id', [
                'type' => 'text',
                'label' => false,
                'class' => ['textbox_w300'],
            ]) ?>
        </dd>
    </dl>
    <dl class="mgb-40">
        <dt>
            <?= $this->Tr->h('login/password') ?>
        </dt>
        <dd>
            <?= $this->Form->control('password', [
                'type' => 'password',
                'label' => false,
                'value' => '',
                'class' => ['textbox_w300'],
            ]) ?>
        </dd>
    </dl>
</div>
