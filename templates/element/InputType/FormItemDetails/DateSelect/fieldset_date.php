<?php

use App\Model\Entity\FormItemDetail;

?>
<div class="js_form_item_detail_container_<?= h($inputType) ?>_<?= h($formItemDetailIndex) ?>">
    <input type="hidden" class="js_form_item_detail_index" value="<?= h($formItemDetailIndex) ?>"/>
    <?= $this->Form->hidden('form_item_details.' . $formItemDetailIndex . '.id') ?>
    <dl class="contents-item d-flex">
        <dt>期間設定</dt>
        <dd>
            <div class="d-flex">
                <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.date_lower_limit', [
                    'type' => 'text',
                    'class' => ['js-datepicker-type-range-start']
                ]) ?>
                <span class="txt mgl-10 mgr-10">から</span>
            </div>
            <div class="d-flex mgt-20">
                <?= $this->Template->radio('form_item_details.' . $formItemDetailIndex . '.date_upper_limit_type', [
                    'type' => 'radio',
                    'label' => false,
                    'options' => $valueOptions['formItemDetails']['dateUpperLimitType'],
                    'class' => ['js_form_item_date_upper_limit_type'],
                ]) ?>
            </div>
            <div>
                <div
                    class="<?php if (!isset($formItemDetail) || ((string)$formItemDetail->date_upper_limit_type) !== ((string)FormItemDetail::DATE_UPPER_LIMIT_TYPE_ABSOLUTE)): ?>hidden <?php endif; ?>js_form_item_date_upper_limit js_form_item_date_upper_limit_<?= h(FormItemDetail::DATE_UPPER_LIMIT_TYPE_ABSOLUTE) ?>">
                    <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.date_upper_limit_absolute', [
                        'type' => 'text',
                        'class' => ['js-datepicker-type-range-end']
                    ]) ?>
                </div>
                <div class="<?php if (!isset($formItemDetail) || ((string)$formItemDetail->date_upper_limit_type) !== ((string)FormItemDetail::DATE_UPPER_LIMIT_TYPE_RELATIVE)): ?>hidden <?php endif; ?>js_form_item_date_upper_limit js_form_item_date_upper_limit_<?= h(FormItemDetail::DATE_UPPER_LIMIT_TYPE_RELATIVE) ?>">
                    <span class="txt mgl-10">表示日の</span>
                    <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.date_upper_limit_relative', [
                        'type' => 'text',
                        'class' => ['textbox_w70']
                    ]) ?>
                    <span class="txt mgl-10">年後まで</span>
                </div>
            </div>
        </dd>
    </dl>
    <dl class="contents-item d-flex">
        <dt>
            初期表示日
        </dt>
        <dd>
            <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.date_default', [
                'type' => 'text',
                'class' => ['js-datepicker'],
            ]) ?>
        </dd>
    </dl>
</div>
