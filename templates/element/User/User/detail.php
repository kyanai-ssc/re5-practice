<?php $this->assign('mail_edit_button', null); ?>
<?php $this->assign('password_edit_button', null); ?>
<?php if ($mode === 'detail' && $userForm->getUserEntity()->canEdit() && $this->Authority->isAuthority(true, 'User', 'edit')
    && $this->Setting->getSiteSetting()->isUseFlgOn('mail_edit_optin_flg')
): ?>
    <?php $this->start('mail_edit_button'); ?>
        <div class="cmn-txt">
            <?= $this->Form->button($this->Tr->t('user/detail/mailEditBtn'), [
                'type' => 'button',
                'class' => ['cmn-btn', 'is-blue', 'js_change_url'],
                'data-url' => $this->Url->build([
                    'prefix' => 'User',
                    'controller' => 'User',
                    'action' => 'mailEdit',
                    'id' => $userForm->getUserEntity()->get('id'),
                ], ['escape' => false]),
            ]) ?>
        </div>
    <?php $this->end('password_edit_button'); ?>
<?php endif; ?>
<?php if ($mode === 'detail' && $userForm->getUserEntity()->canEdit() && $this->Authority->isAuthority(true, 'User', 'passwordEdit')): ?>
    <?php $this->start('password_edit_button'); ?>
        <div class="cmn-txt">
            <?= $this->Form->button($this->Tr->t('user/detail/passwordEditBtn'), [
                'type' => 'button',
                'class' => ['cmn-btn', 'is-blue', 'js_change_url'],
                'data-url' => $this->Url->build([
                    'prefix' => 'User',
                    'controller' => 'User',
                    'action' => 'passwordEdit',
                    'id' => $userForm->getUserEntity()->get('id'),
                ], ['escape' => false]),
            ]) ?>
        </div>
    <?php $this->end('password_edit_button'); ?>
<?php endif; ?>
<?= $this->element('User/Common/fieldset/input_items_detail', [
    'formGroups' => $userForm->getUserFormGroups(),
    'options' => [
        'user' => $userForm->getUserEntity(),
        'mode' => $mode,
        'passwordEditButton' => $this->fetch('password_edit_button'),
        'mailEditButton' => $this->fetch('mail_edit_button'),
    ],
]) ?>
