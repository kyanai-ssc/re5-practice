<?php
$this->assign('title', 'デザイン設定');
$this->assign('headerType', 'master');
$this->Html->script('vendor/jpicker/jpicker.min', [
    'block' => true,
]);

$this->Breadcrumbs->add(
    'デザイン設定'
);
?>
<section class="form-input">
    <?= $this->Flash->render('cmsFinish') ?>
    <?= $this->Flash->render('cmsErrors') ?>
    <?= $this->Form->create($cms, [
        'type' => 'post',
        'url' => $this->Url->build(["controller" => 'Cms', "action" => 'edit']),
        'idPrefix' => 'cms-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'context' => ['validator' => 'cms'],
        'data-confirm-title' => 'デザイン設定の編集',
        'data-confirm-message' => 'デザイン設定の編集をおこなってよろしいですか？',
    ]) ?>

    <div class="form-input-set">
        <fieldset>
            <table class="input-box btn-inline">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">カラー選択<?= $this->Template->isRequire('site_theme') ?></div>
                    </th>
                    <td>
                        <table class="colorInput-detail" id="colorTable">
                            <thead>
                            <tr>
                                <th class="min-maxW-180">設定場所</th>
                                <th>カラー</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($valueOptions['siteTheme'] as $index => $color) : ?>
                                <tr>
                                    <td>
                                        <?= h($color) ?>
                                    </td>
                                    <td>
                                        <?= $this->Form->control('site_theme.' . $index, [
                                            'type' => 'text',
                                            'class' => ['js_colorPicker_target', 'w-80'],
                                        ]) ?>
                                        <input type="hidden" class="colorPicker hidden" data-image=<?= h($this->Url->image('vendor/jpicker/')) ?> value="<?= h($cms->site_theme[$index]) ?>">
                                        <?= $this->Form->formatTemplate('error', ['content' => (isset($siteThemeError[$index])) ? $siteThemeError[$index] : null]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">ロゴ（PC）<?= $this->Template->isRequire('header_logo_pc') ?></div>
                    </th>
                    <td>
                        <?= $this->Form->control('header_logo_pc', [
                            'type' => 'text',
                            'label' => false,
                        ]) ?>
                        <?= $this->Form->button('ファイル管理から選択', [
                            'type' => 'button',
                            'class' => ['js_file_select_btn', 'cmn-btn', 'is-blue', 'is-circle'],
                            'data-name' => 'header_logo_pc',
                            'data-title' => 'ファイル管理から選択'
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">ロゴ（スマートフォン）<?= $this->Template->isRequire('header_logo_sp') ?></div>
                    </th>
                    <td>
                        <?= $this->Form->control('header_logo_sp', [
                            'type' => 'text',
                            'label' => false,
                        ]) ?>
                        <?= $this->Form->button('ファイル管理から選択', [
                            'type' => 'button',
                            'class' => ['js_file_select_btn', 'cmn-btn', 'is-blue', 'is-circle'],
                            'data-name' => 'header_logo_sp',
                            'data-title' => 'ファイル管理から選択'
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">キーワード（SEO対策）<?= $this->Template->isRequire('meta_keyword') ?></div>
                    </th>
                    <td>
                        <?= $this->Form->control('meta_keyword', [
                            'type' => 'text',
                            'label' => false,
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            ディスクリプション（SEO対策）<?= $this->Template->isRequire('meta_description') ?></div>
                    </th>
                    <td>
                        <?= $this->Form->control('meta_description', [
                            'type' => 'text',
                            'label' => false,
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">トップページ画像<?= $this->Template->isRequire('key_visual_url') ?></div>
                    </th>
                    <td>
                        <?= $this->Form->control('key_visual_url', [
                            'type' => 'text',
                            'label' => false,
                        ]) ?>
                        <?= $this->Form->button('ファイル管理から選択', [
                            'type' => 'button',
                            'class' => ['js_file_select_btn', 'cmn-btn', 'is-blue', 'is-circle'],
                            'data-name' => 'key_visual_url',
                            'data-title' => 'ファイル管理から選択'
                        ]) ?>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">トップページ追加情報<?= $this->Template->isRequire('top_information') ?></div>
                    </th>
                    <td>
                        <?= $this->Form->control('top_information', [
                            'type' => 'textarea',
                            'class' => ['wysiwyg'],
                            'label' => 'false',
                        ]) ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <div class="hide">
        <div id="js_frameWindow"></div>
    </div>
    <div class="btn-box mgt-20">
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>

