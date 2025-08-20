<!-- .search-group.-tag-list -->
<?= $this->Form->hidden('tag_id', [
    'value' => '',
    'secure' => $this->Form::SECURE_SKIP,
]) ?>
<?php foreach ($tagList as $tagGroupId => $tagGroups) : ?>
    <hr class="cmn-hr">
    <div class="search-group -tag-list">
        <fieldset>
            <legend><?= h($tagGroups['name']) ?></legend>
            <ul>
                <?php foreach ($tagGroups['tag'] as $tagId => $tagName) : ?>
                    <li>
                        <?= $this->Form->control('tag_id.' . $tagGroupId . '.' . $tagId, [
                            'type' => 'checkbox',
                            'value' => $tagId,
                            'hiddenField' => false,
                            'id' => 'tag-id-' . $tagGroupId . '-' . $tagId,
                            'class' => ['check-tag'],
                            'label' => ['class' => ['retrieval-btn', 'is-tag'], 'text' => $tagName,],
                        ]) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </fieldset>
    </div>
    <!-- search-group -tag-list -->
<?php endforeach; ?>
<?= $this->FormError->errorWithoutNested('tag_id') ?>
<?php if (!empty($tagList)): ?>
    <hr class="cmn-hr">
<?php endif; ?>
