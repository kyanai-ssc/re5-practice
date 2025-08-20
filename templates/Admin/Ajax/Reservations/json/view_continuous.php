<?php $this->start('ajax_html'); ?>
<div>
    <?= $this->element('Admin/Reservations/detail', [
        'reservationForm' => $reservationForm,
        'valueOptions' => $reservationForm->getFieldValueOptions(),
        'mode' => 'viewContinuous',
    ]) ?>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('編集', [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'add',
            '?' => [
                    'key' => $reservationForm->getContinuousParameter('key'),
                ] + $this->Configure->read('Setting.formInput.backQuery'),
        ], ['class' => ['cmn-btn', 'is-blue']]) ?>
        <?= $this->Form->button('削除', [
            'type' => 'button',
            'class' => ['cmn-btn', 'is-gray', 'js_reservation_remove_continuous'],
            'data-continuous-key' => $reservationForm->getContinuousParameter('key'),
        ]) ?>
    </div>
</div>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
]) ?>
