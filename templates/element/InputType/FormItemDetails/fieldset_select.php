<div class="js_form_item_detail_container_<?= h($inputType) ?>_<?= h($formItemDetailIndex) ?>">
    <input type="hidden" class="js_form_item_detail_index" value="<?= h($formItemDetailIndex) ?>"/>
    <?= $this->Form->hidden('form_item_details.' . $formItemDetailIndex . '.id') ?>
    <dl class="contents-item d-flex">
        <dt>付加文言<br>（前）</dt>
        <dd>
            <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.front_word', [
                'type' => 'text',
            ]) ?>
        </dd>
    </dl>
    <dl class="contents-item d-flex">
        <dt>付加文言<br>（後）</dt>
        <dd>
            <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.back_word', [
                'type' => 'text',
            ]) ?>
        </dd>
    </dl>
    <?php if (!isset($formItem) || ((string)$formItem->default_flg) !== ((string)$this->Configure->read('Master.common.flg.on'))) : ?>
        <?= $this->Form->button('選択肢追加', [
            'type' => 'button',
            'class' => ['js_add_input', 'cmn-btn', 'is-addCell', 'mgt-20', 'contentsAddBtn'],
            'data-container' => '.js_form_item_choices_container_' . $inputType,
            'data-html' => $this->element('InputType/FormItemDetails/fieldset_select_choice', [
                'inputType' => $inputType,
                'formItem' => $formItem,
                'formItemChoiceIndex' => '%FORM_ITEM_CHOICE_INDEX%',
                'formItemChoice' => null,
            ]),
            'data-index-element' => '.js_form_item_choices_index',
            'data-index-replace' => '%FORM_ITEM_CHOICE_INDEX%',
        ]) ?>
    <?php endif; ?>
    <?= $this->FormError->errorWithoutNested('form_item_choices') ?>
    <div class="js_form_item_choices_container_<?= h($inputType) ?> contentsAdd-wrp mgt-20">
        <?php if (isset($formItem->form_item_choices)): ?>
            <?php foreach ($formItem->form_item_choices as $formItemChoiceIndex => $formItemChoice): ?>
                <?= $this->element('InputType/FormItemDetails/fieldset_select_choice', [
                    'inputType' => $inputType,
                    'formItem' => $formItem,
                    'formItemChoiceIndex' => $formItemChoiceIndex,
                    'formItemChoice' => $formItemChoice,
                ]) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
