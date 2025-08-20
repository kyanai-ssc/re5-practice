<?= $this->Form->control('limit', array_merge_recursive([
    'type' => 'select',
    'label' => false,
    'options' => $valueOptions['limit'],
    'value' => $this->Paginator->param('perPage'),
    'class' => ['js_change_search_limit', 'select'],
], (array)$options)) ?>

