<?php foreach ($listItems as $item): ?>
    <?php if (is_object($item)): ?>
        <th class="<?= h($item->getInputTypeItem()->getListClass()) ?>">
            <?php if ($item->getInputTypeItem()->canSort()): ?>
                <div class="sort_wrap">
                    <span><?= h($item->get('name')) ?></span>
                    <?= $this->element('Admin/Common/search/sort', ['key' => $item->getInputTypeItem()->getSortKey()]) ?>
                </div>
            <?php else: ?>
                <span><?= h($item->get('name')) ?></span>
            <?php endif; ?>
        </th>
    <?php else: ?>
        <th class="<?= h($this->Configure->read('Master.adminListItems.itemsClass.' . $item)) ?>">
            <?php if (!is_null($this->Configure->read('Master.adminListItems.sortKey.' . $item))): ?>
                <div class="sort_wrap">
                    <span><?= h($this->Configure->read('Master.adminListItems.items.' . $item)) ?></span>
                    <?= $this->element('Admin/Common/search/sort', ['key' => $this->Configure->read('Master.adminListItems.sortKey.' . $item)]) ?>
                </div>
            <?php else: ?>
                <span><?= h($this->Configure->read('Master.adminListItems.items.' . $item)) ?></span>
            <?php endif; ?>
        </th>
    <?php endif; ?>
<?php endforeach; ?>
