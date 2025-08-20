<div class="js_form_item_detail_container_<?= h($inputType) ?>_<?= h($formItemDetailIndex) ?> multiAdd-wrap">
    <div class="ttl-detail-show">
        <span class="cmn-txt">
            <?php if ($detailType === 'lastName'): ?>
                姓
            <?php endif; ?>
            <?php if ($detailType === 'firstName'): ?>
                名
            <?php endif; ?>
        </span>
        <button type="button" class="showBtn"></button>
    </div>
    <div class="showWrap">
        <input type="hidden" class="js_form_item_detail_index" value="<?= h($formItemDetailIndex) ?>"/>
        <?= $this->Form->hidden('form_item_details.' . $formItemDetailIndex . '.id') ?>
        <dl class="contents-item d-flex">
            <dt>付加文言</dt>
            <dd>
                <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.front_word', [
                    'type' => 'text',
                    'placeholder' => $detailType,
                ]) ?>
            </dd>
        </dl>
        <dl class="contents-item d-flex">
            <dt>文字数制限</dt>
            <dd class="d-flex">
                <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.text_lower_limit', [
                    'type' => 'text',
                    'class' => ['textbox_w70']
                ]) ?>
                <span class="txt mgl-10 mgr-10">～</span>
                <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.text_upper_limit', [
                    'type' => 'text',
                    'class' => ['textbox_w70']
                ]) ?>
            </dd>
        </dl>
        <dl class="contents-item d-flex">
            <dt>入力変換</dt>
            <dd>
                <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.text_input_translate', [
                    'type' => 'select',
                    'label' => '入力変換',
                    'options' => $valueOptions['formItemDetails']['textInputTranslate'],
                    'empty' => true,
                    'class' => ['select']
                ]) ?>
            </dd>
        </dl>
        <dl class="contents-item d-flex">
            <dt>入力チェック</dt>
            <dd>
                <?= $this->Form->control('form_item_details.' . $formItemDetailIndex . '.text_input_check', [
                    'type' => 'select',
                    'label' => '入力',
                    'options' => $valueOptions['formItemDetails']['textInputCheck'],
                    'empty' => true,
                    'class' => ['select']
                ]) ?>
            </dd>
        </dl>
    </div>
</div>
