<?php $this->start('ajax_html'); ?>
    <?php if (!$finish): ?>
        <?= $this->Form->create($formItem, [
            'type' => 'post',
            'url' => '.',
            'idPrefix' => 'form-items-add',
            'novalidate' => true,
            'class' => ['js_submit_once js_no_submit js_form_item_add_submit'],
            'data-form-type' => $formType,
        ]) ?>
            <?= $this->element('Admin/FormItems/fieldset', [
                'formType' => $formType,
                'formItem' => $formItem,
                'valueOptions' => $valueOptions,
                'mode' => 'add',
            ]) ?>
        <div class="btn-box">
        <?= $this->Form->button('項目を追加', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
        </div>
        <?= $this->Form->end() ?>
    <?php else: ?>
        <?= $this->element('Admin/FormGroups/list_item', [
            'formType' => $formType,
            'formItem' => $formItem,
            'formGroupIndex' => '%FORM_GROUP_INDEX%',
            'formItemIndex' => '%FORM_ITEM_INDEX%',
        ]) ?>
    <?php endif; ?>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'finish' => $finish,
    'html' => $this->fetch('ajax_html'),
]) ?>
