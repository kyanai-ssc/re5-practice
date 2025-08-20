<?php $this->assign('ajax_html', null); ?>
<?php if (!$finish): ?>
    <?php $this->start('ajax_html'); ?>
        <div class="popup-content">
            <?= $this->Form->create($updateAuthorityForm, [
                'type' => 'post',
                'url' => '.',
                'idPrefix' => 'users-update-authority',
                'novalidate' => true,
                'class' => ['js_submit_once', 'js_no_submit', 'js_update_authority_form'],
                'data-user-id' => $updateAuthorityForm->getEntity()->get('id'),
                'data-user-authority-id' => $updateAuthorityForm->getEntity()->get('user_authority_id'),
                'data-confirm-message' => '権限を変更してよろしいですか？',
                'data-confirm-title' => '',
            ]) ?>
            <?= $this->Template->radio('user_authority_id', [
                'type' => 'radio',
                'label' => false,
                'options' => $valueOptions['user_authority_id'],
                'class' => ['js_user_authority_id', 'cmn-radio'],
            ]) ?>
            <?= $this->Form->end() ?>
        </div>
    <?php $this->end('ajax_html'); ?>
<?php endif; ?>
<?= $this->Ajax->json([
    'finish' => $finish,
    'html' => $this->fetch('ajax_html'),
]) ?>
