<?php $this->assign('ajax_html', null); ?>
<?php if (!$finish): ?>
    <?php $this->Form->create($loginForm); ?>
        <?php $this->start('ajax_fieldset_html'); ?>
            <?= $this->element('User/Auth/login') ?>
        <?php $this->end('ajax_fieldset_html'); ?>
    <?php $this->Form->end(); ?>
    <?php $this->start('ajax_error_html'); ?>
        <?= $this->Flash->render('authError') ?>
    <?php $this->end('ajax_error_html'); ?>
<?php endif; ?>
<?= $this->Ajax->json([
    'finish' => $finish,
    'fieldsetHtml' => $this->fetch('ajax_fieldset_html'),
    'errorHtml' => $this->fetch('ajax_error_html'),
]) ?>
