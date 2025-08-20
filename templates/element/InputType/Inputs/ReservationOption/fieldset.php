<?php $this->start('inputTypeReservationOptionFieldset'); ?>
    <div class="cmnInput">
        <?php if (!$formItem->get('form_item_option_group')->isMultipleType()): ?>
            <?= $this->Form->hidden($formItem->getInputTypeItem()->getFieldsetInputKey() . '.option_id', [
                'value' => '',
            ]) ?>
            <?php if ($formItem->getInputTypeItem()->getEmptyOptionValue() !== false): ?>
                <div>
                    <?= $this->Template->radio($formItem->getInputTypeItem()->getFieldsetInputKey() . '.option_id', [
                        'type' => 'radio',
                        'options' => ['' => $formItem->getInputTypeItem()->getEmptyOptionValue()],
                        'hiddenField' => false,
                        'class' => ['js_reservation_option_check'],
                        'templates' => [
                            'error' => '',
                        ],
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($formItem->getInputTypeItem()->getOptionValueOptions($options['event']->get('id'), $options['reservation']->get('usage_timestamp_from')) as $optionId => $optionName): ?>
                <div class="d-flex">
                    <span>
                        <?= $this->Template->radio($formItem->getInputTypeItem()->getFieldsetInputKey() . '.option_id', [
                            'type' => 'radio',
                            'options' => [$optionId => $optionName],
                            'hiddenField' => false,
                            'class' => ['js_reservation_option_check'],
                            'templates' => [
                                'error' => '',
                            ],
                        ]) ?>
                    </span>
                    <?php $hideNumber = false; ?>
                    <?php if ($formItem->getInputTypeItem()->isFixedNumberValueOptions($optionId)): ?>
                        <?php $hideNumber = true; ?>
                    <?php endif; ?>
                    <span class="mgl-10<?php if ($hideNumber): ?> hidden<?php endif; ?>">
                        <?= $this->Form->hidden($formItem->getInputTypeItem()->getFieldsetInputKey() . '.reservation_options.value_' . $optionId . '.number', [
                            'value' => '',
                            'secure' => $this->Form::SECURE_SKIP,
                        ]) ?>
                        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '.reservation_options.value_' . $optionId . '.number', [
                            'type' => 'select',
                            'options' => $formItem->getInputTypeItem()->getNumberValueOptions($optionId),
                            'empty' => $formItem->getInputTypeItem()->getEmptyNumberValue($optionId),
                            'class' => array_merge(
                                ['select', 'js_reservation_option_number', 'js_reservation_option_number_' . $optionId],
                                $this->FormError->addFieldErrorClassWithoutNested('reservations.option_errors_' . $optionId)
                            ),
                        ]) ?>
                    </span>
                </div>
                <?= $this->FormError->errorWithoutNested('reservations.option_errors_' . $optionId) ?>

                <?php if(!empty($formItem->getInputTypeItem()->getOptionDescription($optionId))): ?>
                <div class="mgb-10">
                    <?= nl2br(h($formItem->getInputTypeItem()->getOptionDescription($optionId))) ?>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <?= $this->FormError->errorWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()) ?>
            <?= $this->FormError->errorWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey() . '.reservation_options') ?>
            <?php if ((string)$this->FormError->errorWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey() . '.reservation_options') === ''): ?>
                <?= $this->Form->error($formItem->getInputTypeItem()->getFieldsetInputKey() . '.option_id') ?>
            <?php endif; ?>
        <?php else: ?>
            <?php foreach ($formItem->getInputTypeItem()->getOptionValueOptions($options['event']->get('id'), $options['reservation']->get('usage_timestamp_from')) as $optionId => $optionName): ?>
                <div class="d-flex">
                    <span><?= h($optionName) ?></span>
                    <?php $hideNumber = false; ?>
                    <?php if ($formItem->getInputTypeItem()->isFixedNumberValueOptions($optionId)): ?>
                        <?php $hideNumber = true; ?>
                    <?php endif; ?>
                    <span class="mgl-10<?php if ($hideNumber): ?> hidden<?php endif; ?>">
                        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '.reservation_options.value_' . $optionId . '.number', [
                            'type' => 'select',
                            'options' => $formItem->getInputTypeItem()->getNumberValueOptions($optionId),
                            'empty' => $formItem->getInputTypeItem()->getEmptyNumberValue($optionId),
                            'class' => array_merge(
                                ['select'],
                                $this->FormError->addFieldErrorClassWithoutNested('reservations.option_errors_' . $optionId)
                            ),
                        ]) ?>
                    </span>
                </div>
                <?= $this->FormError->errorWithoutNested('reservations.option_errors_' . $optionId) ?>
                <?php if(!empty($formItem->getInputTypeItem()->getOptionDescription($optionId))): ?>
                    <div class="mgb-10">
                        <?= nl2br(h($formItem->getInputTypeItem()->getOptionDescription($optionId))) ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <?= $this->FormError->errorWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()) ?>
            <?= $this->FormError->errorWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey() . '.reservation_options') ?>
        <?php endif; ?>
    </div>
<?php $this->end('inputTypeReservationOptionFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeReservationOptionFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
