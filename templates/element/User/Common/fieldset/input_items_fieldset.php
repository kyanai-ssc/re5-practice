<?php
use App\Model\Entity\FormGroup;
?>
<?php foreach ($formGroups as $formGroup): ?>
    <?php $this->start('inputItemsFieldset'); ?>
        <?php foreach ($formGroup->get('form_items') as $formItem): ?>
            <?= $this->InputType->renderFieldsetItem($formItem, $options) ?>
        <?php endforeach; ?>
    <?php $this->end('inputItemsFieldset'); ?>
    <?php if (trim($this->fetch('inputItemsFieldset')) !== ''): ?>
        <?php if (((string)$formGroup->get('name_display_flg')) === ((string)FormGroup::NAME_DISPLAY_FLG_ON)): ?>
            <h3 class="ttl-sec mgt-20"><?= h($formGroup['name']) ?></h3>
        <?php else : ?>
            <div class="mgt-20"></div>
        <?php endif; ?>
        <fieldset class="input-info">
            <table class="input-box">
                <?= $this->fetch('inputItemsFieldset') ?>
            </table>
        </fieldset>
    <?php endif; ?>
<?php endforeach; ?>
