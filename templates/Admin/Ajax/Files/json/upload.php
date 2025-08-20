<?php
$this->assign('ajax_file', null);
?>

<?php $this->start('ajax_file'); ?>
    <?php if($checkResult) :?>
        <?= $this->element('Admin/FileGroups/fieldset_files', [
            'fileGroup' => [],
            'fileIndex' => $file['index'],
            'file' => $file,
            'originalName' => $tmpUpload['original_file_name'],
            'nextIndex' => $nextIndex,
            'new' => true
        ]) ?>
        <div class="js_file_form"></div>
    <?php else : ?>
    <div>
        <?php foreach($errorMessages as $errorMessage) : ?>
            <?= $this->Form->formatTemplate('error', ['content' => $errorMessage]) ?>
        <?php endforeach ;?> 
    </div>
    <?php endif;?>
<?php $this->end('ajax_file'); ?>

<?= $this->Ajax->json([
    'result' => $checkResult,
    'html' => $this->fetch('ajax_file'),
    'title' => __('ファイルアップロード')
]) ?>
