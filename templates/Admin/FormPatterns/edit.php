<?php
$this->assign('title', $formTypeName . ' 編集');
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
$this->Breadcrumbs->add('編集');

$this->Html->script('admin/common/tableBtn_noScr', [
    'block' => true,
]);
$this->Html->script('admin/form-patterns/fieldset', [
    'block' => true,
]);
?>

<section class="form-input">
    <?= $this->Flash->render('formPatternsErrors') ?>

    <?= $this->Form->create($formPattern, [
        'type' => 'post',
        'url' => [
            'controller' => 'FormPatterns',
            'action' => 'edit',
            'id' => $formPattern->id,
            '_name' => $formTypeUrl . 'Edit'
        ],
        'idPrefix' => 'formPatterns-edit',
        'novalidate' => true,
        'class' => ['js_submit_json_confirm'],
        'data-confirm-title' => $formTypeName .'の編集',
        'data-confirm-message' => $formTypeName .'の編集をおこなってよろしいですか？',
    ]) ?>
    <?php $this->Form->unlockField('name'); ?>
    <?php $this->Form->unlockField('remark'); ?>
    <?php $this->Form->unlockField('form_pattern_display_types'); ?>
    <?php $this->Form->unlockField('form_pattern_options'); ?>
    <?php $this->Form->unlockField('formJson'); ?>

    <?= $this->element('Admin/FormPatterns/fieldset', [
        'reservationFormPattern' => $formPattern,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
    ]) ?>
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
    <?php $this->assign('formItemOptionIndex', 0); ?>
    <?php foreach ($formGroups as $groupId => $groupName): ?>
        <?php if ($groupId != 'all' && ((string)$groupId === (string)$selectGroupId || $selectGroupId === 'all')):  ?>
            <div class="js_form_groups_container_<?= h($groupId) ?> mgb-30">
                <div class="ttl-panel-show mgb-20">
                    <span class="ttl-s">
                        <?= h($groupName) ?>
                    </span>
                    <button type="button" class="showBtn"></button>
                </div>
                <div class="showWrap">
                    <div class="fixedTable-in">
                        <div class="fixedTableHead">
                            <table class="cmn-table">
                                <thead class="stickyTable">
                                <tr class="parent">
                                    <th>項目名</th>
                                    <th><span>入力タイプ</span></th>
                                    <th><span>表示設定</span></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <table class="cmn-table summary-table fixedTableBody">
                            <tbody>
                            <?= $this->element('Admin/FormPatterns/fieldset_items', [
                                'formPattern' => $formPattern,
                                'valueOptions' => $valueOptions,
                                'fieldPrefix' => '',
                                'labelDisplay' => true,
                                'className' => [],
                                'groupId' => $groupId,
                                'mode' => 'edit',
                            ]) ?>
                            </tbody>
                        </table><!-- .result-table -->
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'FormPatterns',
            'action' => 'list',
            '_name' => $formTypeUrl,
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ], [
            'class' => ['cmn-btn', 'is-reset', 'is-gray'],
        ]) ?>
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
