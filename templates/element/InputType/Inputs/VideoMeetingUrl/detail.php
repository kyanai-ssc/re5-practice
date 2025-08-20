<?php if (isset($options['mode']) && ($options['mode'] === 'detail' || $options['mode'] === 'guestDetail')): ?>
    <?php $this->assign('inputTypeVideoMeetingUrlDetail', null); ?>
    <?php if (isset($detailValue) && $detailValue !== ''): ?>
        <?php $this->start('inputTypeVideoMeetingUrlDetail'); ?>
            <?= $this->Html->link($detailValue, $detailValue, [
                'target' => '_blank',
            ]) ?>
        <?php $this->end('inputTypeVideoMeetingUrlDetail'); ?>
    <?php endif; ?>
    <?= $this->element('InputType/Inputs/detail', [
        'formItem' => $formItem,
        'detailValue' => $this->fetch('inputTypeVideoMeetingUrlDetail'),
        'escape' => false,
        'options' => $options,
    ]) ?>
<?php endif; ?>
