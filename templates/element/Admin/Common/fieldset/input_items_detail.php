<?php
use App\Model\Entity\FormGroup;
?>
<?php foreach ($formGroups as $formGroup): ?>
    <?php $this->start('inputItemsDetail'); ?>
        <?php foreach ($formGroup->get('form_items') as $formItem): ?>
            <?= $this->InputType->renderDetailItem($formItem, $options) ?>
        <?php endforeach; ?>
    <?php $this->end('inputItemsDetail'); ?>
    <?php if (trim($this->fetch('inputItemsDetail')) !== ''): ?>
        <fieldset>
            <?php if (((string)$formGroup->get('name_display_flg')) === ((string)FormGroup::NAME_DISPLAY_FLG_ON)): ?>
                <h3 class="ttl-sec mgt-10"><?= h($formGroup['name']) ?></h3>
            <?php else : ?>
                <div class="mgt-20"></div>
            <?php endif; ?>
            <table class="input-box mgt-10">
                <?= $this->fetch('inputItemsDetail') ?>
            </table>
        </fieldset>
    <?php endif; ?>
<?php endforeach; ?>
