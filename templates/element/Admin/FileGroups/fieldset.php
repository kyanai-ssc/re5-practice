<?php

use App\Locale\Message;

$this->Form->unlockField('files');
$this->Form->unlockField('uploadFile');

$this->Html->script('admin/file-groups/fieldset', [
    'block' => true,
]);
?>
<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">グループ名<?= $this->Template->isRequire('name') ?></div>
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
                    <div class="ttl-input-wrap">ディレクトリ名<?= $this->Template->isRequire('directory') ?></div>
                </th>
                <td>
                    <?php if ($fileGroup->isNew()) : ?>
                        <?= $this->Form->control('directory', [
                            'type' => 'text',
                            'label' => 'ディレクトリ名',
                        ]) ?>
                        <div class="desc-wrap">
                            <p>
                                ※ディレクトリ名は、<?= $this->Url->build(['prefix' => 'User', 'controller' => 'index'], ['fullBase' => true]); ?>
                                file/***/####.pdf　の『***』部分です。</p>
                            <p>半角英数小文字で指定ください。</p>
                        </div>
                    <?php else : ?>
                        <div class="cmn-txt">
                            <?= h($fileGroup->directory) ?>
                            <?= $this->Form->hidden('directory', ['value' => $fileGroup->directory]) ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap js_error_message" data-error-message="<?= $this->Tr->h(Message::ERROR_UPLOAD) ?>">ファイル<?= $this->Template->isRequire('files') ?></div>
                </th>
                <td>
                    <?= $this->FormError->errorWithoutNested('files') ?>
                    <div class="js_files_container">
                        <?php if (isset($fileGroup->files)): ?>
                            <?php foreach ($fileGroup->files as $fileIndex => $file): ?>
                                <?php if (!$file->isNew()) {
                                    $file->index = $fileIndex;
                                }
                                ?>
                                <?= $this->element('Admin/FileGroups/fieldset_files', [
                                    'fileGroup' => $fileGroup,
                                    'fileIndex' => $fileIndex,
                                    'file' => $file,
                                    'originalName' => (isset($uploadFiles[$file->index]['original_file_name']) && $uploadFiles[$file->index]['new']) ? $uploadFiles[$file->index]['original_file_name'] : '',
                                    'nextIndex' => null,
                                    'new' => $file->isNew(),
                                ]) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <div class="js_file_form"></div>

                        <span class="txt">ファイル追加：</span>
                        <?= $this->Form->control('uploadFile', [
                            'type' => 'file',
                            'label' => false,
                            'class' => ['js_file_upload']
                        ]) ?>

                        <div class="desc-wrap">
                            <p>
                                ※ファイル名は、<?= $this->Url->build(['prefix' => 'User', 'controller' => 'index'], ['fullBase' => true]); ?>
                                file/***/####.pdf　の『####』部分です。
                            </p>
                            <p>半角英数小文字で指定ください。</p>
                        </div>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
