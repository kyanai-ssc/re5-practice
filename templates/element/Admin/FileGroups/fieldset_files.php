<dl class="js_files_container_<?= h($fileIndex) ?> inputWrap mgb-20">
    <input type="hidden" class="js_files_index" value="<?= h($fileIndex) ?>"/>

    <?php if ($new) : ?>
        <?= $this->Form->hidden('files.' . $fileIndex . '.index', ['default' => $nextIndex]) ?>
    <?php else : ?>
        <?= $this->Form->hidden('files.' . $fileIndex . '.index', ['default' => $fileIndex]) ?>
    <?php endif; ?>

    <?= $this->Form->hidden('files.' . $fileIndex . '.id', ['class' => ['js_files_id']]) ?>
    <?php if ($new) : ?>
    <dt class="fwb"><?= h($originalName) ?></dt>
    <dd>
        <div class="d-flex">
            <span class="txt">ファイル名：</span>
            <?= $this->Form->control('files.' . $fileIndex . '.file_name', [
                'type' => 'text',
                'class' => ['textbox_w300'],
                'label' => false,
            ]) ?>
            <?php else : ?>
    <dd>
        <?php if ($originalName != '') : ?>
            <span class="txt">更新ファイル名：<?= h($originalName) ?></span>
        <?php endif; ?>
        <div class="d-flex">
            <?= $this->Html->link($file->full_file_name,
                ['controller' => 'File', 'action' => 'index', 'prefix' => 'User', $fileGroup->directory, $file->full_file_name], ['target' => '_blank']);
            ?>
            <?php endif; ?>
            <span class="txt">説明文：</span>

            <?= $this->Form->control('files.' . $fileIndex . '.description', [
                'type' => 'text',
                'class' => ['textbox_w300'],
                'label' => false,
            ]) ?>
            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"></use></svg>', [
                'type' => 'button',
                'title' => '削除',
                'class' => ['js_remove_input', 'btn-input', 'is-delete', 'indiAdd', 'tooltip'],
                'data-selector' => '.js_files_container_' . $fileIndex,
                'data-context' => '.js_files_container',
                'escapeTitle' => false,
            ]) ?>
        </div>
        <?php if (!$new) : ?>
            <?= $this->Form->control('uploadFile', $this->Template->getTemplateSpan('file', ['class' => ['js_file_upload_edit', 'mgt-10'], 'data-index' => $fileIndex])) ?>
        <?php endif; ?>
    </dd>
</dl>
