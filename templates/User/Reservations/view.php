<?php
$this->assign('title', $this->Tr->t('pageTitle/reservationsDetail'));

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsHistory'),
    ['prefix' => 'User', 'controller' => 'Reservations', 'action' => 'history']
);

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsDetail')
);
?>
<section class="contents-area l-main">
    <?= $this->Flash->render('reservationsFinish') ?>
    <?= $this->Flash->render('reservationsError') ?>
    <?= $this->element('User/Reservations/detail', [
        'reservationForm' => $reservationForm,
        'valueOptions' => $valueOptions,
        'mode' => 'detail',
    ]) ?>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?php if ($this->Authority->isAuthority(true, 'Reservations', 'history')): ?>
            <?= $this->Form->button($this->Tr->t('reservation/detail/backHistory'), [
                'type' => 'button',
                'class' => ['cmn-btn', 'is-gray', 'js_change_url'],
                'data-url' => $this->Url->build([
                    'prefix' => 'User',
                    'controller' => 'Reservations',
                    'action' => 'history',
                    '?' => $this->Configure->read('Setting.searchInput.searchQuery')
                ], ['escape' => false]),
            ]) ?>
        <?php endif; ?>
        <?php if ($reservationForm->getReservationEntity()->canEdit()
            && $this->Authority->isAuthority(true, 'Reservations', 'edit')): ?>
            <?= $this->Form->button($this->Tr->t('reservation/detail/editBtn'), [
                'class' => ['cmn-btn', 'is-blue', 'js_change_url'],
                'type' => 'button',
                'data-url' => $this->Url->build([
                    'prefix' => 'User',
                    'controller' => 'Reservations',
                    'action' => 'edit',
                    'id' => $reservationForm->getReservationEntity()->get('id'),
                ], ['escape' => false]),
            ]) ?>
        <?php endif; ?>
        <?php if ($reservationForm->getReservationEntity()->canCancel()
            && $this->Authority->isAuthority(true, 'Reservations', 'cancel')): ?>
            <?= $this->Form->button($this->Tr->t('reservation/detail/cancelBtn'), [
                'type' => 'button',
                'class' => ['cmn-btn', 'is-red', 'js_post_confirm', 'is-circle'],
                'data-confirm-message' => $this->Tr->t('reservation/cancelDialog/message'),
                'data-confirm-title' => $this->Tr->t('reservation/cancelDialog/title'),
                'data-confirm-html' => '',
                'data-url' => $this->Url->build([
                    'prefix' => 'User',
                    'controller' => 'Reservations',
                    'action' => 'cancel',
                    'id' => $reservationForm->getReservationEntity()->get('id'),
                ], ['escape' => false]),
            ]) ?>
        <?php endif; ?>
    </p>
</section>
