<?php
$this->Form->unlockField('option_stock_settings');
?>

<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">オプション名<?= $this->Template->isRequire('name') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('name', [
                        'type' => 'text',
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">料金<?= $this->Template->isRequire('charge') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('charge', [
                        'type' => 'text',
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">在庫数<?= $this->Template->isRequire('stock') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('stock', [
                        'type' => 'text',
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">在庫の単位<?= $this->Template->isRequire('stock_unit') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('stock_unit', [
                        'type' => 'text',
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">適用期間<?= $this->Template->isRequire('usage_timestamp_from') ?></div>
                </th>
                <td>
                    <div class="d-flex">
                        <?= $this->Form->control('usage_timestamp_from', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['js-datepicker-time'],
                        ]) ?>
                        <span class="txt mgl-10 mgr-10">から</span>
                        <?= $this->Form->control('usage_timestamp_to', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['js-datepicker-time',]
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">説明文<?= $this->Template->isRequire('description') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('description', [
                        'type' => 'textarea',
                        'label' => false,
                        'class' => ['toWysiWyg'],
                        'rows' => 5,
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">公開設定<?= $this->Template->isRequire('charge') ?></div>
                </th>
                <td>
                    <?= $this->Template->radio('public_flg', [
                        'type' => 'radio',
                        'label' => false,
                        'options' => $valueOptions['publicFlg'],
                        'default' => \App\Model\Entity\Option::PUBLIC_FLG_ON
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">例外日設定<?= $this->Template->isRequire('option_stock_settings') ?></div>
                </th>
                <td>
                    <table class="pliceInput-detail">
                        <thead>
                        <tr>
                            <th>適用期間</th>
                            <th>在庫数</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody class="js_stock_setting_container">
                        <?php if (isset($option->option_stock_settings)): ?>
                            <?php foreach ($option->option_stock_settings as $optionStockSettingIndex => $optionStockSetting): ?>
                                <?= $this->element('Admin/Options/fieldset_option_stock_setting', [
                                    'option' => $option,
                                    'optionStockSettingIndex' => $optionStockSettingIndex,
                                    'optionStockSetting' => $optionStockSetting,
                                ]) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <?= $this->FormError->errorWithoutNested('option_stock_settings'); ?>
                    <?= $this->Form->button('例外日 追加', [
                        'type' => 'button',
                        'class' => ['js_add_input', 'cmn-btn', 'is-addCell', 'mgt-20'],
                        'data-container' => '.js_stock_setting_container',
                        'data-html' => $this->element('Admin/Options/fieldset_option_stock_setting', [
                            'option' => $option,
                            'optionStockSettingIndex' => '%INDEX%',
                            'optionStockSetting' => null,
                        ]),
                        'data-index-element' => '.js_stock_setting_index',
                        'data-index-replace' => '%INDEX%',
                    ]) ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
