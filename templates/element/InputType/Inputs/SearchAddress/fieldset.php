<?php $this->start('inputTypeSearchAddressFieldset'); ?>
<div class="cmnInput">
    <div class="js_search_address_input addInput">
        <dl>
            <dt><?= $this->Tr->h('form/zipCode') ?></dt>
            <dd class="addSearch">
                <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '.zip', [
                    'type' => 'text',
                    'class' => ['js_search_address_input_zip', 'textbox_w150'] + $this->FormError->addFieldErrorClassWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()),
                    'error' => false,
                ]) ?>
                <?php if ($formItem->getInputTypeItem()->isAdmin()): ?>
                    <?= $this->Form->button($this->Tr->h('form/zipSearchBtn'), [
                        'type' => 'button',
                        'class' => ['js_search_address', 'cmn-btn', 'is-blue', 'mgl-10', 'is-circle'],
                        'data-url' => $this->Url->build([
                            'prefix' => 'Admin/Ajax',
                            'controller' => 'Zip',
                            'action' => 'search',
                        ], ['escape' => false]),
                    ]) ?>
                <?php else: ?>
                    <?= $this->Form->button($this->Tr->h('form/zipSearchBtn'), [
                        'type' => 'button',
                        'class' => ['js_search_address', 'cmn-btn', 'is-blue', 'is-circle', 'mgl-10'],
                        'data-url' => $this->Url->build([
                            'prefix' => 'User/Ajax',
                            'controller' => 'Zip',
                            'action' => 'search',
                        ], ['escape' => false]),
                    ]) ?>
                <?php endif; ?>
                <?= $this->Form->error($formItem->getInputTypeItem()->getFieldsetInputKey() . '.zip') ?>
            </dd>
            <dt><?= $this->Tr->h('form/pref') ?></dt>
            <dd>
                <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '.prefecture', [
                    'type' => 'select',
                    'class' => ['js_search_address_input_prefecture', 'select'] + $this->FormError->addFieldErrorClassWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()),
                    'options' => $this->Master->getPrefectureValues(),
                    'empty' => true,
                ]) ?>
            </dd>
            <dt><?= $this->Tr->h('form/town1') ?></dt>
            <dd>
                <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '.municipality', [
                    'type' => 'text',
                    'class' => ['js_search_address_input_municipality', 'textbox_w300'] + $this->FormError->addFieldErrorClassWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()),
                ]) ?>
            </dd>
            <dt><?= $this->Tr->h('form/town2') ?></dt>
            <dd>
                <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '.town', [
                    'type' => 'text',
                    'class' => ['js_search_address_input_town', 'textbox_w300'] + $this->FormError->addFieldErrorClassWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()),
                ]) ?>
            </dd>
            <dt><?= $this->Tr->h('form/town3') ?></dt>
            <dd>
                <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey() . '.building', [
                    'type' => 'text',
                    'class' => ['textbox_w300'] + $this->FormError->addFieldErrorClassWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()),
                ]) ?>
            </dd>
        </dl>
    </div>
    <?= $this->FormError->errorWithoutNested($formItem->getInputTypeItem()->getFieldsetInputKey()) ?>
</div>
<?php $this->end('inputTypeSearchAddressFieldset'); ?>
<?= $this->element('InputType/Inputs/fieldset', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeSearchAddressFieldset'),
    'require' => ['inputName' => $formItem->getInputTypeItem()->getFieldsetInputKey()],
]) ?>
