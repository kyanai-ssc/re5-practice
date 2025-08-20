<?php $this->start('inputTypeUsageDateDetail'); ?>
<span class="txt fwb"><?= $this->Template->displayDayAndWeek($detailValue) ?></span>
<?php if (isset($options['mode']) && ($options['mode'] === 'add' || $options['mode'] === 'edit')): ?>
    <?= $this->element('InputType/Inputs/UsageDate/select_calendar', [
        'reservation' => $options['reservation'],
        'adminFlg' => $formItem->getInputTypeItem()->isAdmin(),
    ]) ?>
<?php endif; ?>
<?php $this->end('inputTypeUsageDateDetail'); ?>
<?= $this->element('InputType/Inputs/detail', [
    'formItem' => $formItem,
    'detailValue' => $this->fetch('inputTypeUsageDateDetail'),
    'escape' => false,
    'divAddClass' => 'cmnInput d-flex fwb',
    'options' => $options,
]) ?>
