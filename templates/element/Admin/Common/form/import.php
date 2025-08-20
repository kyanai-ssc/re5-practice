<?php $this->start('import_html'); ?>
<div class="hidden js_import_form">
    <div id="fileUp" title="ファイルアップロード">
        <div class="popup-content">
            <p class="cmn-txt">
                <?= $this->Form->Control('import', [
                    'type' => 'file',
                    'class' => ['mgt-20', 'js_import_file'],
                ]) ?>
            </p>
            <div class="js_import_error"></div>
            <div class="desc-wrap">
                <p>文字コードがUTF-8（BOMあり）のCSVファイルを選択してください。形式が異なる場合、値が登録されないことがあります。</p>
            </div>
            <?php if (isset($importNote)) : ?>
            <div class="desc-wrap">
                <p><?= h($importNote) ?></p>
            </div>
            <?php endif; ?>
            <p class="cmn-txt mgt-20">
                <?= $this->Html->link('＞ フォーマットをダウンロードする', '#', [
                    'class' => ['js_post_link_plural', 'link-txt'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => $importController,
                        'action' => 'sample',
                    ], ['escape' => false]),
                ])
                ?>
            </p>
            <div class="popupIn-btn tac mgt-20">
                <?= $this->Form->button((isset($uploadTitle)) ? $uploadTitle : 'アップロード', [
                    'type' => 'button',
                    'class' => ['cmn-btn', 'is-blue', 'js_import_submit'],
                    'data-url' => strtolower($importController),
                ]) ?>
            </div>
        </div>
    </div>
</div>
<?php $this->end('import_html'); ?>
<?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_upload"/></svg>', [
    'type' => 'button',
    'class' => ['btn-list', 'js_import', 'is-upload', 'fileUp'],
    'title' => (isset($uploadTitle)) ? $uploadTitle : 'データのアップロード',
    'data-title' => (isset($uploadTitle)) ? $uploadTitle : 'データのアップロード',
    'data-url' => strtolower($importController),
    'data-html' => $this->fetch('import_html'),
    'escapeTitle' => false,
]) ?>
