<?php

use App\Model\Entity\EventRemark;

?>

<dl class="groupInput-detail js_event_remarks_container_<?= h($eventRemarkIndex) ?>">
    <dt class="addIndex"><?= (is_numeric($eventRemarkIndex) ? h($eventRemarkIndex + 1) : h($eventRemarkIndex)); ?></dt>
    <dd>
        <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"></use></svg>', [
            'type' => 'button',
            'title' => '削除',
            'class' => ['js_remove_input', 'btn-input', 'is-delete', ' groupInputDel ', 'tooltip'],
            'data-selector' => '.js_event_remarks_container_' . $eventRemarkIndex,
            'data-context' => '.js_event_remarks_container',
            'escapeTitle' => false,
        ]) ?>
        <dl class="groupInput-detail-in">
            <dt>予約枠備考を選択</dt>
            <dd class="d-flex">
                <input type="hidden" class="js_event_remarks_index" value="<?= h($eventRemarkIndex) ?>"/>
                <?= $this->Form->hidden('event_remarks.' . $eventRemarkIndex . '.id') ?>

                <?= $this->Template->checkbox('event_remarks.' . $eventRemarkIndex . '.detail_display_flg', [
                    'type' => 'checkbox',
                    'id' => 'event_remarks_detail_display_flg' . $eventRemarkIndex,
                    'label' => ['class' => 'cmn-check btn-tool', 'text' => $valueOptions['remarkDetailDisplayFlg'][EventRemark::DETAIL_DISPLAY_FLG_ON]],
                    'title' => $valueOptions['remarkDetailDisplayFlg'][EventRemark::DETAIL_DISPLAY_FLG_ON]
                ]) ?>
                <?= $this->Form->control('event_remarks.' . $eventRemarkIndex . '.form_item_id', [
                    'type' => 'select',
                    'options' => $valueOptions['formItemId'],
                    'class' => ['select'],
                    'id' => 'event_remarks.' . $eventRemarkIndex . '.form_item_id',
                ]) ?>
            </dd>
        </dl>
        <dl class="groupInput-detail-in">
            <dt>注釈名</dt>
            <dd>
                <?= $this->Form->control('event_remarks.' . $eventRemarkIndex . '.name', [
                    'type' => 'text',
                    'id' => 'event_remarks.' . $eventRemarkIndex . '.name',
                ]) ?>
            </dd>
        </dl>
        <dl class="groupInput-detail-in">
            <dt>本文</dt>
            <dd>
                <?= $this->Form->control('event_remarks.' . $eventRemarkIndex . '.remark', [
                    'type' => 'textarea',
                    'class' => ['wysiwyg'],
                    'rows' => 5,
                    'id' => 'event_remarks.' . $eventRemarkIndex . '.remark',
                ]) ?>
            </dd>
        </dl>
    </dd>
</dl>
