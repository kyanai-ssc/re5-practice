<?php $this->start('ajax_html'); ?>
<section class="contents-area">
    <?= $this->element('User/Reservations/detail', [
        'reservationForm' => $reservationForm,
        'valueOptions' => $reservationForm->getFieldValueOptions(),
        'mode' => 'viewContinuous',
    ]) ?>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Form->button($this->Tr->t('reservationsAdd/continuousEditBtn'), [
            'class' => ['cmn-btn', 'is-blue', 'js_change_url'],
            'type' => 'button',
            'data-url' => $this->Url->build([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'add',
                '?' => [
                        'key' => $reservationForm->getContinuousParameter('key'),
                    ] + $this->Configure->read('Setting.formInput.backQuery'),
            ], ['escape' => false]),
        ]) ?>
    </p>
</section>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
]) ?>
