<?php
use App\Model\Entity\FormItem;
?>

<tr class="js_form_items_element_<?= h($formItem->session_key) ?> parent">
    <td class="handle">
        <svg class="icon is-drag">
            <use xlink:href="#icon_drag"/>
        </svg>
    </td>
    <td class="tool">
        <ul>
            <li>
                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"/></svg>', [
                    'type' => 'button',
                    'title' => '編集',
                    'class' => ['js_edit_form_item', 'btn-tool', 'is-editor', 'popup01', 'tooltip'],
                    'data-form-type' => $formType,
                    'data-form-group-index' => $formGroupIndex,
                    'data-form-item-index' => $formItemIndex,
                    'data-session-key' => $formItem->session_key,
                    'escapeTitle' => false,
                ]) ?>

            </li>
            <?php if ($formItem->canDelete() && $formItem->session_key !== FormItem::ATTRIBUTE): ?>
                <li>
                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                        'type' => 'button',
                        'title' => '削除',
                        'class' => ['js_delete_form_item', 'btn-tool', 'is-delete', 'tooltip'],
                        'data-form-type' => $formType,
                        'data-session-key' => $formItem->session_key,
                        'data-confirm-title' => '項目の削除',
                        'data-confirm-message' => 'ご登録いただいているデータも削除されますが、削除してもよろしいでしょうか？',
                        'data-confirm-html' => h('削除したデータの復旧はできません。'),
                        'escapeTitle' => false,
                    ]) ?>
                </li>
            <?php endif; ?>
        </ul>
        <?= $this->Flash->render('formItemsErrors.' . $formGroupIndex . '.' . $formItemIndex) ?>
        <input type="hidden" class="js_form_item_key" name="form_groups[<?= h($formGroupIndex) ?>][form_items][][session_key]" value="<?= h($formItem->session_key) ?>"/>
        <input type="hidden" class="js_form_items_index" value="<?= h($formItemIndex) ?>"/>
    </td>
    <td>
        <?= h($formItem->name) ?>
    </td>
    <td>
        <?= h($this->Configure->read('Master.form.requiredFlg.' . $formItem->required_flg)) ?>
    </td>
    <td>
        <?= h($this->Configure->read('Master.form.inputType.' . $formItem->input_type)) ?>
    </td>
    <?php if ($this->ArrayUtility->inArray($formType, $this->Configure->read('Master.form.canReservationDisplayFormType'))): ?>
        <td>
            <?= h($this->Configure->read('Master.form.reservationDisplayFlg.' . $formItem->reservation_display_flg)) ?>
        </td>
    <?php endif; ?>
    <td>
        <?php if (is_array($formItem->get('form_item_details'))): ?>
            <?php foreach ($formItem->get('form_item_details') as $formItemDetail): ?>
                <?php if (((string)$formItemDetail->get('text_input_check')) !== ''): ?>
                    <div>
                        <?= h($this->Configure->read('Master.form.textInputCheck.' . $formItemDetail->text_input_check)) ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </td>
    <td>
        <?php if (is_array($formItem->get('form_item_details'))): ?>
            <?php foreach ($formItem->get('form_item_details') as $formItemDetail): ?>
                <?php if (((string)$formItemDetail->get('text_input_translate')) !== ''): ?>
                    <div>
                        <?= h($this->Configure->read('Master.form.textInputTranslate.' . $formItemDetail->text_input_translate)) ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </td>
</tr>
