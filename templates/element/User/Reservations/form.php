<?php
    if (!isset($formController, $formAction)) {
        $formController = 'Reservations';
        $formAction = $mode;
    }
?>
<?= $this->Form->create($reservationForm, [
    'type' => 'post',
    'url' => [
        'prefix' => 'User',
        'controller' => $formController,
        'action' => $formAction,
        'id' => $reservationForm->getReservationEntity()->get('id'),
        '?' => [
            'key' => $reservationForm->getContinuousParameter('key'),
            'user_authority_id' => null,
        ] + $reservationForm->getReservationParameter(),
    ],
    'idPrefix' => 'reservations-' . $mode,
    'novalidate' => true,
    'class' => ['js_submit_once', 'js_reservation_form'],
]) ?>
<?= $this->Token->getTokenError(); ?>
<?= $this->Token->getTokenTag(); ?>
<?= $this->element('User/Reservations/fieldset', [
    'reservationForm' => $reservationForm,
    'valueOptions' => $valueOptions,
    'mode' => $mode,
]) ?>
<fieldset class="input-info">
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?php if ($mode === 'edit') : ?>
            <?php if ($formController === 'Guest') : ?>
                <?= $this->Html->link(
                    $this->Tr->t('common/backBtn'),
                    [
                        'prefix' => 'User',
                        'controller' => 'Guest',
                        'action' => 'reservationDetail',
                        'id' => $reservationForm->getReservationEntity()->get('id'),
                    ],
                    ['class' => ['cmn-btn', 'is-gray', 'is-reset']]
                ); ?>
            <?php else : ?>
                <?= $this->Html->link(
                    $this->Tr->t('common/backBtn'),
                    [
                        'prefix' => 'User',
                        'controller' => 'Reservations',
                        'action' => 'view',
                        'id' => $reservationForm->getReservationEntity()->get('id'),
                    ],
                    ['class' => ['cmn-btn', 'is-gray', 'is-reset']]
                ); ?>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($this->Authority->isAuthority(true, 'Reservations', 'calendar')) : ?>
                <?= $this->Html->link(
                    $this->Tr->t('common/backBtn'),
                    [
                        'prefix' => 'User',
                        'controller' => 'Reservations',
                        'action' => 'calendar',
                        '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                    ],
                    ['class' => ['cmn-btn', 'is-gray', 'is-reset']]
                ); ?>
            <?php endif; ?>
        <?php endif; ?>
        <?= $this->Form->button($this->Tr->t('reservation/nextBtn'), [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </p>
</fieldset>
<?= $this->Form->end() ?>
<?= $this->element('Common/file_form') ?>
