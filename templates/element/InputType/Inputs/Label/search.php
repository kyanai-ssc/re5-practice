<?php $this->start('inputTypeLabelSearch'); ?>
    <td>
        <?= $this->Label->renderSelect([
            'type' => $this->Configure->read('Master.label.type.reservations'),
        ]) ?>
    </td>
<?php $this->end('inputTypeLabelSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeLabelSearch'),
]) ?>
