<?php
$this->assign('title', $this->Tr->t('pageTitle/waitingCancellation/add'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/waitingCancellation/add')
);
$this->assign('noNavi', true);
?>
<section class="contents-area l-main">
    <?= $this->Form->create($waitingCancellationForm, [
        'type' => 'post',
        'url' => [
            'prefix' => 'User',
            'controller' => 'WaitingCancellation',
            'action' => 'add',
            '?' => $waitingCancellationForm->getParameter(),
        ],
        'idPrefix' => 'waiting-cancellation-add',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]); ?>
        <?= $this->element('User/WaitingCancellations/fieldset', [
            'waitingCancellationForm' => $waitingCancellationForm,
        ]) ?>
    <?= $this->Form->end(); ?>
</section>
