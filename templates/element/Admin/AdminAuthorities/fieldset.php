<?php $this->Html->script('admin/admin-authorities/fieldset', [
    'block' => true,
]);
?>

<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">パターン名<?= $this->Template->isRequire('name') ?></div>
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
                    <div class="ttl-input-wrap">利用許可画面<br>（各種設定メニュー）<?= $this->Template->isRequire('access_setting') ?></div>
                </th>
                <td>
                    <?= $this->Template->checkbox('access_setting', [
                        'type' => 'select',
                        'multiple' => 'checkbox',
                        'label' => false,
                        'options' => $valueOptions['accessSettingList'],
                        'class' => ['js_access_setting'],
                        'data-all' => $this->Configure->read('Master.adminAuthority.frontCode.All'),
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">利用許可画面<br>（運用メニュー）<?= $this->Template->isRequire('access_operator') ?></div>
                </th>
                <td>
                    <?= $this->Template->checkbox('access_operator', [
                        'type' => 'select',
                        'multiple' => 'checkbox',
                        'label' => false,
                        'options' => $valueOptions['accessOperatorList'],
                        'class' => ['js_access_operator'],
                        'data-all' => $this->Configure->read('Master.adminAuthority.frontCode.All'),
                    ]) ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
