<?php $this->Html->script('admin/user-authorities/fieldset', [
    'block' => true,
]);
?>

<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">権限名<?= $this->Template->isRequire('name') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('name', [
                        'type' => 'text',
                        'label' => false,
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">顧客情報の表示パターン<?= $this->Template->isRequire('form_pattern_id') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('form_pattern_id', [
                        'type' => 'select',
                        'label' => false,
                        'class' => ['select'],
                        'options' => $valueOptions['userFormPatterns'],
                    ]) ?>
                    <div class="desc-wrap">
                        <?= $this->Html->link('パターンを確認する',
                            ['controller' => 'formPatterns', 'action' => 'list', '_name' => 'formPatternsUser'], ['target' => '_blank']);
                        ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">利用できる機能<?= $this->Template->isRequire('access') ?></div>
                </th>
                <td>
                    <?= $this->Template->checkbox('access', [
                        'type' => 'select',
                        'multiple' => 'checkbox',
                        'label' => false,
                        'options' => $valueOptions['frontPageActions'],
                        'class' => ['js_access'],
                        'data-all' => $this->Configure->read('Master.userAuthority.frontCode.All'),
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">予約状況表示タイプ<?= $this->Template->isRequire('calendar_type') ?></div>
                </th>
                <td>
                    <?= $this->Template->checkbox('calendar_type', [
                        'type' => 'select',
                        'multiple' => 'checkbox',
                        'label' => false,
                        'options' => $valueOptions['calendarType'],
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">予約状況初期表示<?= $this->Template->isRequire('calendar_type_default') ?></div>
                </th>
                <td>
                    <?= $this->Template->radio('calendar_type_default', [
                        'type' => 'radio',
                        'label' => false,
                        'options' => $valueOptions['calendarType'],
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        ログイン時の表示項目<?= $this->Template->isRequire('login_name_form_item_id') ?>
                    </div>
                </th>
                <td>
                    <?= $this->Form->control('login_name_form_item_id', [
                        'type' => 'select',
                        'label' => false,
                        'class' => ['select'],
                        'options' => $valueOptions['loginNameFormItemId'],
                        'empty' => true,
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約回数の上限<?= $this->Template->isRequire('reservation_limit_future') ?>
                    </div>
                </th>
                <td>
                    <?= $this->Form->control('reservation_limit_future', [
                        'type' => 'text',
                    ]) ?>
                    <span class="txt mgl-10">回まで</span>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約回数の上限（1月あたり）<?= $this->Template->isRequire('reservation_limit_month') ?>
                    </div>
                </th>
                <td>
                    <?= $this->Form->control('reservation_limit_month', [
                        'type' => 'text',
                    ]) ?>
                    <span class="txt mgl-10">回まで</span>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約回数の上限（1日あたり）<?= $this->Template->isRequire('reservation_limit_day') ?>
                    </div>
                </th>
                <td>
                    <?= $this->Form->control('reservation_limit_day', [
                        'type' => 'text',
                    ]) ?>
                    <span class="txt mgl-10">回まで</span>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約回数の上限（過去含む全て）<?= $this->Template->isRequire('reservation_limit_all') ?>
                    </div>
                </th>
                <td>
                    <?= $this->Form->control('reservation_limit_all', [
                        'type' => 'text',
                    ]) ?>
                    <span class="txt mgl-10">回まで</span>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
