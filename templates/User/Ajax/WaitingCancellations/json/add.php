<?php $this->start('ajax_html'); ?>
    <?php if (!$finish): ?>
        <div data-title="<?= $this->Tr->h('pageTitle/waitingCancellation/add') ?>">
            <?= $this->Form->create($waitingCancellationForm, [
                'type' => 'post',
                'url' => '.',
                'idPrefix' => 'waiting-cancellations-add',
                'novalidate' => true,
                'class' => ['js_submit_once', 'js_no_submit', 'js_waiting_cancellation_form'],
            ]); ?>
                <?= $this->element('User/WaitingCancellations/fieldset', [
                    'waitingCancellationForm' => $waitingCancellationForm,
                ]) ?>
            <?= $this->Form->end(); ?>
        </div>
    <?php else: ?>
        <div>
            <?= $this->Tr->h('waitingCancellation/finishMessage'); ?>
        </div>
    <?php endif; ?>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'finish' => $finish,
    'html' => $this->fetch('ajax_html'),
]) ?>
