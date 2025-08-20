
<?= $this->Template->checkbox('target',
    [
        'type' => 'checkbox',
        'value' => $valueOptions['listCheckId']['check'],
        'class' => ['js_search_check', 'btn-tool', 'cmn-check'],
        'label' => ['class' => 'cmn-check btn-tool no-txt-label', 'text' => '', 'title' => $check['targetLabel']],
        'hiddenField' => false,
        'checked' => $check['allChecked'],
    ]) ?>

<?= $this->Form->control('', [
    'type' => 'hidden',
    'class' => 'js_check_search_all_on',
    'value' => $valueOptions['listCheckId']['check'],
    'data-label' => $valueOptions['listCheck'][$valueOptions['listCheckId']['check']],
]); ?>

<?= $this->Form->control('', [
    'type' => 'hidden',
    'class' => 'js_check_search_all_off',
    'value' => $valueOptions['listCheckId']['remove'],
    'data-label' => $valueOptions['listCheck'][$valueOptions['listCheckId']['remove']],
]); ?>
