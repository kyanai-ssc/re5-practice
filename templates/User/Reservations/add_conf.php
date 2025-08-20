<?php

use App\Model\Entity\Event;
use App\Model\Entity\PaymentMethod;
use App\Model\Entity\RecaptchaSetting;
use App\Utility\DateTimeUtility;

$this->assign('title', $this->Tr->t('pageTitle/reservationsAdd'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/calendar'),
    ['prefix' => 'User', 'controller' => 'Reservations', 'action' => 'calendar']
);

$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsAdd')
);

$this->Html->script('user/common/continue', [
    'block' => true,
]);
$this->Html->script('user/reservations/confirm', [
    'block' => true,
]);

if ($continuousForm->requiresPayments()) {
    if ($this->Payment->usesApiPayment()) {
        foreach ($this->Payment->getTokenJsUrl() as $tokenJsUrl) {
            $this->Html->script($tokenJsUrl, [
                'block' => true,
            ]);
        }
        $this->Html->script('user/payment/payment', [
            'block' => true,
        ]);
    }
    if ($this->Setting->requiresKycSb()) {
        $this->Html->script($this->Payment->getTds2infotokenJsUrl(), [
            'block' => true,
        ]);
    }
    if ($this->Setting->requiresKycGmo()) {
        $this->Html->css('vendor/intl-tel-input/css/intlTelInput.min', [
            'block' => true,
        ]);
        $this->Html->script('vendor/intl-tel-input/intlTelInput.min', [
            'block' => true,
        ]);
    }
}

$this->Html->script('common/recaptcha', [
    'block' => true,
]);

?>
<?= $this->element('User/Common/form/step', [
    'stepTitle' => 'reservationsAdd/stepTitle',
    'step1' => 'reservationsAdd/step1',
    'step2' => 'reservationsAdd/step2',
    'step3' => 'reservationsAdd/step3',
    'stepNum' => 3,
    'currentStep' => 2,
]); ?>
<section class="contents-area l-main">
    <?= $this->Flash->render('reservationsError') ?>
    <?= $this->Form->create($continuousForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'Reservations',
            'action' => 'add-conf',
            '?' => [
                'key' => $continuousForm->getContinuousParameter('key'),
            ],
        ],
        'idPrefix' => 'reservations-add-conf',
        'novalidate' => true,
        'class' => array_merge(['js_submit_once', 'js_payment_form'], $this->Recaptcha->getFormClass()),
    ] + $this->Recaptcha->getFormAttribute(RecaptchaSetting::ACTION_RESERVE)) ?>
    <?= $this->Token->getTokenTag() ?>
    <?= $this->Token->getTokenError() ?>
    <?= $this->Recaptcha->getTokenElement() ?>
    <?php $requiredChargeBreakdownAll = $continuousForm->requiredChargeBreakdownAll(
        $continuousForm->isContinuous(),
        $continuousForm->hasCharge(),
        $continuousForm->requiresPayments()
    ); ?>
    <?php if (((string)$continuousForm->getContinuousParameter('key')) !== ''): ?>
        <?= $this->element('User/Reservations/detail', [
            'reservationForm' => $continuousForm->getReservationForm($continuousForm->getContinuousParameter('key')),
            'valueOptions' => $continuousForm->getReservationForm($continuousForm->getContinuousParameter('key'))->getFieldValueOptions(),
            'mode' => 'addConf',
            'requiredChargeBreakdownAll' => $requiredChargeBreakdownAll,
        ]) ?>
    <?php else: ?>
        <?= $this->element('User/Common/fieldset/input_items_detail', [
            'formGroups' => $continuousForm->getUserFormGroups(),
            'options' => [
                'user' => $continuousForm->getUserEntity(),
                'adminFlg' => $continuousForm->isAdmin(),
                'mode' => 'addConf',
            ],
        ]) ?>
    <?php endif; ?>
    <?php if ($continuousForm->isContinuous()): ?>
        <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('reservationsAdd/continuousTitle') ?></h3>
        <div>
            <?php foreach ($continuousForm->getErrors() as $name => $errors): ?>
                <?php if ($name === 'reservations'): ?>
                    <?php foreach (array_keys($errors) as $index => $key): ?>
                        [<?= h($index + 1) ?><?= $this->Tr->h('reservationsAdd/continuousErrorLine') ?>]
                        <?= $this->Form->error('reservations.' . $key) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div>
            <div class="schedule-header sticky pc-only">
                <div class="is-listOnly pc-only">
                    <div class="history_list_head">
                        <ul>
                            <li class="h-name">
                                <?= $this->Tr->h('reservationsAdd/continuousEventHeader') ?>
                            </li>
                            <li class="h-dayTime">
                                <?= $this->Tr->h('reservationsAdd/continuousDateTimeHeader') ?>
                            </li>
                            <li class="h-num">
                                <?= $this->Tr->h('reservationsAdd/continuousNumberHeader') ?>
                            </li>
                            <li class="h-confirm">
                                <?= $this->Tr->h('reservationsAdd/continuousDetailHeader') ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="history_list_body is-continue">
                <ul>
                    <?php foreach ($continuousForm->getReservationForm() as $key => $reservationForm): ?>
                        <li class="list_body_line_wrap clearfix">
                            <ul class="list_body_line">
                                <li class="b-name for_txt">
                                    <?php if ($reservationForm->getReservationEntity()->getEventEntity()->has('label')): ?>
                                        [<?= h($reservationForm->getReservationEntity()->getEventEntity()->get('label')->get('name')) ?>]
                                    <?php endif; ?>
                                    <?= h($reservationForm->getReservationEntity()->getEventEntity()->get('name')) ?>
                                </li>
                                <li class="b-dayTime">
                                    <?php if ($reservationForm->getReservationEntity()->getEventEntity()->time_plan === Event::PLAN_MULTIPLE) : ?>
                                        <?= h($this->Template->displayDayAndWeek($reservationForm->getReservationEntity()->get('usage_timestamp_from'))) ?>
                                        <?= h(DateTimeUtility::convertToDateTimeObject($reservationForm->getReservationEntity()->get('usage_timestamp_from'))->format('H:i')) ?>
                                        <?= $this->Tr->h('common/fromToSeparate') ?>
                                        <p><?= $this->Text->truncate(implode(',', $reservationForm->getReservationEntity()->getReservationPlans()), 18, ['tooltip' => true, 'escape' => true]) ?></p>
                                        <?php $reservationForm->getReservationEntity()->getReservationPlans() ?>
                                    <?php else: ?>
                                        <?php
                                        $usageTimeStampFromDtObj = DateTimeUtility::convertToDateTimeObject($reservationForm->getReservationEntity()->get('usage_timestamp_from'));
                                        $usageTimeStampToDtObj = DateTimeUtility::convertToDateTimeObject($reservationForm->getReservationEntity()->get('usage_timestamp_to'));
                                        ?>
                                        <?php if ($reservationForm->getReservationEntity()->getEventEntity()->usage_time_notation === Event::USAGE_TIME_NOTATION_END_TIME) : ?>
                                            <?php if ($usageTimeStampFromDtObj->format('ymd') === $usageTimeStampToDtObj->format('ymd')) : ?>
                                                <?= h($this->Template->displayDayAndWeek($reservationForm->getReservationEntity()->get('usage_timestamp_from'))) ?>
                                                <?= $this->Template->getFromToDisplay(
                                                    h($usageTimeStampFromDtObj->format('H:i')),
                                                    h($usageTimeStampToDtObj->format('H:i')),
                                                    ' ' . $this->Tr->h('common/fromToSeparate') . ' '
                                                ) ?>
                                            <?php else : ?>
                                                <?= $this->Template->getFromToDisplay(
                                                    h($this->Template->displayDayAndWeek($reservationForm->getReservationEntity()->get('usage_timestamp_from'), 'H:i')),
                                                    h($this->Template->displayDayAndWeek($reservationForm->getReservationEntity()->get('usage_timestamp_to'), 'H:i')),
                                                    ' ' . $this->Tr->h('common/fromToSeparate') . ' ',
                                                    true,
                                                    false
                                                ) ?>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <?= h($this->Template->displayDayAndWeek($reservationForm->getReservationEntity()->get('usage_timestamp_from'), 'H:i')) ?>
                                            <?= $this->Tr->h('common/fromToSeparate') ?>
                                            <?php if ($reservationForm->getReservationEntity()->getEventEntity()->type === Event::TYPE_TIME) : ?>
                                                <?= h($reservationForm->getReservationEntity()->get('usage_time')) ?> <?= $this->Tr->h('reservation/dateTimeMinute') ?>
                                            <?php else : ?>
                                                <?= h($reservationForm->getReservationEntity()->get('usage_day')) ?> <?= $this->Tr->h('reservation/dateTimeDay') ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </li>
                                <li class="b-num">
                                    <?= h($reservationForm->getReservationEntity()->get('number')) ?>
                                </li>
                                <li class="b-confirm">
                                    <div>
                                        <?= $this->Form->button($this->Tr->t('reservationsAdd/continuousDetailBtn'), [
                                            'type' => 'button',
                                            'class' => ['cmn-btn', 'is-blue', 'js_reservation_view_continuous'],
                                            'data-continuous-key' => $key,
                                        ]) ?>
                                        <?= $this->Form->button($this->Tr->t('reservationsAdd/continuousEditBtn'), [
                                            'type' => 'button',
                                            'class' => ['cmn-btn', 'is-blue', 'js_change_url'],
                                            'data-url' => $this->Url->build([
                                                'prefix' => 'User',
                                                'controller' => 'Reservations',
                                                'action' => 'add',
                                                '?' => [
                                                        'key' => $key,
                                                    ] + $this->Configure->read('Setting.formInput.backQuery'),
                                            ], ['escape' => false]),
                                        ]) ?>
                                        <?= $this->Form->button($this->Tr->t('reservationsAdd/continuousDeleteBtn'), [
                                            'type' => 'button',
                                            'class' => ['cmn-btn', 'is-gray', 'js_reservation_remove_continuous'],
                                            'data-continuous-key' => $key,
                                        ]) ?>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!$continuousForm->isContinuous()): ?>
        <?php if ($continuousForm->canContinuous()): ?>
            <p class="cmn-txt mgt-20 tac btn-wrap clearfix">
                <?= $this->Form->button($this->Tr->t('reservationsAdd/continuousBtn'), [
                    'type' => 'button',
                    'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_reservation_set_continuous'],
                ]) ?>
            </p>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($continuousForm->canContinuous()): ?>
            <p class="cmn-txt mgt-20 tac btn-wrap clearfix">
                <?= $this->Form->button($this->Tr->t('reservationsAdd/continuousBtn'), [
                    'type' => 'button',
                    'class' => ['cmn-btn', 'is-blue', 'js_change_url', 'is-circle'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'User',
                        'controller' => 'Reservations',
                        'action' => 'calendar',
                        '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                    ], ['escape' => false]),
                ]) ?>
            </p>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($continuousForm->requiresPayments() || $requiredChargeBreakdownAll): ?>
        <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('reservation/paymentInfo') ?></h3>
        <fieldset class="input-info">
            <table class="input-box">
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            <?= $this->Tr->h('reservation/totalCharge') ?>
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt fwb">
                            <?= h($continuousForm->getTotalCharge()) ?><?= $this->Tr->h('reservation/chargeUnit') ?>
                        </p>
                    </td>
                    <?php if ($requiredChargeBreakdownAll): ?>
                        <?= $this->element('User/Reservations/charge_breakdown', [
                            'reservationForms' => $continuousForm->getReservationForm(),
                        ]) ?>
                    <?php endif; ?>
                </tr>
                <?php if ($continuousForm->requiresPayments()): ?>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                <?= $this->Tr->h('reservation/paymentMethod') ?>
                                <?php if (count($valueOptions['paymentMethodId']) > 1): ?>
                                    <?= $this->element('User/Common/form/require') ?>
                                <?php endif; ?>
                            </div>
                        </th>
                        <td>
                            <div class="cmn-txt">
                                <?php if (count($valueOptions['paymentMethodId']) > 1): ?>
                                    <?= $this->Template->radio('payment_method_id', [
                                        'type' => 'radio',
                                        'class' => ['js_payment_method'],
                                        'options' => $valueOptions['paymentMethodId'],
                                    ]) ?>
                                <?php else: ?>
                                    <?php $paymentMethodIds = array_keys($valueOptions['paymentMethodId']); ?>
                                    <?= h($this->Master->getPaymentMethodName(reset($paymentMethodIds))) ?>
                                    <div class="hidden">
                                        <?= $this->Template->radio('payment_method_id', [
                                            'type' => 'radio',
                                            'class' => ['js_payment_method'],
                                            'options' => $valueOptions['paymentMethodId'],
                                            'value' => reset($paymentMethodIds),
                                        ]) ?>
                                    </div>
                                <?php endif; ?>
                                <?php foreach (array_keys($valueOptions['paymentMethodId']) as $paymentMethodId): ?>
                                    <?php if (((string)$this->Master->getPaymentMethodType((int)$paymentMethodId)) === ((string)PaymentMethod::TYPE_CARD)): ?>
                                        <input type="hidden" class="js_payment_method_card"
                                               value="<?= h($paymentMethodId) ?>"/>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </fieldset>
        <?php if ($this->Payment->usesApiPayment()): ?>
            <?= $this->element('User/Common/fieldset/payment', [
                'paymentTokenName' => 'payment_tokens',
                'paymentTokenNumber' => $continuousForm->getPaymentTokenNumber(),
            ]) ?>
        <?php endif; ?>
        <?= $this->Form->error('payment_tokens') ?>
    <?php endif; ?>
    <?= $this->element('User/Reservations/sctl', [
        'reservationForm' => $continuousForm,
    ]) ?>
    <fieldset class="input-info">
        <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
            <?php if (!$continuousForm->isContinuous()): ?>
                <?= $this->Html->link(
                    $this->Tr->t('reservationsAdd/backBtn'),
                    [
                        'prefix' => 'User',
                        'controller' => 'Reservations',
                        'action' => 'add',
                        '?' => [
                                'key' => $continuousForm->getContinuousParameter('key'),
                            ] + $this->Configure->read('Setting.formInput.backQuery'),
                    ],
                    [
                        'class' => ['cmn-btn', 'is-reset', 'is-gray'],
                    ]
                ) ?>
                <?= $this->Form->button($this->Tr->t('reservationsAdd/finishBtn'), [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue']
                ]) ?>
            <?php else: ?>
                <?= $this->Form->button($this->Tr->t('reservationsAdd/continuousDeleteAllBtn'), [
                    'type' => 'button',
                    'class' => ['cmn-btn', 'is-gray', 'js_reservation_remove_all_continuous'],
                ]) ?>
                <?= $this->Form->button($this->Tr->t('reservationsAdd/finishBtn'), [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue']
                ]) ?>
            <?php endif; ?>
        </p>
    </fieldset>
    <div class="hidden">
        <input type="hidden" class="js_reservation_current_continuous_key"
               value="<?= h($continuousForm->getContinuousParameter('key')) ?>"/>
    </div>
    <?= $this->Form->end() ?>
</section>
