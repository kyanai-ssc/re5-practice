<?php

use App\Model\Entity\Admin;

$this->Form->unlockField('admin_mails');
?>
<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">担当カテゴリー<?= $this->Template->isRequire('label_id') ?></div>
                </th>
                <td>
                    <?php if ($admin->canEditLabelId()): ?>
                        <?= $this->Label->renderSelect([
                            'type' => $this->Configure->read('Master.label.type.other'),
                            'labelId' => $admin->label_id,
                        ]) ?>
                    <?php else: ?>
                        <div class="cmn-txt">
                            <?= $this->Form->hidden('label_id') ?>
                            <?php if (isset($admin->label)): ?>
                                <?= h($admin->label->name) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">管理者権限<?= $this->Template->isRequire('authority') ?></div>
                </th>
                <td>
                    <?php if ($admin->canEditAuthority() && !$admin->isInitialAdmin()): ?>
                        <?= $this->Template->radio('authority', [
                            'type' => 'radio',
                            'label' => false,
                            'class' => ['cmn-radio'],
                            'options' => $valueOptions['authority'],
                        ]) ?>
                    <?php else: ?>
                        <p class="cmn-txt">
                            <?= h($this->Configure->read('Master.admin.authority.' . $admin->authority)) ?>
                            <?= $this->Form->hidden('authority') ?>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">管理者ログインID<?= $this->Template->isRequire('login_id') ?></div>
                </th>
                <td>
                    <?php if ($admin->canEditLoginId()): ?>
                        <?= $this->Form->control('login_id', [
                            'type' => 'text',
                            'label' => 'ログインID',
                        ]) ?>
                        <div class="desc-wrap">
                            <p>
                                利用可能な文字は半角英数字、ハイフン、ピリオド、アンダースコアです。<br>
                                <?= h($this->Configure->read('Setting.auth.admin.loginId.length.min')) ?>～<?= h($this->Configure->read('Setting.auth.admin.loginId.length.max')) ?>文字で入力してください。
                            </p>
                        </div>
                    <?php else: ?>
                        <p class="cmn-txt">
                            <?= h($admin->login_id) ?>
                            <?= $this->Form->hidden('login_id') ?>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">管理者パスワード
                    <?php if ($mode !== 'edit' || ($mode === 'edit' && $admin->password_reset_flg === Admin::PASSWORD_RESET_FLG_OFF)): ?>
                        <?= $this->Template->isRequire('password') ?>
                    <?php endif; ?>
                    </div>
                </th>
                <td>
                    <?= $this->Form->control('password', [
                        'type' => 'password',
                        'label' => false,
                        'value' => '',
                        'autocomplete' => 'new-password',
                    ]) ?>
                    <div class="desc-wrap">
                        <p>
                            利用可能な文字は半角英数記号です。<br>
                            英数混合、<?= h($this->Configure->read('Setting.auth.admin.password.length.min')) ?>～<?= h($this->Configure->read('Setting.auth.admin.password.length.max')) ?>文字で入力してください。
                        </p>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">管理者パスワード（確認）<?= $this->Template->isRequire('password_confirm') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('password_confirm', [
                        'type' => 'password',
                        'label' => false,
                        'value' => '',
                        'autocomplete' => 'new-password',
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">管理者メールアドレス
                    <?php if (!empty($admin->initial_admin_flg) && $admin->initial_admin_flg === Admin::INITIAL_ADMIN_FLG_ON): ?>
                        <?= $this->element('Admin/Common/form/require') ?>
                    <?php endif; ?>
                    </div>
                </th>
                <td>
                    <div class="pliceInput-box">
                        <?= $this->FormError->errorWithoutNested('admin_mails') ?>

                        <table class="pliceInput-detail" id="pliceTable">
                            <tbody class="js_admin_mails_container">

                            <?php if (isset($admin->admin_mails)): ?>
                                <?php foreach ($admin->admin_mails as $adminMailIndex => $adminMail): ?>
                                    <?= $this->element('Admin/Admins/fieldset_mail', [
                                        'admin' => $admin,
                                        'adminMailIndex' => $adminMailIndex,
                                        'adminMail' => $adminMail,
                                    ]) ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <?= $this->Form->button('管理者メールアドレス追加', [
                            'type' => 'button',
                            'class' => ['js_add_input', 'cmn-btn', 'is-addCell', 'mgt-20'],
                            'data-container' => '.js_admin_mails_container',
                            'data-html' => $this->element('Admin/Admins/fieldset_mail', [
                                'admin' => $admin,
                                'adminMailIndex' => '%INDEX%',
                                'adminMail' => null,
                            ]),
                            'data-index-element' => '.js_admin_mails_index',
                            'data-index-replace' => '%INDEX%',
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">利用許可画面のパターン<?= $this->Template->isRequire('admin_authority_id') ?></div>
                </th>
                <td>
                <?php if ($this->Authority->isAuthority(true, 'AdminAuthorities', 'all')) : ?>
                    <?= $this->Form->control('admin_authority_id', [
                        'type' => 'select',
                        'label' => false,
                        'class' => ['select'],
                        'options' => $valueOptions['adminAuthorityList'],
                    ]) ?>
                <?php else: ?>
                    <?= h($valueOptions['adminAuthorityList'][$admin['admin_authority_id']]) ?>
                <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
