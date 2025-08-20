<?php
use App\Model\Entity\FormGroup;
?>
<?php if ($mode === 'detail' || $mode === 'guestDetail'): ?>
    <fieldset class="input-info">
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('reservation/reservationId') ?>
                    </div>
                </th>
                <td>
                    <p class="cmn-txt">
                        <?= h($reservationForm->getReservationEntity()->get('id')) ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
<?php endif; ?>
<?php foreach ($reservationForm->getReservationFormGroups() as $formType => $formGroups): ?>
    <?php if ($mode !== 'viewContinuous' || ((string)$formType) !== ((string)FormGroup::FORM_TYPE_USER)): ?>
        <?= $this->element('User/Common/fieldset/input_items_detail', [
            'formGroups' => $formGroups,
            'options' => [
                'event' => $reservationForm->getReservationEntity()->getEventEntity(),
                'user' => $reservationForm->getReservationEntity()->getUserEntity(),
                'reservation' => $reservationForm->getReservationEntity(),
                'mode' => $mode,
            ],
        ]) ?>
        <?php if (((string)$formType) === ((string)FormGroup::FORM_TYPE_RESERVATION)): ?>
            <?php if ($mode === 'detail' || $mode === 'guestDetail'): ?>
                <?php if (
                    $reservationForm->getReservationEntity()->displayReservationSmartLockPin() || 
                    $reservationForm->getReservationEntity()->displayReservationUniversalAccessKey()
                ) : ?>
                    <fieldset>
                        <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('reservationView/remoteLockSetting') ?></h3>
                        <table class="input-box">
                            <tbody>
                                <?php if ($reservationForm->getReservationEntity()->displayReservationSmartLockPin()) : ?>
                                    <tr class="field-input">
                                        <th class="ttl-input">
                                            <div class="ttl-input-wrap"><?= $this->Tr->h('reservationView/smartLockPin') ?></div>
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
                                            <div class="ttl-input-wrap"><?= $this->Tr->h('reservationView/universalAccessKey') ?></div>
                                        </th>
                                        <td>
                                            <p class="cmn-txt">
                                                <?= $this->Html->link($reservationForm->getReservationEntity()->getReservationSmartLockEntity()->get('smart_lock_key_url'), null, ['target' => '_blank']) ?>
                                            </p>
                                            <p><?= $this->Tr->h('reservationView/universalAccessKey/description') ?></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </fieldset>
                <?php endif; ?>
                <?php if ($reservationForm->getReservationEntity()->displayReservationSmartLockKeyUrl()) : ?>
                    <fieldset>
                        <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('reservationView/akerunSetting') ?></h3>
                        <table class="input-box">
                            <tbody>
                                <tr class="field-input">
                                    <th class="ttl-input">
                                        <div class="ttl-input-wrap"><?= $this->Tr->h('reservationView/smartLockKeyUrl') ?></div>
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
            <?php
            $showCharge = false;
            $showPaymentMethod = false;
            $showPaymentStatus = false;
            if ($reservationForm->getReservationEntity()->get('charge') > 0) {
                $showCharge = true;
            }
            if ($this->Setting->getSystemSetting()->usePayment()) {
                if (((string)$reservationForm->getReservationEntity()->get('payment_method_id')) !== '') {
                    $showPaymentMethod = true;
                }
                if (((string)$reservationForm->getReservationEntity()->get('payment_status_id')) !== '') {
                    $showPaymentStatus = true;
                }
            }
            ?>
            <?php if ($showCharge || $showPaymentMethod || $showPaymentStatus): ?>
                <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('reservation/chargeInfo') ?></h3>
                <fieldset class="input-info">
                    <table class="input-box">
                        <?php if ($showCharge): ?>
                            <tr class="field-input">
                                <th class="ttl-input">
                                    <div class="ttl-input-wrap">
                                        <?= $this->Tr->h('reservation/charge') ?>
                                    </div>
                                </th>
                                <td>
                                    <p class="cmn-txt fwb">
                                        <?= h($reservationForm->getReservationEntity()->get('charge')) ?><?= $this->Tr->h('reservation/chargeUnit') ?>
                                    </p>
                                </td>
                                <?php if (isset($requiredChargeBreakdownAll) && !$requiredChargeBreakdownAll && $reservationForm->requiredChargeBreakdownEach()): ?>
                                    <?= $this->element('User/Reservations/charge_breakdown', [
                                        'reservationForms' => [$reservationForm],
                                    ]) ?>
                                <?php endif; ?>
                            </tr>
                        <?php endif; ?>
                        <?php if ($showPaymentMethod): ?>
                            <tr class="field-input">
                                <th class="ttl-input">
                                    <div class="ttl-input-wrap">
                                        <?= $this->Tr->h('reservation/paymentMethod') ?>
                                    </div>
                                </th>
                                <td>
                                    <p class="cmn-txt">
                                        <?= h($this->Master->getPaymentMethodName($reservationForm->getReservationEntity()->get('payment_method_id'))) ?>
                                    </p>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($showPaymentStatus): ?>
                            <tr class="field-input">
                                <th class="ttl-input">
                                    <div class="ttl-input-wrap">
                                        <?= $this->Tr->h('reservation/paymentStatus') ?>
                                    </div>
                                </th>
                                <td>
                                    <p class="cmn-txt">
                                        <?= h($this->Master->getPaymentStatusName($reservationForm->getReservationEntity()->get('payment_status_id'))) ?>
                                    </p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </fieldset>
            <?php endif; ?>
            <?php if (
                ($mode === 'detail' || $mode === 'guestDetail')
                && $reservationForm->getReservationEntity()->displayQrCode()
            ): ?>
                <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('reservationView/qrCode/title') ?></h3>
                <?= $this->Tr->nl2br('reservationView/qrCode/description') ?>
                </br>
                <?= $this->Template->qrCodeImage($reservationForm->getReservationEntity()->get('qr_code')) ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
<?php endforeach; ?>
