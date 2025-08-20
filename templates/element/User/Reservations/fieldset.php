<?php

use App\Model\Entity\FormGroup;

?>
<?php if ($mode === 'add' && !$this->CommonData->existsUserLoginData() && count($valueOptions['reservationType']) > 1): ?>
    <fieldset class="input-info">
        <table class="input-box">
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?= $this->Tr->h('reservation/registerUser') ?>
                    </div>
                </th>
                <td>
                    <p class="cmn-txt">
                        <?= $this->Template->radio('reservation_type', [
                            'type' => 'radio',
                            'options' => $valueOptions['reservationType'],
                            'class' => ['js_reservation_type'],
                            'value' => $reservationForm->getReservationParameter('reservation_type'),
                        ]) ?>
                    </p>
                </td>
            </tr>
        </table>
    </fieldset>
<?php endif; ?>
<?php if ($mode !== 'add' || $this->CommonData->existsUserLoginData() || !empty($valueOptions['reservationType'])): ?>
    <?php foreach ($reservationForm->getReservationFormGroups() as $formType => $formGroups): ?>
        <?= $this->element('User/Common/fieldset/input_items_fieldset', [
            'formGroups' => $formGroups,
            'options' => [
                'event' => $reservationForm->getReservationEntity()->getEventEntity(),
                'user' => $reservationForm->getReservationEntity()->getUserEntity(),
                'reservation' => $reservationForm->getReservationEntity(),
                'mode' => $mode,
            ],
        ]) ?>
        <?php if (((string)$formType) === ((string)FormGroup::FORM_TYPE_RESERVATION)): ?>
            <?php
            $showPaymentMethod = false;
            $showPaymentStatus = false;
            if ($this->Setting->getSystemSetting()->usePayment()) {
                if (((string)$reservationForm->getReservationEntity()->get('payment_method_id')) !== '') {
                    $showPaymentMethod = true;
                }
                if (((string)$reservationForm->getReservationEntity()->get('payment_status_id')) !== '') {
                    $showPaymentStatus = true;
                }
            }
            ?>
            <?php if ($showPaymentMethod || $showPaymentStatus): ?>
                <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('reservation/chargeInfo') ?></h3>
                <fieldset class="input-info">
                    <table class="input-box">
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
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($reservationForm->requiredUserTerms() || $reservationForm->requiredReservationTerms()): ?>
        <?php if ($reservationForm->requiredUserTerms()): ?>
            <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('reservation/userTerms') ?></h3>
            <fieldset class="input-info">
                <div class="userPolicy wysiwyg-area">
                    <?= $reservationForm->getUserTerms() ?>
                </div>
                <p class="cmn-txt tac mgt-20">
                    <?= $this->Template->checkbox('user_terms', [
                        'type' => 'checkbox',
                        'label' => [
                            'text' => $this->Tr->t('reservation/agreeUserTerms'),
                            'class' => ['cmn-check'],
                        ],
                    ]) ?>
                </p>
            </fieldset>
        <?php endif; ?>
        <?php if ($reservationForm->requiredReservationTerms()): ?>
            <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('reservation/reservationTerms') ?></h3>
            <fieldset class="input-info">

                <div class="userPolicy wysiwyg-area">
                    <?= $reservationForm->getReservationTerms() ?>
                </div>
                <p class="cmn-txt tac mgt-20">
                    <?= $this->Template->checkbox('reservation_terms', [
                        'type' => 'checkbox',
                        'label' => [
                            'text' => $this->Tr->t('reservation/agreeReservationTerms'),
                            'class' => ['cmn-check'],
                        ],
                    ]) ?>
                </p>
            </fieldset>
        <?php endif; ?>
    <?php endif; ?>
    <?= $this->element('User/Reservations/sctl', [
        'reservationForm' => $reservationForm,
    ]) ?>
<?php endif; ?>
<div class="hidden">
    <?php foreach ($reservationForm->getReservationParameter() as $key => $value): ?>
        <input type="hidden" class="js_reservation_parameter" value="<?= h($value) ?>" data-name="<?= h($key) ?>"/>
    <?php endforeach; ?>
    <input type="hidden" class="js_reservation_parameter" value="<?= h($mode) ?>" data-name="mode"/>
    <input type="hidden" class="js_reservation_parameter"
           value="<?= h($reservationForm->getReservationEntity()->get('id')) ?>" data-name="reservation_id"/>
</div>
