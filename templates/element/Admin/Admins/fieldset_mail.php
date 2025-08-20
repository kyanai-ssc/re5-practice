<tr class="js_admin_mails_container_<?= h($adminMailIndex) ?>">
    <td>
        <input type="hidden" class="js_admin_mails_index" value="<?= h($adminMailIndex) ?>"/>
        <?= $this->Form->hidden('admin_mails.' . $adminMailIndex . '.id') ?>
        <?= $this->Form->control('admin_mails.' . $adminMailIndex . '.mail', [
            'type' => 'text',
            'label' => false,
        ]) ?>
    </td>
    <td>
        <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
            'type' => 'button',
            'class' => ['js_remove_input', 'btn-input', 'is-delete'],
            'title' => '削除',
            'data-selector' => '.js_admin_mails_container_' . $adminMailIndex,
            'data-context' => '.js_admin_mails_container',
            'escapeTitle' => false,
        ]) ?>
    </td>
</tr>
