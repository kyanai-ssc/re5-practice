<div class="-body">
    <div class="item-box-wrap">
        <div class="item-box">
            <div class="-menu js-menu-wrap">
                <ul>
                    <?php if (((string)$formItem->default_flg) !== ((string)$this->Configure->read('Master.common.flg.on'))): ?>
                        <?php foreach ($this->InputType->getInputTypes() as $inputType => $inputTypeName): ?>
                            <?php if ($this->InputType->inputTypeManager($inputType)->canCreate($formType)): ?>
                                <li class="js_form_item_input_type_changer js-menu-btn<?php if ((string)$formItem->input_type === (string)$inputType): ?> is-open <?php endif; ?>"
                                    data-input-type="<?= h($inputType) ?>">
                                    <?= h($inputTypeName) ?>
                                    <input type="hidden" class="js_form_item_input_type_data_<?= h($inputType) ?>"
                                           data-required="<?= h($this->InputType->inputTypeManager($inputType)->required()) ?>"
                                           data-can-select-required="<?= h($this->InputType->inputTypeManager($inputType)->canSelectRequired()) ?>"
                                           data-can-reservation-display="<?= h($this->InputType->inputTypeManager($inputType)->canReservationDisplay()) ?>"/>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="js_form_item_input_type_changer js-menu-btn is-open">
                            <?= $this->Configure->read('Master.form.inputType.' . $formItem->input_type) ?>
                            <input type="hidden" class="js_form_item_input_type_data_<?= h($formItem->input_type) ?>"
                                   data-required="<?= h($this->InputType->inputTypeManager($formItem->input_type)->required()) ?>"
                                   data-can-select-required="<?= h($this->InputType->inputTypeManager($formItem->input_type)->canSelectRequired()) ?>"
                                   data-can-reservation-display="<?= h($this->InputType->inputTypeManager($formItem->input_type)->canReservationDisplay()) ?>"
                                   data-has-form-item-details="<?= h($this->InputType->inputTypeManager($formItem->input_type)->hasFormItemDetails()) ?>"/>
                        </li>
                    <?php endif; ?>
                </ul>
                <?= $this->Form->hidden('input_type') ?>
                <?= $this->Form->error('input_type') ?>
            </div>
            <div class="-contents js-tab-wrap js_form_item_tabs">
                <div class="tab-box">
                    <div class="-tab js-tab-btn is-open js_form_item_tab js_form_item_tab_base">
                        基本設定
                    </div>
                    <div class="-tab js-tab-btn js_form_item_tab js_form_item_tab_detail">
                        詳細設定
                    </div>
                </div>
                <div class="contents-box baseEdit is-open" id="js_form_item_tabs_base">
                    <dl class="contents-item d-flex">
                        <dt>項目名<?= $this->Template->isRequire('name') ?></dt>
                        <dd>
                            <?= $this->Form->control('name', [
                                'type' => 'text',
                            ]) ?>
                        </dd>
                    </dl>
                    <dl class="contents-item d-flex">
                        <dt>必須<?= $this->Template->isRequire('required_flg') ?></dt>
                        <dd class="js_form_item_required js_form_item_select_required_on btn-inline">
                            <?= $this->Template->radio('required_flg', [
                                'type' => 'radio',
                                'options' => $valueOptions['requiredFlg'],
                                'default' => '',
                            ]) ?>
                        </dd>
                        <dd class="js_form_item_required js_form_item_select_required_off js_form_item_required_on btn-inline">
                            <?= $this->Configure->read('Master.form.requiredFlg.' . $this->Configure->read('Master.common.flg.on')) ?>
                            <?= $this->Form->hidden('required_flg', [
                                'value' => $this->Configure->read('Master.common.flg.on'),
                            ]) ?>
                            <?= $this->Form->error('required_flg') ?>
                        </dd>
                        <dd class="js_form_item_required js_form_item_select_required_off js_form_item_required_off btn-inline">
                            <?= $this->Configure->read('Master.form.requiredFlg.' . $this->Configure->read('Master.common.flg.off')) ?>
                            <?= $this->Form->hidden('required_flg', [
                                'value' => $this->Configure->read('Master.common.flg.off'),
                            ]) ?>
                            <?= $this->Form->error('required_flg') ?>
                        </dd>
                    </dl>
                    <?php if ($this->ArrayUtility->inArray($formType, $this->Configure->read('Master.form.canReservationDisplayFormType'))): ?>
                        <dl class="contents-item d-flex">
                            <dt>
                                予約画面表示<?= $this->Template->isRequire('reservation_display_flg') ?>
                            </dt>
                            <dd class="js_form_item_can_reservation_display js_form_item_can_reservation_display_on btn-inline">
                                <?= $this->Template->radio('reservation_display_flg', [
                                    'type' => 'radio',
                                    'options' => $valueOptions['reservationDisplayFlg'],
                                    'default' => '',
                                ]) ?>
                            </dd>
                            <dd class="js_form_item_can_reservation_display js_form_item_can_reservation_display_off btn-inline">
                                <?= $this->Configure->read('Master.form.reservationDisplayFlg.' . $this->Configure->read('Master.common.flg.off')) ?>
                                <?= $this->Form->hidden('reservation_display_flg', [
                                    'value' => $this->Configure->read('Master.common.flg.off'),
                                ]) ?>
                                <?= $this->Form->error('reservation_display_flg') ?>
                            </dd>
                        </dl>
                    <?php else: ?>
                        <?= $this->Form->hidden('reservation_display_flg', [
                            'value' => $this->Configure->read('Master.common.flg.off'),
                        ]) ?>
                    <?php endif; ?>
                    <dl class="contents-item d-flex">
                        <dt>説明文<?= $this->Template->isRequire('description') ?></dt>
                        <dd>
                            <?= $this->Form->control('description', [
                                'type' => 'textarea',
                                'class' => ['wysiwyg']
                            ]) ?>
                        </dd>
                    </dl>
                </div>
                <div id="js_form_item_tabs_detail" class="contents-box detailEdit">
                    <?php if (((string)$formItem->default_flg) !== ((string)$this->Configure->read('Master.common.flg.on'))): ?>
                        <?php foreach ($this->InputType->getInputTypes() as $inputType => $inputTypeName): ?>
                            <?php if ($this->InputType->inputTypeManager($inputType)->hasFormItemDetailTab()): ?>
                                <?php if (((string)$formItem->input_type) === ((string)$inputType)): ?>
                                    <div class="js_form_item_detail js_form_item_detail_<?= h($inputType) ?>">
                                        <?= $this->InputType->renderFormItemDetailTab($inputType, true) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="hidden js_form_item_detail js_form_item_detail_<?= h($inputType) ?>">
                                        <?= $this->InputType->renderFormItemDetailTab($inputType, false) ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php if ($this->InputType->inputTypeManager($formItem->input_type)->hasFormItemDetailTab()): ?>
                            <div class="js_form_item_detail js_form_item_detail_<?= h($formItem->input_type) ?>">
                                <?= $this->InputType->renderFormItemDetailTab($formItem->input_type, true) ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
