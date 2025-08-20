<?php
use Cake\Utility\Hash;
?>
<?php $this->assign('inputTypeSearchAddressDetail', null); ?>
<?php if (isset($detailValue) && is_array($detailValue)): ?>
    <?php $this->start('inputTypeSearchAddressDetail'); ?>
        <div>
            <div>
                <span><?= $this->Tr->h('form/zipCode') ?></span>
                <?= h(Hash::get($detailValue, 'zip')) ?>
            </div>
            <div>
                <span><?= $this->Tr->h('form/pref') ?></span>
                <?php if (isset($detailValue['prefecture']) && $detailValue['prefecture'] !== ''): ?>
                    <?= h($this->Master->getPrefectureName($detailValue['prefecture'])) ?>
                <?php endif; ?>
            </div>
            <div>
                <span><?= $this->Tr->h('form/town1') ?></span>
                <?= h(Hash::get($detailValue, 'municipality')) ?>
            </div>
            <div>
                <span><?= $this->Tr->h('form/town2') ?></span>
                <?= h(Hash::get($detailValue, 'town')) ?>
            </div>
            <div>
                <span><?= $this->Tr->h('form/town3') ?></span>
                <?= h(Hash::get($detailValue, 'building')) ?>
            </div>
        </div>
    <?php $this->end('inputTypeSearchAddressDetail'); ?>
<?php endif; ?>
<?= $this->element('InputType/Inputs/detail', [
    'formItem' => $formItem,
    'detailValue' => $this->fetch('inputTypeSearchAddressDetail'),
    'escape' => false,
    'options' => $options,
]) ?>
