<?php
use App\Model\Entity\EVENT;
?>

<div class="panel-show-set">
    <?php if ($mode === 'reservationId'): ?>
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Form->label('id', '予約ID') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($event->id) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            予約ステータス
                        </div>
                    </th>
                    <td class="status-td <?= $this->Configure->read('Master.reservation.statusClass.' . $this->Master->getReservationStatusType($reservation->reservation_status_id)) ?>">
                        <p class="cmn-txt">
                            <?= h($this->Master->getReservationStatusName($reservation->reservation_status_id)) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            受付ステータス
                        </div>
                    </th>
                    <td class="status-td <?php //$this->Configure->read('Master.reservation.statusClass.' . $this->Master->getReservationStatusType($reservationForm->getReservationEntity()->get('reservation_status_id'))) ?>">
                        <p class="cmn-txt">
                            <?php //h($this->Master->getReservationStatusName($reservationForm->getReservationEntity()->get('reservation_status_id'))) ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    <?php endif; ?>

    <?php if ($mode === 'userId'): ?>
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            会員権限
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($user->user_authority->name) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            メールアドレス
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($user->mail) ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>

    <?php endif; ?>
    <?php if ($mode === 'reservationContent'): ?>
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            絞込みキーワード
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            予約枠名
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($event->name) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            予約利用期間
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($event->date_from . ' ～ ' . $event->date_to) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            実施時間
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                        <?= h($event->time_from . ' ～ ' . $event->time_to) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            予約時間設定
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($event->usage_time_from . '分 ～ ' . $event->usage_time_to . '分') ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            インターバル
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($event->interval_time) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            料金
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($event->event_unit_time . '分 ' . $event->charge . '円') ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            予約受付締切タイミング
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?php if ($event->registration_deadline_type === EVENT::DEADLINE_TYPE_TIME) :?>
                            <?= h('利用日時の ' . $event->registration_deadline_number . '時間前') ?>
                            <?php elseif ($event->registration_deadline_type === EVENT::DEADLINE_TYPE_DAY) :?>
                            <?= h('利用日時の ' . $event->registration_deadline_number . '日前') ?>
                            <?php endif ; ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            予約変更締切タイミング
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?php if ($event->editing_deadline_type === EVENT::DEADLINE_TYPE_TIME) :?>
                            <?= h('利用日時の ' . $event->editing_deadline_number . '時間前') ?>
                            <?php elseif ($event->editing_deadline_type === EVENT::DEADLINE_TYPE_DAY) :?>
                            <?= h('利用日時の ' . $event->editing_deadline_number . '日前') ?>
                            <?php endif ; ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            予約キャンセル締切タイミング
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?php if ($event->cancellation_deadline_type === EVENT::DEADLINE_TYPE_TIME) :?>
                            <?= h('利用日時の ' . $event->cancellation_deadline_number . '時間前') ?>
                            <?php elseif ($event->cancellation_deadline_type === EVENT::DEADLINE_TYPE_DAY) :?>
                            <?= h('利用日時の ' . $event->cancellation_deadline_number . '日前') ?>
                            <?php endif ; ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            利用日
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($event->date_from) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            利用時間
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($event->time_from. ' ～ ' .$event->time_to) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            予約時間
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($reservation->usage_time) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            予約数
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($reservation->number) ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            料金
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($reservation->charge . '円') ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            決済方法
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?php //h($reservation->payment_method->name) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            決済ステータス
                        </div>
                    </th>
                    <!-- <td class="status-td <?php //$this->Configure->read('Master.payment.statusClass.' . $this->Master->getReservationStatusType($reservation->payment_status_id)) ?>">
                        <p class="cmn-txt">
                            <?php // h($reservation->payment_status->name) ?>
                        </p>
                    </td> -->
                </tr>
                </tbody>
            </table>
        </fieldset>
    <?php endif; ?>
</div>