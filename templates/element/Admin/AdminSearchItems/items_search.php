<?php
use App\Model\Entity\AdminSearchItem;
?>
<?php foreach ($searchItems as $formType => $items): ?>
    <div class="panel-show-set mgt-10">
        <fieldset>
            <legend class="ttl-search mgb-20">
                <span class="ttl-s"><?= h($this->Configure->read('Master.form.formType.' . $formType)) ?></span>
            </legend>
            <table class="input-box">
                <?php foreach ($items as $item): ?>
                    <?php if (is_object($item)): ?>
                        <?= $this->InputType->renderSearchItem($item) ?>
                    <?php else: ?>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">
                                    <?= h($this->Configure->read('Master.adminSearchItems.items.' . $item)) ?>
                                </div>
                            </th>
                            <?php if (((string)$item) === ((string)AdminSearchItem::ITEM_USER_ID)): ?>
                                <td>
                                    <?= $this->Form->control($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'text',
                                        'label' => false,
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_GUEST_FLG)): ?>
                                <td>
                                    <?= $this->Template->checkbox($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'multicheckbox',
                                        'label' => false,
                                        'options' => $searchForm->getFieldValueOptions('guestFlg'),
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_WITHDRAWAL_FLG)): ?>
                                <td>
                                    <?= $this->Template->checkbox($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'multicheckbox',
                                        'label' => false,
                                        'options' => $searchForm->getFieldValueOptions('withdrawalFlg'),
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_RESERVATION_ID)): ?>
                                <td>
                                    <?= $this->Form->control($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'text',
                                        'label' => false,
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_EVENT_ID)): ?>
                                <td>
                                    <?= $this->Form->control($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'text',
                                        'label' => false,
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_RESERVATION_STATUS_ID)): ?>
                                <td class="btn-inline">
                                    <?= $this->Template->checkbox($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'multicheckbox',
                                        'label' => false,
                                        'options' => $searchForm->getFieldValueOptions('reservationStatusId'),
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_RECEPTION_STATUS_ID)): ?>
                                <td class="btn-inline">
                                    <?= $this->Template->checkbox($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'multicheckbox',
                                        'label' => false,
                                        'options' => $searchForm->getFieldValueOptions('receptionStatusId'),
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_USAGE_TIMESTAMP)): ?>
                                <td class="d-flex">
                                    <?= $this->Form->control($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item) . '.from', [
                                        'type' => 'text',
                                        'class' => ['js-datepicker-time'],
                                    ]) ?>
                                    <span class="txt mgl-10 mgr-10">～</span>
                                    <?= $this->Form->control($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item) . '.to', [
                                        'type' => 'text',
                                        'class' => ['js-datepicker-time'],
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_PAYMENT_METHOD)): ?>
                                <td class="btn-inline">
                                    <?= $this->Template->checkbox($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'multicheckbox',
                                        'label' => false,
                                        'options' => $searchForm->getFieldValueOptions('paymentMethod'),
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_PAYMENT_STATUS)): ?>
                                <td class="btn-inline">
                                    <?= $this->Template->checkbox($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'multicheckbox',
                                        'label' => false,
                                        'options' => $searchForm->getFieldValueOptions('paymentStatus'),
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS)): ?>
                                <td class="btn-inline">
                                    <?= $this->Template->checkbox($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'multicheckbox',
                                        'label' => false,
                                        'options' => $searchForm->getFieldValueOptions('reservationPaymentStatus'),
                                    ]) ?>
                                </td>
                                <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS)): ?>
                                <td class="btn-inline">
                                    <?= $this->Template->checkbox($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'multicheckbox',
                                        'label' => false,
                                        'options' => $searchForm->getFieldValueOptions('reservationSmartLockStatus'),
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_USER_INS_TIMESTAMP)
                                || ((string)$item) === ((string)AdminSearchItem::ITEM_RESERVATION_INS_TIMESTAMP)
                            ): ?>
                                <td class="d-flex">
                                    <?= $this->Form->control($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item) . '.from', [
                                        'type' => 'text',
                                        'class' => ['js-datepicker'],
                                    ]) ?>
                                    <span class="txt mgl-10 mgr-10">～</span>
                                    <?= $this->Form->control($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item) . '.to', [
                                        'type' => 'text',
                                        'class' => ['js-datepicker'],
                                    ]) ?>
                                </td>
                            <?php elseif (((string)$item) === ((string)AdminSearchItem::ITEM_RESERVATION_VIDEO_METTING)): ?>
                                <td class="btn-inline">
                                    <?= $this->Template->checkbox($this->Configure->read('Master.adminSearchItems.itemsSearchInputKey.' . $item), [
                                        'type' => 'multicheckbox',
                                        'label' => false,
                                        'options' => $searchForm->getFieldValueOptions('reservationVideoMeeting'),
                                    ]) ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </fieldset>
    </div>
<?php endforeach; ?>
