<?= $this->Template->checkbox('id.', [
    'type' => 'checkbox',
    'value' => $checkId,
    'hiddenField' => false,
    'id' => 'id-' . $checkId,
    'class' => ['js_check_all_target', 'cmn-check', $check['allCheckClass']],
    'label' => ['class' => ['cmn-check', 'btn-tool', 'no-txt-label', $check['allCheckLabelClass']], 'text' => ''],
    'templates' => ['nestingLabel' => '{{hidden}}{{input}}<label{{attrs}}>{{text}}</label>',],
    'checked' => ($check['allChecked'] || array_search($checkId, $check['checkList'])),
    'disabled' => $check['allChecked'],
]) ?>
