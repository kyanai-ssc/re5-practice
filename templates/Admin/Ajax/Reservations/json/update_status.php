<?php $this->assign('ajax_html', null); ?>
<?php if (!$finish): ?>
    <?php $this->start('ajax_html'); ?>
    <?= $this->Form->create($updateStatusForm, [
        'type' => 'post',
        'url' => '.',
        'idPrefix' => 'reservations-update-status',
        'novalidate' => true,
        'class' => ['js_submit_once', 'js_no_submit', 'js_update_status_form'],
        'data-reservation-id' => $updateStatusForm->getEntity()->id,
        'data-reservation-status-id' => $updateStatusForm->getEntity()->reservation_status_id,
        'data-confirm-message' => 'ステータスを変更してよろしいですか？',
        'data-confirm-title' => '',
        'data-confirm-html' => $this->element('Admin/Common/fieldset/mail_check'),
    ]) ?>
    <?= $this->Template->radio('reservation_status_id', [
        'type' => 'radio',
        'options' => $valueOptions['reservation_status_id'],
        'class' => ['js_reservation_status_id'],
        'idPrefix' => 'reservations-update-status',
    ]) ?>
    <?= $this->Form->error('mail_check') ?>
    <?= $this->Form->end() ?>
    <?php $this->end('ajax_html'); ?>
<?php endif; ?>
<?= $this->Ajax->json([
    'finish' => $finish,
    'html' => $this->fetch('ajax_html'),
    'statusData' => $statusData,
    'optionalMessages' => $optionalMessages,
]) ?>
