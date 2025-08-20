<?php if (isset($listValue)): ?>
    <?php $this->start('event_tag_display'); ?>
    <?php foreach ($listValue as $tagGroup): ?>【<?= h($tagGroup['name']) ?>】<?= h(implode('・', $tagGroup['tag'])) ?>

    <?php endforeach; ?>
    <?php $this->end('event_tag_display'); ?>
    <?= $this->Text->truncate($this->Text->nl2format($this->fetch('event_tag_display')), '30', ['tooltip' => true, 'escape' => true]) ?>
<?php endif; ?>
