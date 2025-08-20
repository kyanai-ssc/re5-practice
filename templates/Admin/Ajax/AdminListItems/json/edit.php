<?php
$this->assign('ajax_html', null);
?>
<?php if (!$finish): ?>
    <?php $this->start('ajax_html'); ?>
    <div class="tooltip">
        <?= $this->Form->create($adminListItem, [
            'type' => 'post',
            'url' => '.',
            'idPrefix' => 'admin-list-items-edit',
            'novalidate' => true,
            'class' => ['js_submit_once js_no_submit js_select_list_items_form'],
            'data-type' => $type,
        ]) ?>
        <?= $this->Form->hidden('items', [
            'value' => '',
        ]) ?>

        <div class="desc-wrap mgl-20 mgr-20 mgt-20">
            <p>
                <svg class="icon is-drag">
                    <use xlink:href="#icon_drag_side"/>
                </svg>
                を左クリック（タップ）しながら動かすことで順番を変更できます。
            </p>
        </div>
        <div class="popup-content">
            <div class="popupIn-btn tac">
                <div class="thEdit-wrap">
                    <ul class="thEdit js_sortable">
                        <?php if (is_array($adminListItem->get('items'))): ?>
                            <?php foreach ($adminListItem->get('items') as $item): ?>
                                <li class="thEdit-cell">
                                    <div class="handle">
                                        <svg class="icon is-drag">
                                            <use xlink:href="#icon_drag_side"/>
                                        </svg>
                                    </div>
                                    <p class="tac">
                                        <label
                                            for="list-disp-id-<?= h($item) ?>"><?= h($valueOptions['items'][$type][$item]) ?></label>
                                    </p>
                                    <p class="tac">
                                        <?= $this->Template->checkbox('items[]', [
                                            'type' => 'checkbox',
                                            'label' => ['class' => ['cmn-check', 'btn-tool', 'no-txt-label'], 'text' => ''],
                                            'value' => $item,
                                            'id' => 'list-disp-id-' . $item,
                                            'hiddenField' => false,
                                            'checked' => true,
                                            'templates' => [
                                                'error' => '',
                                            ],
                                        ]) ?>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php foreach ($valueOptions['items'][$type] as $key => $value): ?>
                            <?php if (!is_array($adminListItem->get('items')) || !$this->ArrayUtility->inArray($key, $adminListItem->get('items'))): ?>
                                <li class="thEdit-cell">
                                    <div class="handle">
                                        <svg class="icon is-drag">
                                            <use xlink:href="#icon_drag_side"/>
                                        </svg>
                                    </div>
                                    <p class="tac"><label for="list-disp-id-<?= h($key) ?>"><?= h($value) ?></label></p>
                                    <p class="tac">
                                        <?= $this->Template->checkbox('items[]', [
                                            'type' => 'checkbox',
                                            'label' => ['class' => ['cmn-check', 'btn-tool', 'no-txt-label'], 'text' => ''],
                                            'value' => $key,
                                            'id' => 'list-disp-id-' . $key,
                                            'hiddenField' => false,
                                            'checked' => false,
                                            'templates' => [
                                                'error' => '',
                                            ],
                                        ]) ?>
                                    </p>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?= $this->Form->error('items') ?>
                <div class="btn-box mgt-20 tac">
                    <?= $this->Form->button('この内容で保存', [
                        'type' => 'submit',
                        'class' => ['cmn-btn', 'is-blue'],
                    ]) ?>
                </div>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
    <?php $this->end('ajax_html'); ?>
<?php endif; ?>
<?= $this->Ajax->json([
    'finish' => $finish,
    'html' => $this->fetch('ajax_html'),
]) ?>
