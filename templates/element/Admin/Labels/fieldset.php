<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">上位カテゴリー<?= $this->Template->isRequire('parent_id') ?></div>
                </th>
                <td>
                    <?= $this->Label->renderSelect([
                        'type' => $this->Configure->read('Master.label.type.create'),
                        'excludeId' => $excludeId,
                        'labelId' => $label->parent_id,
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">カテゴリー名<?= $this->Template->isRequire('name') ?></div>
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
                    <div class="ttl-input-wrap">公開設定<?= $this->Template->isRequire('public_flg') ?></div>
                </th>
                <td>
                    <?= $this->Template->radio('public_flg', [
                        'type' => 'radio',
                        'label' => false,
                        'class' => ['cmn-radio'],
                        'options' => $valueOptions['publicFlg'],
                        'default' => \App\Model\Entity\Label::PUBLIC_FLG_ON
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">表示順<?= $this->Template->isRequire('sort_no') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('sort_no', [
                        'type' => 'text',
                        'label' => false,
                        'class' => ['textbox_w70']
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">閲覧権限<?= $this->Template->isRequire('label_authorities') ?></div>
                </th>
                <td class="btn-inline">
                    <?php foreach ($valueOptions['userAuthorityId'] as $userAuthorityId => $userAuthorityName) : ?>
                        <?= $this->Form->hidden('label_authorities.' . $userAuthorityId . '.id') ?>
                        <?= $this->Template->checkbox('label_authorities.' . $userAuthorityId . '.user_authority_id', [
                            'type' => 'checkbox',
                            'value' => $userAuthorityId,
                            'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => $userAuthorityName],
                            'id' => 'label-authorities-user-authority-id-' . $userAuthorityId,
                            'class' => ['js_access'],
                        ]) ?>
                    <?php endforeach; ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
