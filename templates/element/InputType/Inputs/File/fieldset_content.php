<div class="js_file_container">
    <div class="cmnInput">
        <?= $this->Form->control($formItem->getInputTypeItem()->getFieldsetInputKey(), [
            'type' => 'file',
            'label' => false,
            'data-id' => (string)$formItem->id,
            'data-url' => $this->Url->build($formItem->getInputTypeItem()->getUploadAjaxUrl(),['escape' => false,]),
            'data-key' => isset($reservationForm) ? $reservationForm->getContinuousParameter('key') : $index,
            'class' => ['js_reservation_file_upload']
        ]) ?>
        <?php if (isset($errorMessages)): ?>
            <?php foreach ($errorMessages as $key => $message): ?>
                <span class="warning"><?= h($message)?></span>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($formItem->getInputTypeItem()->hasTempFile()): ?>
            <div>
                添付されているファイル：
                <?= $this->Html->link(
                    $formItem->getInputTypeItem()->getAttachedFileName(),
                    $formItem->getInputTypeItem()->getTempFileUrl()
                    );
                ?>
                <?= $this->Form->button('x', [
                    'type' => 'button',
                    'title' => '削除',
                    'class' => ['js_remove_file_input', 'btn-input', 'is-delete', 'tooltip'],
                    'data-file' => json_encode([
                        'continuous_key' => isset($reservationForm) ? $reservationForm->getContinuousParameter('key') : $index,
                        'form_item_id' => $formItem->id,
                    ], JSON_UNESCAPED_UNICODE),
                    'data-selector' => '.js_file_item[data-id="' . h($formItem->id) . '"]',
                    'data-context' => '.js_file_form[data-id="' . h($formItem->id) . '"]',
                    'data-error-message' => 'ファイルの削除に失敗しました',
                    'data-confirm-message' => 'ファイルの削除をおこなってよろしいですか？',
                    'data-confirm-title' => 'ファイルの削除',
                    'data-url' => $this->Url->build($formItem->getInputTypeItem()->getDeleteAjaxUrl(),['escape' => false,]),
                    'escapeTitle' => false,
                ]) ?>
            </div>
        <?php elseif ($formItem->getInputTypeItem()->hasSavedFile() && !$formItem->getInputTypeItem()->isDeleting()): ?>
            <div>
                添付されているファイル：
                <?= $this->Html->link(
                    $formItem->getInputTypeItem()->getAttachedFileName(),
                    $formItem->getInputTypeItem()->getSavedFileUrl()
                    );
                ?>
                <?= $this->Form->button('x', [
                    'type' => 'button',
                    'title' => '削除',
                    'class' => ['js_remove_file_input', 'btn-input', 'is-delete', 'tooltip'],
                    'data-file' => json_encode([
                        'form_item_id' => $formItem->id,
                        'continuous_key' => isset($reservationForm) ? $reservationForm->getContinuousParameter('key') : $index,
                    ], JSON_UNESCAPED_UNICODE),
                    'data-selector' => '.js_file_item[data-id="' . h($formItem->id) . '"]',
                    'data-context' => '.js_file_form[data-id="' . h($formItem->id) . '"]',
                    'data-error-message' => 'ファイルの削除に失敗しました',
                    'data-confirm-message' => 'ファイルの削除をおこなってよろしいですか？',
                    'data-confirm-title' => 'ファイルの削除',
                    'data-url' => $this->Url->build($formItem->getInputTypeItem()->getDeleteAjaxUrl(),['escape' => false,]),
                    'escapeTitle' => false,
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
