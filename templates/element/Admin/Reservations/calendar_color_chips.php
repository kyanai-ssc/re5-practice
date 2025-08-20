<div class="is-bottom">
    <div class="colorTip is-open">
        <ul class="colorTip-block-base hidden">
            <li class="colorTip-block-li">
                <span class="colorTip-block"></span>
            </li>
        </ul>
        <ul class="js_add_color">
            <?php if (!empty($colorChips)): ?>
                <?php foreach ($colorChips as $colorChipId => $colorChip): ?>
                    <li class="colorTip-block-li">
                        <span
                            class="colorTip-block js_apply_style"
                            data-sort="<?= h($colorChip['sort_no']) ?>"
                            data-style="<?= h(json_encode(['background-color' => $colorChip['color_code']])) ?>"
                            data-colorchip-id="<?= h($colorChipId) ?>"
                        >
                        </span>
                        <?= h($colorChip['name']) ?>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
