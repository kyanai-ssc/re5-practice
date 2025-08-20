<?php

use \App\Model\Entity\ColorChip;

?>

<tr class="js_colorChips_container_<?= h($colorChipIndex) ?>">
    <td class="handle">
        <svg class="icon is-drag">
            <use xlink:href="#icon_drag"/>
        </svg>
        <input type="hidden" class="js_colorChips_index" value="<?= h($colorChipIndex) ?>"/>
        <?= $this->Form->hidden('colorChips.' . $colorChipIndex . '.id') ?>
    </td>
    <td>
        <span>
            <?php if (isset($colorChipData)): ?>
                <?= h($this->Configure->read('Master.colorChip.type.' . $colorChipData->get('type'))) ?>
            <?php else: ?>
                <?= h($this->Configure->read('Master.colorChip.type.' . ColorChip::TYPE_ADD)) ?>
            <?php endif; ?>
        </span>
    </td>
    <td>
        <?= $this->Form->control('colorChips.' . $colorChipIndex . '.name', [
            'type' => 'text',
            'label' => false,
            'id' => 'name-%INDEX%',
            'class' => ['textbox_w200']
        ]) ?>
    </td>
    <td>
        <div class="d-flex">
            <?= $this->Form->control('colorChips.' . $colorChipIndex . '.color_code', [
                'type' => 'text',
                'label' => false,
                'id' => 'color_code-' . $colorChipIndex,
                'class' => ['js_colorPicker_target', 'w-80'],
            ]) ?>
            <input type="hidden" class="colorPicker hidden"
                   data-image=<?= h($this->Url->image('vendor/jpicker/')) ?> value="<?php if (isset($colorChipData['color_code'])): ?><?= h($colorChipData['color_code']) ?><?php endif; ?>">
        </div>
    </td>
    <td>
        <?= $this->Template->checkbox('colorChips.' . $colorChipIndex . '.front_display_flg', [
            'type' => 'checkbox',
            'label' => ['class' => ['cmn-check', 'btn-tool', 'no-txt-label'], 'text' => ''],
            'title' => $valueOptions['frontDisplayFlg'][ColorChip::FRONT_DISPLAY_FLG_ON],
            'value' => ColorChip::FRONT_DISPLAY_FLG_ON,
        ]) ?>
    </td>
    <td>
        <?php if ($colorChipData === null || $colorChipData->canDelete()) : ?>
            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                'type' => 'button',
                'class' => ['js_remove_input', 'btn-input', 'is-delete'],
                'title' => '削除',
                'data-selector' => '.js_colorChips_container_' . $colorChipIndex,
                'data-context' => '.js_colorChips_container',
                'escapeTitle' => false,
            ]) ?>
        <?php endif; ?>
    </td>
</tr>

