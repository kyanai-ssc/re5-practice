<?php

use App\Model\Entity\Reservation;
use Cake\Core\Configure;
?>
<?php $this->start('inputTypeUsageDateDetail'); ?>
<div>
    <?php if(isset($options['mode']) && $options['mode'] !== 'addConf'): ?>
        <span class="txt fwb"><?= $this->Template->displayDayAndWeek($detailValue) ?></span>
    <?php endif; ?>
    <?php if (isset($options['mode']) && ($options['mode'] === 'add' || $options['mode'] === 'edit')): ?>
        <?= $this->element('InputType/Inputs/UsageDate/select_calendar', [
            'reservation' => $options['reservation'],
            'adminFlg' => $formItem->getInputTypeItem()->isAdmin(),
        ]) ?>
        <!-- 繰り返し予約ができる条件 -->
        <?php if ($options['mode'] === 'add' && $options['user'] && $this->Template->repeatReservation($options['user'], $options['reservation'])): ?>
            <div class="mgt-10" >
                <?= $this->Template->radio('reservations.repeat_reservation', [
                    'type' => 'radio',
                    'options' => $valueOptions['repeatReservationType'],
                    'class' => ['js_change_repeat_reservation'],
                    'default' => (string)Reservation::RESERVATION_TYPE_ONE_RESERVATION,
                ]) ?>
            </div>
            <?php
                $repeatReservationClass = 'hidden';
                if ($reservationForm->getData('reservations.repeat_reservation') === (string)Reservation::RESERVATION_TYPE_REPEAT_RESERVATION) {
                    $repeatReservationClass = '';
                }
            ?>

            <div
                class="js_toggle_repeat_reservation <?= h($repeatReservationClass) ?> js_toggle_repeat_reservation_<?= h(Reservation::RESERVATION_TYPE_REPEAT_RESERVATION) ?>">
                <dl>
                    <dt>
                        <span class="txt fwb">
                            <?= $this->Template->displayDayAndWeek($detailValue) ?>
                        </span>
                        <span class="txt mgl-10 mgr-10">～</span>
                        <?= $this->Form->control('reservations.date_to', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['js-datepicker-type-range-end',],
                            'data-datepicker-dd' => 'on',
                        ]) ?>
                    </dt>

                    <div>
                        <?= $this->Template->checkbox('reservations.select_day_of_week', [
                            'type' => 'checkbox',
                            'label' => ['class' => ['cmn-check','btn-tool',], 'text' => '曜日を指定する', ],
                            'class' => ['js_select_day_of_week',],
                            'default' => $this->Configure->readOrFail('Master.common.flg.off'),
                        ]) ?>
                    </div>

                     <?php
                        $selectDayOfWeekClass = 'hidden';
                        if ($reservationForm->getData('reservations.select_day_of_week') === (string)$this->Configure->readOrFail('Master.common.flg.on')) {
                            $selectDayOfWeekClass = '';
                        }
                    ?>
                    <div class='js_day_of_week  <?= h($selectDayOfWeekClass) ?> js_day_of_week_<?= $this->Configure->readOrFail('Master.common.flg.on') ?>'>
                        <?= $this->Template->radio('reservations.day_of_week', [
                            'type' => 'radio',
                            'options' => $valueOptions['week'],
                        ]) ?>
                    </div>
                </dl>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if (isset($options['mode']) && ($options['mode'] === 'addConf')): ?>
    <!-- １回予約の場合表示 -->
    <?php if ($options['reservation']->repeat_reservation === null || $options['reservation']->repeat_reservation === (string)Reservation::RESERVATION_TYPE_ONE_RESERVATION): ?>
        <span class="txt fwb"><?= $this->Template->displayDayAndWeek($detailValue) ?></span>
    <?php else: ?>
    <!-- 複数日予約の場合表示 -->
    <span  class="txt fwb">
        <div>複数日予約</div>
        <div>期間：<?= $this->Template->displayDayAndWeek($detailValue) ?> ～ <?= $this->Template->displayDayAndWeek($options['reservation']->date_to) ?></div>
        <?php if ($options['reservation']->day_of_week !== null): ?>
            <?php $dayOfWeek = 'Master.common.week.' . (int)$options['reservation']->day_of_week?>
            <div class="mgt-10">
                曜日：<?= Configure::readOrFail($dayOfWeek) ?>
            </div>
        <?php endif; ?>
        <div class="mgt-10" >
            <p>予約日</p>
            <!-- 取得した日をループで表示 -->
            <?php foreach ($options['reservation']['reserve_dates'] as $reserveDay): ?>
                <p>・<?= $this->Template->displayDayAndWeek($reserveDay) ?><p>
            <?php endforeach; ?>
        </div>
        <?php if ($options['reservation']->can_not_reserve_dates): ?>
            <div class="mgt-10">
                <p>＊下記の日は予約できません。</p>
                <!-- 取得した予約できない日をループで表示 -->
                <?php foreach ($options['reservation']['can_not_reserve_dates'] as $canNotReserveDay): ?>
                    <p>・<?= $this->Template->displayDayAndWeek($canNotReserveDay) ?><p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </span>
    <?php endif; ?>
<?php endif; ?>
<?php $this->end('inputTypeUsageDateDetail'); ?>
<?= $this->element('InputType/Inputs/detail', [
    'formItem' => $formItem,
    'detailValue' => $this->fetch('inputTypeUsageDateDetail'),
    'escape' => false,
    'divAddClass' => 'cmnInput d-flex fwb',
    'options' => $options,
]) ?>
