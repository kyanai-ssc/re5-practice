    <div class="js_form_item_detail_container_<?= h($inputType) ?>_<?= h($formItemDetailIndex) ?> multiAdd-wrap">
        <div class="ttl-detail-show">
            <span class="cmn-txt">基本情報</span>
            <button type="button" class="showBtn"></button>
            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"></use></svg>', [
                'type' => 'button',
                'title' => '削除',
                'class' => ['js_remove_input', 'btn-input', 'is-delete', 'formM'],
                'data-selector' => '.js_form_item_detail_container_' . $inputType . '_' . $formItemDetailIndex,
                'data-context' => '.js_form_item_detail_container_' . $inputType,
                'escapeTitle' => false,
            ]) ?>
        </div>
        <div class="showWrap">
            <?= $this->element('InputType/FormItemDetails/fieldset_text', [
                'inputType' => $inputType,
                'formItem' => $formItem,
                'valueOptions' => $valueOptions,
                'formItemDetailIndex' => $formItemDetailIndex,
                'formItemDetail' => $formItemDetail,
            ]) ?>
        </div>
    </div>

