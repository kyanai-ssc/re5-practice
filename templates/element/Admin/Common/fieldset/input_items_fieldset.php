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
        <fieldset>
            <?php if (((string)$formGroup->get('name_display_flg')) === ((string)FormGroup::NAME_DISPLAY_FLG_ON)): ?>
                <h3 class="ttl-sec mgt-30"><?= h($formGroup['name']) ?></h3>
            <?php endif; ?>
            <table class="input-box mgt-30">
                <?= $this->fetch('inputItemsFieldset') ?>
            </table>
        </fieldset>
    <?php endif; ?>
<?php endforeach; ?>
