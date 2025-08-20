<?php $this->start('inputTypeSearchAddressSearch'); ?>
    <td>
        <div>
            <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey() . '.' . 'empty', [
                'type' => 'checkbox',
                'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '未入力'],
                'value' => $this->Configure->readOrFail('Master.common.flg.on'),
            ]) ?>
        </div>
        <div class="addInput">
            <dl>
                <dt><?= h(__('住所検索/郵便番号')) ?></dt>
                <dd class="addSearch">
                    <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.value.zip', [
                        'type' => 'text',
                        'class' => ['textbox_w300']
                    ]) ?>
                </dd>
                <dt><?= h(__('住所検索/都道府県')) ?></dt>
                <dd class="btn-inline">
                    <?= $this->Template->checkbox($formItem->getInputTypeItem()->getSearchInputKey() . '.value.prefecture', [
                        'type' => 'multicheckbox',
                        'options' => $this->Master->getPrefectureValues(),
                    ]) ?>
                </dd>
                <dt><?= h(__('住所検索/市区町村')) ?></dt>
                <dd>
                    <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.value.municipality', [
                        'type' => 'text',
                        'class' => ['textbox_w300']
                    ]) ?>
                </dd>
                <dt><?= h(__('住所検索/町域番地')) ?></dt>
                <dd>
                    <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.value.town', [
                        'type' => 'text',
                        'class' => ['textbox_w300']
                    ]) ?>
                </dd>
                <dt><?= h(__('住所検索/建物名')) ?></dt>
                <dd>
                    <?= $this->Form->control($formItem->getInputTypeItem()->getSearchInputKey() . '.value.building', [
                        'type' => 'text',
                        'class' => ['textbox_w300']
                    ]) ?>
                </dd>
            </dl>
        </div>
    </td>
<?php $this->end('inputTypeSearchAddressSearch'); ?>
<?= $this->element('InputType/Inputs/search', [
    'formItem' => $formItem,
    'element' => $this->fetch('inputTypeSearchAddressSearch'),
]) ?>
