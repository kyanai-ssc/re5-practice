<?= $this->element('InputType/Inputs/detail', [
    'formItem' => $formItem,
    'detailValue' => $detailValue . ((isset($options['event']) && $options['event'] instanceof \Cake\Datasource\EntityInterface) ? $options['event']->get('stock_unit') : ''),
    'escape' => true,
    'options' => $options,
]) ?>
