<?php
$this->assign('ajax_html', null);
?>
<?php if (!$finish): ?>
    <?php $this->start('ajax_html'); ?>
    <div class="tooltip">
        <?= $this->Form->create($adminSearchItem, [
            'type' => 'post',
            'url' => '.',
            'idPrefix' => 'admin-search-items-edit',
            'novalidate' => true,
            'class' => ['js_submit_once js_no_submit js_select_search_items_form'],
            'data-type' => $type,
        ]) ?>
        <ul>
            <li class="setting">
                <div class="baloon baloon-srot">
                    <dl class="sort-list">
                        <dd class="sort-list-contents">
                            <?php foreach ($valueOptions['items'][$type] as $formType => $items): ?>
                                <dl class="sort-list-section">
                                    <dt><?= h($this->Configure->read('Master.form.formType.' . $formType)) ?>で絞り込み</dt>
                                    <dd>
                                        <ul>
                                            <li>
                                                <?= $this->Template->checkbox('items.' . $formType, [
                                                    'type' => 'multicheckbox',
                                                    'label' => '',
                                                    'options' => $items,
                                                    'hiddenField' => false,
                                                ]) ?>
                                            </li>
                                        </ul>
                                    </dd>
                                </dl>
                            <?php endforeach; ?>
                        </dd><!-- .sort-list-contents -->
                    </dl><!-- .sort-list -->
                    <?= $this->Form->error('items') ?>
                    <span class="parts-for-btn">
                        <?= $this->Form->button('設定する', [
                            'type' => 'submit',
                            'class' => ['btn_baloon_submit'],
                        ]) ?>
                     </span>
                </div>
            </li>
        </ul>
        <?= $this->Form->end() ?>
    </div>
    <?php $this->end('ajax_html'); ?>
<?php endif; ?>
<?= $this->Ajax->json([
    'finish' => $finish,
    'html' => $this->fetch('ajax_html'),
]) ?>
