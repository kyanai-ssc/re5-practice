<div class="js_form_item_detail_container_<?= h($inputType) ?>">
    <?php if (isset($formItem->form_item_details) && count($formItem->form_item_details) > 0): ?>
        <?php foreach ($formItem->form_item_details as $formItemDetailIndex => $formItemDetail): ?>
            <?= $this->element($formItemDetailTemplate, [
                'inputType' => $inputType,
                'formItem' => $formItem,
                'valueOptions' => $valueOptions,
                'formItemDetailIndex' => $formItemDetailIndex,
                'formItemDetail' => $formItemDetail,
            ]) ?>
        <?php endforeach; ?>
    <?php else: ?>
        <?php if (!is_null($this->InputType->inputTypeManager($inputType)->getFormItemDetailNumber())) : ?>
            <?= $this->element($formItemDetailTemplate, [
                'inputType' => $inputType,
                'formItem' => $formItem,
                'valueOptions' => $valueOptions,
                'formItemDetailIndex' => 0,
                'formItemDetail' => null,
            ]) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php if (is_null($this->InputType->inputTypeManager($inputType)->getFormItemDetailNumber())) : ?>
    <?= $this->Form->button('テキストボックスを追加', [
        'type' => 'button',
        'class' => ['js_add_input', 'cmn-btn', 'is-addCell', 'mgt-20', 'mgb-20'],
        'data-container' => '.js_form_item_detail_container_' . $inputType,
        'data-html' => $this->element($formItemDetailTemplate, [
            'inputType' => $inputType,
            'formItem' => $formItem,
            'valueOptions' => $valueOptions,
            'formItemDetailIndex' => '%FORM_ITEM_DETAIL_INDEX%',
            'formItemDetail' => null,
        ]),
        'data-index-element' => '.js_form_item_detail_index',
        'data-index-replace' => '%FORM_ITEM_DETAIL_INDEX%',
    ]) ?>
    <?= $this->FormError->errorWithoutNested('form_item_details') ?>
<?php endif; ?>
