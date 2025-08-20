<?php

use App\Model\Entity\Event;

?>
<?php $this->start('inputTypeReservationTimeFieldset'); ?>
    <div class="cmnInput">
        <?php if (((string)$options['event']->get('time_plan')) !== ((string)Event::PLAN_MULTIPLE)): ?>
            <?php if (!$formItem->getInputTypeItem()->isFixedUsageTimeValueOptions($options['event'])): ?>
                <?= $this->Form->control('reservations.usage_time', [
                    'type' => 'select',
                    'options' => $formItem->getInputTypeItem()->getUsageTimeValueOptions($options['event']),
                    'empty' => $formItem->getInputTypeItem()->hasUsageTimeEmptyValue(),
                    'default' => $formItem->getInputTypeItem()->getUsageTimeDefaultValue($options['event']),
                    'class' => ['select']
                ]) ?>
                <?php $this->assign('inputName', 'reservations.usage_time'); ?>
            <?php else: ?>
                <?php foreach ($formItem->getInputTypeItem()->getUsageTimeValueOptions($options['event']) as $key => $value): ?>
                    <?php if (((string)$options['event']->get('type')) === ((string)Event::TYPE_TIME)): ?>
                        <span><?= h($value) ?></span>
                    <?php endif; ?>
                    <?= $this->Form->hidden('reservations.usage_time', [
                        'value' => $key,
                    ]) ?>
                <?php endforeach; ?>
                <?php $this->assign('inputName', 'reservations.usage_time'); ?>
            <?php endif; ?>
            <?php if (!$formItem->getInputTypeItem()->isFixedUsageDayValueOptions($options['event'])): ?>
                <?= $this->Form->control('reservations.usage_day', [
                    'type' => 'select',
                    'options' => $formItem->getInputTypeItem()->getUsageDayValueOptions($options['event']),
                    'empty' => $formItem->getInputTypeItem()->hasUsageDayEmptyValue(),
                    'default' => $formItem->getInputTypeItem()->getUsageDayDefaultValue($options['event']),
                    'class' => ['select']
                ]) ?>
                <?php $this->assign('inputName', 'reservations.usage_day'); ?>
            <?php else: ?>
                <?php foreach ($formItem->getInputTypeItem()->getUsageDayValueOptions($options['event']) as $key => $value): ?>
                    <?php if (((string)$options['event']->get('type')) === ((string)Event::TYPE_DAY)): ?>
                        <span><?= h($value) ?></span>
                    <?php endif; ?>
                    <?= $this->Form->hidden('reservations.usage_day', [
                        'value' => $key,
                    ]) ?>
                <?php endforeach; ?>
                <?php $this->assign('inputName', 'reservations.usage_time'); ?>
            <?php endif; ?>
        <?php else: ?>
            <?php if (((string)$options['event']->get('multiple_time_plan_type')) === ((string)Event::MULTIPLE_TIME_PLAN_TYPE_SINGLE)): ?>
                <?= $this->Template->radio('reservations.plan_values.single', [
                    'type' => 'radio',
                    'options' => $formItem->getInputTypeItem()->getEventPlansValueOptions($options['event']),
                ]) ?>
                <?php $this->assign('inputName', 'reservations.plan_values'); ?>
            <?php endif; ?>
            <?php if (((string)$options['event']->get('multiple_time_plan_type')) === ((string)Event::MULTIPLE_TIME_PLAN_TYPE_MULTI)): ?>
                <?= $this->Template->checkbox('reservations.plan_values', [
                    'type' => 'multicheckbox',
                    'options' => $formItem->getInputTypeItem()->getEventPlansValueOptions($options['event']),
                ]) ?>
                <?php $this->assign('inputName', 'reservations.plan_values'); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php $this->end('inputTypeReservationTimeFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeReservationTimeFieldset'),
    'require' => ['inputName' => '', 'always' => true],
]) ?>
