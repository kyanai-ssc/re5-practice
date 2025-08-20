<?php

use App\Model\Entity\AdminListItem;

?>
<?php foreach ($listItems as $item): ?>
    <?php if (is_object($item)): ?>
        <td class="<?= h($item->getInputTypeItem()->getListClass()) ?>">
            <?= $this->InputType->renderListItem($item, $options) ?>
        </td>
    <?php else: ?>
        <?php if (((string)$item) === ((string)AdminListItem::ITEM_USER_ID)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?php if (isset($options['user'])): ?>
                    <?= h($options['user']->id) ?>
                <?php elseif (isset($options['reservation'])): ?>
                    <?= h($options['reservation']->user_id) ?>
                <?php endif; ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_GUEST_FLG)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?php if (isset($options['user'])): ?>
                    <?= h($this->Configure->read('Master.user.guestFlg.' . $options['user']->guest_flg)) ?>
                <?php endif; ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_WITHDRAWAL_FLG)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?php if (isset($options['user'])): ?>
                    <?= h($this->Configure->read('Master.user.withdrawalFlg.' . $options['user']->withdrawal_flg)) ?>
                <?php elseif (isset($options['reservation'])): ?>
                    <span class="warning">会員削除済み</span>
                <?php endif; ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_USER_INS_TIMESTAMP)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?php if (isset($options['user'])): ?>
                    <?= h($this->Template->displayDayAndWeek($options['user']->created, 'H:i:s')) ?>
                <?php endif; ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_RESERVATION_ID)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?= h($options['reservation']->id) ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_RESERVATION_STATUS_ID)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?= $this->element('Admin/Reservations/status', [
                    'reservationId' => $options['reservation']->get('id'),
                    'reservationStatusId' => $options['reservation']->get('reservation_status_id'),
                    'canChangeStatus' => $options['reservation']->canEdit(),
                ]) ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_USAGE_TIMESTAMP)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?= h($this->Template->displayDayAndWeek($options['reservation']->usage_timestamp_from, 'H:i')) ?>～
                <?= h($this->Template->displayDayAndWeek($options['reservation']->usage_timestamp_to, 'H:i')) ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_EVENT_PLANS)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?php if ($options['reservation']->has('reservation_event_plans')): ?>
                    <?php foreach ($options['reservation']->reservation_event_plans as $reservationEventPlan): ?>
                        <div>
                            <?= h($reservationEventPlan->event_plan->name) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_CHARGE)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?= h($options['reservation']->charge) ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_PAYMENT_METHOD)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?php if ($options['reservation']->has('payment_method_id')): ?>
                    <?= h($this->Master->getPaymentMethodName($options['reservation']->payment_method_id)) ?>
                <?php endif; ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_PAYMENT_STATUS)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?php if ($options['reservation']->has('payment_status_id')): ?>
                    <?= h($this->Master->getPaymentStatusName($options['reservation']->payment_status_id)) ?>
                <?php endif; ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_RESERVATION_PAYMENT_STATUS)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?php if ($options['reservation']->has('reservation_payments')): ?>
                    <?php foreach ($options['reservation']->reservation_payments as $reservationPayment): ?>
                        <div>
                            <?php $value = $this->Master->getReservationPaymentStatus($reservationPayment->status, $reservationPayment->payment_limit); ?>
                            <?= h($this->Configure->read('Master.payment.reservationPaymentStatus.' . $value)) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_RESERVATION_INS_TIMESTAMP)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?= h($this->Template->displayDayAndWeek($options['reservation']->created, 'H:i:s')) ?>
            </td>
        <?php elseif (((string)$item) === ((string)AdminListItem::ITEM_RECEPTION_STATUS_ID)): ?>
            <td class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
                <?= h($this->Master->getReceptionStatusName($options['reservation']->reception_status_id)) ?>
            </td>
        <?php endif; ?>
    <?php endif; ?>
<?php endforeach; ?>
