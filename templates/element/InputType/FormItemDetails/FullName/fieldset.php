<?php
$detailTypes = ['lastName', 'firstName'];
$formItemDetails = array_fill(0, 2, null);
if (isset($formItem->form_item_details)) {
    $formItemDetails = array_values($formItem->form_item_details);
}
?>
<div class="js_form_item_detail_container_<?= h($inputType) ?>">
    <?php foreach ($formItemDetails as $formItemDetailIndex => $formItemDetail): ?>
        <?= $this->element('InputType/FormItemDetails/FullName/fieldset_name', [
            'inputType' => $inputType,
            'formItem' => $formItem,
            'valueOptions' => $valueOptions,
            'formItemDetailIndex' => $formItemDetailIndex,
            'formItemDetail' => $formItemDetail,
            'detailType' => $detailTypes[$formItemDetailIndex],
        ]) ?>
    <?php endforeach; ?>
</div>
