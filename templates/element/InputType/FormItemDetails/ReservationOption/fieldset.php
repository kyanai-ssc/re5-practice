<div class="js_form_item_detail_container_<?= h($inputType) ?>">
    <?= $this->Form->hidden('form_item_option_group.id') ?>
    <dl class="contents-item d-flex">
        <dt>選択タイプ</dt>
        <dd>
            <?php if (isset($formItem->id) && $this->InputType->inputTypeManager($inputType)->isReserve($formItem->id)) : ?>
                <?= h($valueOptions['formItemOptionGroups']['selectType'][$formItem->form_item_option_group->select_type]) ?>
                <?= $this->Form->hidden('form_item_option_group.select_type') ?>
            <?php else: ?>
                <?= $this->Template->radio('form_item_option_group.select_type', [
                    'type' => 'radio',
                    'label' => '',
                    'options' => $valueOptions['formItemOptionGroups']['selectType'],
                ]) ?>
            <?php endif; ?>
        </dd>
    </dl>
    <?= $this->FormError->errorWithoutNested('form_item_option_group') ?>
    <?= $this->FormError->errorWithoutNested('form_item_option_group.form_item_options') ?>
    <div class="js_form_item_option_container_<?= h($inputType) ?>">
        <?php if (isset($formItem->form_item_option_group->form_item_options)): ?>
            <?php foreach ($formItem->form_item_option_group->form_item_options as $formItemOptionIndex => $formItemOption): ?>
                <?= $this->element('InputType/FormItemDetails/ReservationOption/fieldset_option', [
                    'inputType' => $inputType,
                    'formItem' => $formItem,
                    'valueOptions' => $valueOptions,
                    'formItemOptionIndex' => $formItemOptionIndex,
                    'formItemOption' => $formItemOption,
                ]) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?= $this->Form->button('オプションを追加', [
        'type' => 'button',
        'class' => ['js_add_input', 'cmn-btn', 'is-addCell', 'mgt-20', 'mgb-20'],
        'data-container' => '.js_form_item_option_container_' . $inputType,
        'data-html' => $this->element('InputType/FormItemDetails/ReservationOption/fieldset_option', [
            'inputType' => $inputType,
            'formItem' => $formItem,
            'valueOptions' => $valueOptions,
            'formItemOptionIndex' => '%FORM_ITEM_OPTION_INDEX%',
            'formItemOption' => null,
        ]),
        'data-index-element' => '.js_form_item_option_index',
        'data-index-replace' => '%FORM_ITEM_OPTION_INDEX%',
    ]) ?>
</div>
