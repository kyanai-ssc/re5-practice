<tr class="js_event_stock_marks_container_<?= h($eventStockMarkIndex) ?>">
    <td>
        <input type="hidden" class="js_event_stock_marks_index" value="<?= h($eventStockMarkIndex) ?>"/>
        <?= $this->Form->hidden('event_stock_marks.' . $eventStockMarkIndex . '.id') ?>
        <?= $this->Form->control('event_stock_marks.' . $eventStockMarkIndex . '.symbolic', [
            'type' => 'select',
            'label' => false,
            'class' => ['select'],
            'options' => $valueOptions['symbolic'],
        ]) ?>
    </td>
    <td>
        <span class="txt mgr-10">在庫数が</span>
        <?php if (isset($eventStockMarkData) && $eventStockMarkIndex <= '1') : ?>
            <span class="cmn-txt"><?= h($eventStockMarkIndex) ?></span>
            <?= $this->Form->control('event_stock_marks.' . $eventStockMarkIndex . '.number', [
                'type' => 'hidden',
                'value' => $eventStockMarkIndex,
            ]) ?>
            <?php if ($eventStockMarkIndex) : ?>
                <span class="txt mgl-10">以上</span>
            <?php endif; ?>
        <?php else: ?>
            <?= $this->Form->control('event_stock_marks.' . $eventStockMarkIndex . '.number', [
                'type' => 'text',
                'label' => '切り替えタイミング',
                'class' => ['textbox_w70']
            ]) ?>
            <span class="txt mgl-10">以上</span>
        <?php endif; ?>
    </td>
    <td>

        <?php if (!isset($eventStockMarkData) || $eventStockMarkIndex > '1') : ?>
            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                'type' => 'button',
                'class' => ['js_remove_input', 'btn-input', 'is-delete'],
                'title' => '削除',
                'data-selector' => '.js_event_stock_marks_container_' . $eventStockMarkIndex,
                'data-context' => '.js_event_stock_marks_container',
                'escapeTitle' => false,
            ]) ?>
        <?php endif; ?>
    </td>
</tr>
