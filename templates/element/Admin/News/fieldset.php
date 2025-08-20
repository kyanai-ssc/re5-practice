<?php
$this->Form->unlockField('label_id');
$this->Form->unlockField('news_authorities');
?>
<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">表示順<?= $this->Template->isRequire('sort_no') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('sort_no', [
                        'type' => 'text',
                        'class' => ['textbox_w150']
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">カテゴリー<?= $this->Template->isRequire('label_id') ?></div>
                </th>
                <td>
                    <?= $this->Label->renderSelect([
                        'type' => $this->Configure->read('Master.label.type.other'),
                        'labelId' => $news->label_id,
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">タイトル<?= $this->Template->isRequire('title') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('title', [
                        'type' => 'text',
                        'label' => false,
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">閲覧権限<?= $this->Template->isRequire('title') ?></div>
                </th>
                <td class="btn-inline">
                    <?= $this->FormError->errorWithoutNested('news_authorities') ?>
                    <?php foreach ($valueOptions['userAuthorityId'] as $userAuthorityId => $userAuthorityName) : ?>
                        <?= $this->Form->hidden('news_authorities.' . $userAuthorityId . '.id') ?>
                        <?= $this->Template->checkbox('news_authorities.' . $userAuthorityId . '.user_authority_id', [
                            'type' => 'checkbox',
                            'value' => $userAuthorityId,
                            'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => $userAuthorityName],
                            'id' => 'news-authorities-user-authority-id-' . $userAuthorityId,
                            'class' => ['js_access'],
                            'data-all' => \App\Model\Entity\UserAuthority::SELECT_ALL,
                        ]) ?>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">掲載期間<?= $this->Template->isRequire('public_from') ?></div>
                </th>
                <td>
                    <div class="d-flex">
                        <?= $this->Form->control('public_from', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['js-datepicker-time'],
                        ]) ?>
                        <span class="txt mgl-10 mgr-10">から</span>
                        <?= $this->Form->control('public_to', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['js-datepicker-time',]
                        ]) ?>
                    </div>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">本文<?= $this->Template->isRequire('contents') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('contents', [
                        'type' => 'textarea',
                        'label' => false,
                            'class' => ['wysiwyg'],
                        'rows' => 10,
                        'id' => 'news-contents'
                    ]) ?>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>

