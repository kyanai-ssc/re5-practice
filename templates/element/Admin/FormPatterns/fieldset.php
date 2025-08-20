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
                    <div class="ttl-input-wrap">備考<?= $this->Template->isRequire('remark') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('remark', [
                        'type' => 'textarea',
                        'label' => false,
                    ]) ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>