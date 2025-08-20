<?php

use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\FormGroup;
use App\Model\Entity\User;

?>
<?php if ($mode === 'detail'): ?>
    <?= $this->Form->create($reservationForm, [
        'type' => 'post',
        'url' => [
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => $mode,
            'id' => $reservationForm->getReservationEntity()->get('id'),
            '?' => $reservationForm->getReservationParameter(),
        ],
        'idPrefix' => 'reservations-' . $mode,
        'novalidate' => true,
        'class' => ['js_submit_once', 'js_reservation_form'],
    ]) ?>
    <?= $this->Token->getTokenTag() ?>
    <?= $this->Token->getTokenError() ?>
    <div class="hidden">
        <?php foreach ($reservationForm->getReservationParameter() as $key => $value): ?>
            <input type="hidden" class="js_reservation_parameter" value="<?= h($value) ?>" data-name="<?= h($key) ?>"/>
        <?php endforeach; ?>
        <input type="hidden" class="js_reservation_parameter" value="<?= h($mode) ?>" data-name="mode"/>
        <input type="hidden" class="js_reservation_parameter"
            value="<?= h($reservationForm->getReservationEntity()->get('id')) ?>" data-name="reservation_id"/>
    </div>
<?php endif; ?>
<div class="panel-show-set">
    <?php if ($mode === 'detail' || $mode === 'addConf' || $mode === 'editConf'): ?>
        <fieldset>
            <table class="input-box">
                <tbody>
                <?php if ($mode === 'detail'): ?>
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
                <?php endif;?>
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
        </fieldset>
    <?php endif; ?>
    <?php if ($mode === 'add' || $mode === 'addConf'): ?>
        <fieldset>
            <h3 class="ttl-sec mgt-40">予約情報</h3>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Form->label('reservation_type', '会員') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($valueOptions['reservationType'][$reservationForm->getReservationParameter('reservation_type')]) ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    <?php endif; ?>
    <?php foreach ($reservationForm->getReservationFormGroups() as $formType => $formGroups): ?>
        <?php if ($mode !== 'viewContinuous' || ((string)$formType) !== ((string)FormGroup::FORM_TYPE_USER)): ?>
            <fieldset class="mgt-10">
                <div class="ttl-panel-show">
                    <span class="ttl-s">
                        <?php if ($mode === 'detail' && $formType === FormGroup::FORM_TYPE_USER) : ?>
                            <?php if ($reservationForm->getReservationEntity()->getUserEntity()->get('guest_flg') === User::GUEST_FLG_ON) {
                                $addClass = 'hidden';
                            } else {
                                $addClass = '';
                            } ?>
                            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_setting"/></svg>', [
                                'type' => 'button',
                                'title' => '顧客表示項目設定',
                                'class' => ['js_select_search_items', 'btn-setting', ' toolBtn', 'tooltip', $addClass,],
                                'data-title' => '顧客表示項目設定',
                                'data-type' => AdminSearchItem::TYPE_RESERVATION_USER,
                                'escapeTitle' => false,
                            ]) ?>
                        <?php endif; ?>
                        <?= h($this->Configure->read('Master.form.formType.' . $formType)) ?>
                    </span>
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
                <?php if ($mode === 'detail'): ?>
                    <?php if (
                        $reservationForm->getReservationEntity()->displayReservationSmartLockPin() ||
                        $reservationForm->getReservationEntity()->displayReservationUniversalAccessKey()
                    ) : ?>
                        <fieldset>
                            <h3 class="ttl-sec mgt-40"><?= h($this->SmartLock->getName()) ?>設定</h3>
                            <?php
                            $eventSmartLock = $reservationForm->getReservationEntity()->getEventEntity()->getEventSmartLockEntity();
                            ?>
                            <?php if ($eventSmartLock !== null) : ?>
                                <p class="mgb-10">
                                    入退室履歴は<?= h($this->SmartLock->getName()) ?>管理画面の
                                    <a href="<?= h(sprintf($this->Configure->read('Setting.smartLock.eventListUrl.remoteLock'), $eventSmartLock->get('smart_lock_device_key'))) ?>" target="_blank">イベント一覧</a>
                                    をご確認ください。
                                </p>
                            <?php endif; ?>
                            <table class="input-box">
                                <tbody>
                                    <?php if ($reservationForm->getReservationEntity()->displayReservationSmartLockPin()) : ?>
                                        <tr class="field-input">
                                            <th class="ttl-input">
                                                <div class="ttl-input-wrap">PIN番号</div>
                                            </th>
                                            <td>
                                                <p class="cmn-txt">
                                                    <?= h($reservationForm->getReservationEntity()->getReservationSmartLockEntity()->get('smart_lock_pin')) ?>
                                                </p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($reservationForm->getReservationEntity()->displayReservationUniversalAccessKey()) : ?>
                                        <tr class="field-input">
                                            <th class="ttl-input">
                                                <div class="ttl-input-wrap">カギ情報URL（ユニバーサルアクセスキー）</div>
                                            </th>
                                            <td>
                                                <p class="cmn-txt">
                                                    <?= $this->Html->link($reservationForm->getReservationEntity()->getReservationSmartLockEntity()->get('smart_lock_key_url'), null, ['target' => '_blank']) ?>
                                                </p>
                                                <p>RemoteLOCKのカギ情報は上記URLにアクセスしてご確認ください。</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </fieldset>
                    <?php endif; ?>
                    <?php if ($reservationForm->getReservationEntity()->displayReservationSmartLockKeyUrl()) : ?>
                        <fieldset>
                            <h3 class="ttl-sec mgt-40"><?= h($this->SmartLock->getName()) ?>設定</h3>
                                <p class="mgb-10">
                                    入退室履歴は<?= h($this->SmartLock->getName()) ?>管理画面の
                                    <a href="<?= h(sprintf($this->Configure->read('Setting.smartLock.eventListUrl.akerun'), $this->SmartLock->getOrganizationsId())) ?>" target="_blank">イベント一覧</a>
                                    をご確認ください。
                                </p>
                            <table class="input-box">
                                <tbody>
                                    <tr class="field-input">
                                        <th class="ttl-input">
                                            <div class="ttl-input-wrap">ロック解除URL</div>
                                        </th>
                                        <td>
                                            <p class="cmn-txt">
                                                <?= $this->Html->link($reservationForm->getReservationEntity()->getReservationSmartLockEntity()->get('smart_lock_key_url'), null, ['target' => '_blank']) ?>
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </fieldset>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($mode === 'editConf'): ?>
                    <fieldset>
                        <h3 class="ttl-sec mgt-40">ステータス情報</h3>
                        <table class="input-box">
                            <tbody>
                                <tr class="field-input">
                                    <th class="ttl-input">
                                        <div class="ttl-input-wrap">受付ステータス
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
                    </fieldset>
                <?php endif; ?>
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
                            <?php if ($reservationForm->getReservationEntity()->get('id') !== null && $reservationForm->getReservationEntity()->hasReservationPayment()): ?>
                                <tr class="field-input">
                                    <th class="ttl-input">
                                    <?php
                                    $paymentIdTitle = '決済ID';
                                    if ($reservationForm->getReservationEntity()->getReservationPayment()->isReservationPaymentServiceSb()) {
                                        $paymentIdTitle .= '（受注ID）';
                                    } ?>
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
                                        <td>
                                            <p class="cmn-txt">
                                                <?php if (
                                                    $mode === 'editConf'
                                                    && $reservationForm->getReservationEntity()->get('payment_tracking_id') !== $reservationForm->getReservationEntity()->getReservationPayment()->get('payment_tracking_id')
                                                ): ?>
                                                    <?= h($reservationForm->getReservationEntity()->get('payment_tracking_id')) ?>
                                                <?php else: ?>
                                                    <?= h($reservationForm->getReservationEntity()->getReservationPayment()->get('payment_tracking_id')) ?>
                                                <?php endif; ?>
                                            </p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>
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
                            <?php if ($reservationForm->getReservationEntity()->get('id') !== null && $reservationForm->getReservationEntity()->hasReservationPayment()): ?>
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
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php if ($mode === 'detail'): ?>
    <?= $this->Form->end() ?>
<?php endif; ?>
