<?php
use App\Model\Entity\FormGroup;
?>
<div class="panel-show-set">
    <?php if ($mode === 'detail'): ?>
        <fieldset class="mgt-10">
            <div class="ttl-panel-show">
                <span class="ttl-s"><?= h('ID:' . $reservationForm->getReservationEntity()->get('id') . '　' . date('Y/m/d', strtotime($reservationForm->getReservationEntity()->get('usage_timestamp_from'))) . '(' . $this->Configure->read('Master.event.week.' . date('w',  strtotime($reservationForm->getReservationEntity()->get('usage_timestamp_from')))) . ')' . date(' H:i', strtotime($reservationForm->getReservationEntity()->get('usage_timestamp_from'))) . date(' - H:i', strtotime($reservationForm->getReservationEntity()->get('usage_timestamp_to')))) ?></span>
                <button type="button" class="showBtn <?= $open ?>" data-notsave="true"></button>
            </div>
            <div class="showWrap mgt-10" style="<?= $display ?>">
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
                                    <?= h($reservationForm->getReservationEntity()->get('id')) ?>
                                </p>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">
                                    予約ステータス
                                </div>
                            </th>
                            <td class="status-td <?= $this->Configure->read('Master.reservation.statusClass.' . $this->Master->getReservationStatusType($reservationForm->getReservationEntity()->get('reservation_status_id'))) ?>">
                                <p class="cmn-txt">
                                    <?= h($this->Master->getReservationStatusName($reservationForm->getReservationEntity()->get('reservation_status_id'))) ?>
                                </p>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">
                                    受付ステータス
                                </div>
                            </th>
                            <td>
                                <p class="cmn-txt">
                                    <?php if ($reservationForm->getReservationEntity()->get('reception_status_id') !== null): ?>
                                        <?= h($this->Master->getReceptionStatusName($reservationForm->getReservationEntity()->get('reception_status_id'))) ?>
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php foreach ($reservationForm->getReservationFormGroups() as $formType => $formGroups): ?>
                    <fieldset class="mgt-10">
                        <div class="ttl-panel-show">
                            <span class="ttl-s"><?= h($this->Configure->read('Master.form.formType.' . $formType)) ?></span>
                            <button type="button" class="showBtn"></button>
                        </div>
                        <div class="showWrap">
                            <?= $this->element('Admin/Common/fieldset/input_items_detail', [
                                'formGroups' => $formGroups,
                                'options' => [
                                    'event' => $reservationForm->getReservationEntity()->getEventEntity(),
                                    'user' => $reservationForm->getReservationEntity()->getUserEntity(),
                                    'reservation' => $reservationForm->getReservationEntity(),
                                    'mode' => $mode,
                                ],
                            ]) ?>
                        </div>
                    </fieldset>
                    <?php if (((string)$formType) === ((string)FormGroup::FORM_TYPE_RESERVATION)): ?>
                        <fieldset>
                            <h3 class="ttl-sec mgt-40">支払い情報</h3>
                            <table class="input-box">
                                <tbody>
                                <tr class="field-input">
                                    <th class="ttl-input">
                                        <div class="ttl-input-wrap">料金</div>
                                    </th>
                                    <td>
                                        <p class="cmn-txt fwb">
                                            <?= h($reservationForm->getReservationEntity()->get('charge')) ?>
                                        </p>
                                    </td>
                                </tr>
                                <?php if ($this->Setting->getSystemSetting()->usePayment()): ?>
                                    <tr class="field-input">
                                        <th class="ttl-input">
                                            <div class="ttl-input-wrap">決済方法</div>
                                        </th>
                                        <td>
                                            <p class="cmn-txt">
                                                <?php if (((string)$reservationForm->getReservationEntity()->get('payment_method_id')) !== ''): ?>
                                                    <?= h($this->Master->getPaymentMethodName($reservationForm->getReservationEntity()->get('payment_method_id'))) ?>
                                                <?php endif; ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr class="field-input">
                                        <th class="ttl-input">
                                            <div class="ttl-input-wrap">決済ステータス</div>
                                        </th>
                                        <td class="status-td <?= h($this->Configure->read('Master.payment.statusClass.' . $reservationForm->getReservationEntity()->get('payment_status_id'))) ?>">
                                            <p class="cmn-txt">
                                                <?php if (((string)$reservationForm->getReservationEntity()->get('payment_status_id')) !== ''): ?>
                                                    <?= h($this->Master->getPaymentStatusName($reservationForm->getReservationEntity()->get('payment_status_id'))) ?>
                                                <?php endif; ?>
                                            </p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </fieldset>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?= $this->Form->create(null, [
                'type' => 'post',
                'url' => [
                    'prefix' => 'Admin',
                    'controller' => 'ReceptionStatuses',
                    'action' => 'edit',
                    'id' => $reservationForm->getReservationEntity()->get('id')
                ],
                'idPrefix' => 'formPatterns-add',
                'novalidate' => true,
                'class' => ['js_submit_confirm'],
                'data-confirm-title' => '受付ステータスの更新',
                'data-confirm-message' => '受付ステータスを更新してもよろしいですか？',
                ]) ?>
                <div class="btn-box mgt-20 mgb-20">
                    <?= $this->Form->button('更新する', [
                        'type' => 'submit',
                        'class' => ['cmn-btn', 'is-blue'],
                    ]) ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </fieldset>
    <?php endif; ?>
</div>
