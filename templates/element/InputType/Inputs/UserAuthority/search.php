<?php $this->start('inputTypeUserAuthoritySearch');

use App\Model\Entity\UserAuthority; ?>
    <td>
        <div class="multi-select-wrap">
            <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey(), [
                'type' => 'select',
                'multiple' => true,
                'label' => false,
                'options' => $formItem->getInputTypeItem()->getValueOptions(),
                'class' => ['select', 'multi-select','js_multiple_select'],
                'data-select-all-text' => $this->Configure->read('Master.userAuthority.selectAll.' . UserAuthority::SELECT_ALL),
                'empty' => false,
            ]) ?>
        </div>
    </td>
<?php $this->end('inputTypeUserAuthoritySearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeUserAuthoritySearch'),
]) ?>
