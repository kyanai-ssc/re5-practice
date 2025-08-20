<?php if (isset($detailValue)): ?>
    <?php $this->start('inputTypeTagDetail'); ?>
        <?php foreach ($detailValue as $tagGroup): ?>
            <p>[<?= h($tagGroup['name']) ?>]</p>
            <p><?= h(implode('、', $tagGroup['tag'])) ?></p>
        <?php endforeach; ?>
    <?php $this->end('inputTypeTagDetail'); ?>
    <?= $this->element('InputType/Inputs/detail', [
        'formItem' => $formItem,
        'detailValue' => $this->fetch('inputTypeTagDetail'),
        'escape' => false,
        'options' => $options,
    ]) ?>
<?php endif; ?>
