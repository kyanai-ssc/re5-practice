<?php


?>
<tr class="js_tags_container_<?= h($tagIndex) ?>">
    <td>
        <input type="hidden" class="js_tags_index" value="<?= h($tagIndex) ?>"/>
        <?= $this->Form->hidden('tags.' . $tagIndex . '.id') ?>

        <?= $this->Form->control('tags.' . $tagIndex . '.name', [
            'type' => 'text',
            'label' => false,
        ]) ?>
    </td>
    <td>
        <?= $this->Form->control('tags.' . $tagIndex . '.sort_no', [
            'type' => 'text',
            'label' => false,
            'class' => ['textbox_w70']
        ]) ?>
    </td>
    <td>
        <?= $this->Template->radio('tags.' . $tagIndex . '.public_flg', [
            'type' => 'radio',
            'class' => ['cmn-radio'],
            'label' => false,
            'options' => $valueOptions['publicFlg'],
            'default' => \App\Model\Entity\TagGroup::PUBLIC_FLG_ON
        ]) ?>
    </td>
    <td>
        <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
            'type' => 'button',
            'class' => ['js_remove_input', 'btn-input', 'is-delete'],
            'title' => '削除',
            'data-selector' => '.js_tags_container_' . $tagIndex,
            'data-context' => '.js_tags_container',
            'escapeTitle' => false,
        ]) ?>
    </td>
</tr>

