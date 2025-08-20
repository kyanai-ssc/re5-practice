<?php
$this->assign('title', $formTypeName . ' 一括編集');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add(
    $formTypeName . '設定',
    [
        'prefix' => 'Admin',
        'controller' => 'FormPatterns',
        'action' => 'list',
        '_name' => $formTypeUrl,
    ]
);
$this->Breadcrumbs->add('一括編集');
$this->Html->script('admin/form-patterns/together_edit', [
    'block' => true,
]);
$this->Html->script('admin/common/tableBtn', [
    'block' => true,
]);
$this->Html->script('admin/form-patterns/fieldset', [
    'block' => true,
]);
?>

<?= $this->Flash->render('formPatternsFinish') ?>
<?= $this->Flash->render('formPatternsErrors') ?>

<section class="panel-show">
    <div class="panel-show-wrap">
        <?php
            $params = $this->Configure->read('Setting.searchInput.searchQuery');
            if (!empty($selectGroupId)) {
                $params['group'] = $selectGroupId;
            }
        ?>
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'FormPatterns',
                'action' => 'togetherEdit',
                '_name' => $formTypeUrl,
                '?' => $params,
            ],
            'idPrefix' => 'formPatterns_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => $formTypeName . '検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset class="mgt-20">
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">パターン名</div>
                            </th>
                            <td>
                                <?= $this->Form->control('name', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </div><!-- .panel-show-set -->
            <div class="btn-box mgt-40">
                <?= $this->Form->button('リセット', [
                    'type' => 'reset',
                    'class' => ['js_change_url', 'cmn-btn', 'is-reset'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'FormPatterns',
                        'action' => 'togetherEdit',
                        '_name' => $formTypeUrl,
                    ], ['escape' => false]),
                ]) ?>
                <?= $this->Form->button('検索', ['type' => 'submit', 'class' => 'cmn-btn is-blue']) ?>
            </div>
        </div><!-- .showWrap -->
        <?= $this->Form->end() ?>
    </div><!-- .panel-show-wrap -->
</section><!-- .panel-show -->
<section class="search-list mgt-20">
    <?= $this->Form->create(new ArrayObject(['form_patterns' => $formPatterns]), [
        'type' => 'post',
        'url' => [
            'controller' => 'FormPatterns',
            'action' => 'togetherEdit',
            '_name' => $formTypeUrl,
            '?' => $this->Configure->read('Setting.searchInput.saveExec'),
            'page' => $this->Paginator->current(),
        ],
        'idPrefix' => 'formPatterns-togetherEdit',
        'novalidate' => true,
        'class' => ['js_submit_json_confirm'],
        'context' => ['table' => 'FormPatterns'],
        'data-confirm-title' => $formTypeName . 'の編集',
        'data-confirm-message' => $formTypeName . 'の編集をおこなってよろしいですか？',
    ]) ?>
    <?php $this->Form->unlockField('form_patterns'); ?>
    <?php $this->Form->unlockField('together_setting'); ?>
    <?php $this->Form->unlockField('formJson'); ?>
    <?php if (count($formPatterns) > 0): ?>
        <?php $this->Paginator->options(['url' => [
            'prefix' => 'Admin',
            'controller' => 'FormPatterns',
            'action' => 'togetherEdit',
            '_name' => $formTypeUrl,
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ]]) ?>
        <?php if (!empty($selectGroupId)): ?>
            <aside class="search-parts">
                <?= $this->element('Admin/Common/search/paginator') ?>
            </aside><!-- .search-parts -->
            <div class="desc-wrap tac">
                <p>1ページずつ編集してください。</p>
            </div>
        <?php endif; ?>
        <div class="mgt-20 mgb-20">
            グループ：
            <?= $this->Form->control('form_group_id', [
                'type' => 'select',
                'options' => $formGroups,
                'class' => ['select', 'js_select_form_group_id'],
                'empty' => true,
                'value' => $selectGroupId,
            ]) ?>
            <div class="mgt-10 mgb-20">
                ※グループを選択することでそのグループの項目のみ変更可能となります。
            </div>
        </div>
        <?php if (!empty($selectGroupId)): ?>
            <div class="desc-wrap mgt-20 mgb-20">
                <p>※<?= $this->Text->toList($valueOptions['displayTypeExample'], ',') ?></p>
            </div>
        <?php endif; ?>
        <?php $this->assign('formItemOptionIndex_all', 0); ?>
        <?php foreach ($formPatterns as $index => $formPattern) : ?>
            <?php $this->assign('formItemOptionIndex_' . $index, 0); ?>
        <?php endforeach; ?>
        <?php foreach ($formGroups as $groupId => $groupName): ?>
            <?php if ($groupId != 'all' && ((string)$groupId === (string)$selectGroupId || $selectGroupId === 'all')):  ?>
                <div class="fixedTable-option">
                    <aside class="fixedTable-arrow">
                        <button id="left-button" type="button" class="fixedTable-scroll-btn is-prev cmn-btn"></button>
                        <button id="right-button" type="button" class="fixedTable-scroll-btn is-next cmn-btn"></button>
                    </aside><!-- .search-parts -->
                </div>
                <div class="fixedTable-wrap">
                    <div class="fixedTable-in">
                        <div class="fixedTableHead">
                            <table class="cmn-table">
                                <thead class="stickyTable">
                                <tr>
                                    <?php foreach ($formPatterns as $index => $formPattern) : ?>
                                        <th><span><?= h($formPattern->name) ?></span></th>
                                    <?php endforeach; ?>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <table class="cmn-table summary-table fixedTableBody js_together_data_<?= h($groupId); ?>">
                            <tr>
                                <th colspan="<?= h(count($formPatterns)) ?>" class="form-pattern-together-head"></th>
                            </tr>
                            <tbody>
                            <?= $this->element('Admin/FormPatterns/fieldset_items', [
                                'formPatterns' => $formPatterns,
                                'valueOptions' => $valueOptions,
                                'labelDisplay' => false,
                                'className' => [],
                                'groupId' => $groupId,
                                'mode' => 'togetherEdit',
                            ]) ?>
                            </tbody>
                        </table><!-- .result-table -->
                    </div>
                    <table class="fixedTableLeft cmn-table" data-group-id="<?= h($groupId) ?>">
                        <thead>
                        <tr>
                            <th class="fixedElm"><span>項目名</span></th>
                            <th class="fixedElm"><span>入力タイプ</span></th>
                            <th class="fixedElm"><span>一括設定<br><small>※一括設定から右1ページ（10枠のみ）変更</small></span></th>
                        </tr>
                        </thead>
                        <tr>
                            <th colspan="3" class="form-pattern-together-item-head"><span><?= h($groupName) ?></span></th>
                        </tr>
                        <tbody>
                        <?= $this->element('Admin/FormPatterns/fieldset_items', [
                            'formPatterns' => [],
                            'valueOptions' => $valueOptions,
                            'labelDisplay' => true,
                            'className' => [],
                            'groupId' => $groupId,
                            'mode' => 'togetherEdit',
                        ]) ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!empty($selectGroupId)): ?>
            <div class="desc-wrap tac">
                <p>1ページずつ編集してください。</p>
            </div>
            <?= $this->element('Admin/Common/search/paginator') ?>
        <?php endif; ?>
    <?php else: ?>
        <?= $this->element('Admin/Common/search/no_result') ?>
    <?php endif; ?>
    <?php if (!empty($selectGroupId)): ?>
        <div class="btn-box mgt-40 tac">
            <?= $this->Html->link('戻る', [
                'prefix' => 'Admin',
                'controller' => 'FormPatterns',
                'action' => 'list',
                '_name' => $formTypeUrl,
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ], [
                'class' => ['cmn-btn', 'is-reset', 'is-gray'],
            ]) ?>
            <?php if (count($formPatterns) >= 1) : ?>
                <?= $this->Form->button('編集', [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue'],
                ]) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?= $this->Form->end() ?>
</section><!-- .search-list -->
