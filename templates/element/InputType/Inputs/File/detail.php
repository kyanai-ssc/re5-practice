<?php $this->assign('inputTypeFileDetail', null); ?>
<?php $this->start('inputTypeFileDetail'); ?>
    <div>
        <?php if ($formItem->getInputTypeItem()->hasTempFile()): ?>
            <div>
                <?= $this->Html->link(
                    $formItem->getInputTypeItem()->getAttachedFileName(),
                    $formItem->getInputTypeItem()->getTempFileUrl()
                ); ?>
            </div>
        <?php elseif ($formItem->getInputTypeItem()->hasSavedFile() && !$formItem->getInputTypeItem()->isDeleting()): ?>
            <div>
                <?= $this->Html->link(
                    $formItem->getInputTypeItem()->getAttachedFileName(),
                    $formItem->getInputTypeItem()->getSavedFileUrl()
                    );
                ?>
            </div>
        <?php endif; ?>
    </div>
<?php $this->end('inputTypeFileDetail'); ?>
<?= $this->element('InputType/Inputs/detail', [
    'formItem' => $formItem,
    'detailValue' => $this->fetch('inputTypeFileDetail'),
    'escape' => false,
    'options' => $options,
]) ?>
