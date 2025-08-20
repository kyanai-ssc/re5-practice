<?php

use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\FormGroup;
use App\Model\Entity\Reservation;
use App\Model\Entity\User;
use App\Model\Table\ReservationsTable;

?>
<div class="form-input-set">
    <?php if ($mode === 'add'): ?>
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Form->label('reservation_type', '会員') ?>
                            <?= $this->element('Admin/Common/form/require') ?>
                        </div>
                    </th>
                    <td>
                        <?php if (!$reservationForm->isContinuous()): ?>
                            <div>
                                <?= $this->Template->radio('reservation_type', [
                                    'type' => 'radio',
                                    'options' => $valueOptions['reservationType'],
                                    'class' => ['js_reservation_type'],
                                    'value' => $reservationForm->getReservationParameter('reservation_type'),
                                ]) ?>
                            </div>
                            <div class="<?= ((string)$reservationForm->getReservationParameter('reservation_type') === (string)ReservationsTable::RESERVATION_TYPE_EXISTING_USER) ? '' : 'hidden'; ?>">
                                <?= $this->Form->button('会員選択', [
                                    'type' => 'button',
                                    'class' => ['js_select_user', 'cmn-btn', 'is-blue'],
                                    'data-title' => '予約する会員を選択'
                                ]) ?>
                            </div>
                        <?php else: ?>
                            <div>
                                <?= h($valueOptions['reservationType'][$reservationForm->getReservationParameter('reservation_type')]) ?>
                                <?= $this->Form->hidden('reservation_type', [
                                    'value' => $reservationForm->getReservationParameter('reservation_type'),
                                ]) ?>
                            </div>
                        <?php endif; ?>
                        <?= $this->FormError->errorWithoutNested('reservations.user_id') ?>
                        <div class="tool-wrap">

                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    <?php endif; ?>
    <?php foreach ($reservationForm->getReservationFormGroups() as $formType => $formGroups): ?>
        <fieldset class="mgt-10">
            <div class="ttl-panel-show">
            <span class="ttl-s">
            <?php if ($formType === FormGroup::FORM_TYPE_USER) : ?>
                <?php if (($mode === 'edit' && $reservationForm->getReservationEntity()->getUserEntity()->get('guest_flg') === User::GUEST_FLG_ON)
                    || ($mode === 'add' && (string)$reservationForm->getReservationParameter('reservation_type') !== (string)ReservationsTable::RESERVATION_TYPE_EXISTING_USER)
                ) {
                    $addClass = 'hidden';
                } else {
                    $addClass = '';
                } ?>
                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_setting"/></svg>', [
                    'type' => 'button',
                    'title' => '顧客表示項目設定',
                    'class' => ['js_select_search_items', 'btn-setting', ' toolBtn', 'tooltip', $addClass],
                    'data-title' => '顧客表示項目設定',
                    'data-type' => AdminSearchItem::TYPE_RESERVATION_USER,
                    'escapeTitle' => false,
                ]) ?>

            <?php endif; ?>
            <?= h($this->Configure->read('Master.form.formType.' . $formType)) ?></span>
                <button type="button" class="showBtn"></button>
            </div>
            <div class="showWrap">
                <?= $this->element('Admin/Common/fieldset/input_items_fieldset', [
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
                <h3 class="ttl-sec mgt-40">ステータス情報</h3>
                <table class="input-box">
                    <tbody>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">予約ステータス
                                    <?= $this->element('Admin/Common/form/require') ?>
                                </div>
                            </th>
                            <td>
                                <?= $this->Form->control('reservations.reservation_status_id', [
                                    'type' => 'select',
                                    'options' => $valueOptions['reservationStatusId'],
                                    'default' => $reservationForm->getReservationEntity()->getEventEntity()->get('reservation_status_id'),
                                    'class' => 'select'
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">受付ステータス
                                </div>
                            </th>
                            <td>
                                <?= $this->Form->control('reservations.reception_status_id', [
                                    'type' => 'select',
                                    'options' => $valueOptions['receptionStatusId'],
                                    'class' => 'select',
                                    'empty' => true
                                ]) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <fieldset>
                <h3 class="ttl-sec mgt-40">支払い情報</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">料金</div>
                        </th>
                        <td>
                            <?php $calculateChargeDefault = null; ?>
                            <?php if ($mode === 'add'): ?>
                                <?php $calculateChargeDefault = Reservation::CALCULATE_CHARGE_ON; ?>
                            <?php endif; ?>
                            <?= $this->Template->checkbox('reservations.calculate_charge', [
                                'type' => 'checkbox',
                                'value' => Reservation::CALCULATE_CHARGE_ON,
                                'default' => $calculateChargeDefault,
                                'class' => ['btn-tool', 'cmn-check', 'js_calculate_charge_check'],
                                'label' => [
                                    'class' => ['cmn-check', 'btn-tool'],
                                    'text' => '自動計算',
                                    'title' => '自動計算',
                                ],
                            ]) ?>
                            <div class="d-flex">
                                    <span>
                                        <?= $this->Form->control('reservations.charge', [
                                            'type' => 'text',
                                            'class' => ['mgr-10', 'w-160', 'js_reservation_charge'],
                                        ]) ?>
                                    </span>
                                <?= $this->Form->button('再計算', [
                                    'type' => 'button',
                                    'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_calculate_charge'],
                                ]) ?>
                            </div>
                        </td>
                    </tr>
                    <?php if ($this->Setting->getSystemSetting()->usePayment()): ?>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">決済方法</div>
                            </th>
                            <td>
                                <?= $this->Form->control('reservations.payment_method_id', [
                                    'type' => 'select',
                                    'options' => $valueOptions['paymentMethodId'],
                                    'empty' => true,
                                    'class' => ['select']
                                ]) ?>
                                <?php if ($this->Setting->isPaymentServiceGmo()): ?>
                                    <div class="desc-wrap">
                                        <p>
                                            GMOペイメントゲートウェイ社の決済システムをご利用の場合はPayPay、ApplePay、auPAYは使用できません。
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($mode === 'edit' && $reservationForm->getReservationEntity()->hasReservationPayment()): ?>
                            <tr class="field-input">
                                <th class="ttl-input">
                                <?php
                                    $paymentIdTitle = '決済ID';
                                    if ($reservationForm->getReservationEntity()->getReservationPayment()->isReservationPaymentServiceSb()) {
                                        $paymentIdTitle .= '（受注ID）';
                                    }
                                ?>
                                    <div class="ttl-input-wrap"><?= h($paymentIdTitle); ?></div>
                                </th>
                                <td>
                                    <p class="cmn-txt">
                                        <?= h($reservationForm->getReservationEntity()->getReservationPayment()->get('payment_order_id')) ?>
                                    </p>
                                </td>
                            </tr>
                            <?php if ($reservationForm->getReservationEntity()->getReservationPayment()->isReservationPaymentServiceSb()) : ?>
                                <tr class="field-input">
                                    <th class="ttl-input">
                                        <div class="ttl-input-wrap">決済トラッキングID</div>
                                    </th>
                                    <?php if ($reservationForm->getReservationEntity()->getReservationPayment()->canUpdatePaymentTrackingId()) : ?>
                                        <td>
                                            <?= $this->Form->control('reservations.payment_tracking_id', [
                                                'type' => 'text',
                                                'class' => ['mgr-10'],
                                                'default' => h($reservationForm->getReservationEntity()->getReservationPayment()->get('payment_tracking_id')),
                                            ]) ?>
                                            <div class="desc-wrap">
                                                <p>auPAYは決済トラッキングIDの存在チェックは実施されませんのでご注意ください。</p>
                                            </div>
                                        </td>
                                    <?php else: ?>
                                        <td>
                                            <p class="cmn-txt">
                                                <?= h($reservationForm->getReservationEntity()->getReservationPayment()->get('payment_tracking_id')) ?>
                                            </p>
                                            <?= $this->Form->hidden('reservations.payment_tracking_id', [
                                                'value' => h($reservationForm->getReservationEntity()->getReservationPayment()->get('payment_tracking_id')),
                                            ]) ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">決済ステータス</div>
                            </th>
                            <td>
                                <?= $this->Form->control('reservations.payment_status_id', [
                                    'type' => 'select',
                                    'options' => $valueOptions['paymentStatusId'],
                                    'empty' => true,
                                    'class' => ['select']
                                ]) ?>
                            </td>
                        </tr>
                        <?php if ($mode === 'edit' && $reservationForm->getReservationEntity()->hasReservationPayment()): ?>
                            <tr class="field-input">
                                <th class="ttl-input">
                                    <div class="ttl-input-wrap">決済連携状況</div>
                                </th>
                                <td>
                                    <p class="cmn-txt">
                                        <?php $value = $this->Master->getReservationPaymentStatus(
                                            $reservationForm->getReservationEntity()->getReservationPayment()->get('status'),
                                            $reservationForm->getReservationEntity()->getReservationPayment()->get('payment_limit')
                                        ); ?>
                                        <?= h($this->Configure->read('Master.payment.reservationPaymentStatus.' . $value)) ?>
                                    </p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </fieldset>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="hidden">
        <?php foreach ($reservationForm->getReservationParameter() as $key => $value): ?>
            <input type="hidden" class="js_reservation_parameter" value="<?= h($value) ?>" data-name="<?= h($key) ?>"/>
        <?php endforeach; ?>
        <input type="hidden" class="js_reservation_parameter" value="<?= h($mode) ?>" data-name="mode"/>
        <input type="hidden" class="js_reservation_parameter"
               value="<?= h($reservationForm->getReservationEntity()->get('id')) ?>" data-name="reservation_id"/>
        <?php $this->start('select_user_iframe'); ?>
        <iframe class="select_user_iframe" src="<?= $this->Url->build([
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'list',
            '?' => [
                'guest_flg' => [User::GUEST_FLG_OFF],
                'select_user' => $this->Configure->readOrFail('Master.common.flg.on'),
            ],
        ]) ?>">
        </iframe>
        <?php $this->end('select_user_iframe'); ?>
        <input type="hidden" class="js_select_user_iframe" value="<?= h($this->fetch('select_user_iframe')) ?>"/>
    </div>
</div>
