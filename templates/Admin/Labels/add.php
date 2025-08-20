<?php
$this->assign('title', 'カテゴリー設定 新規登録');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    'カテゴリー設定',
    ['prefix' => 'Admin', 'controller' => 'Labels', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '新規登録'
);
?>
<section class="form-input">
    <?= $this->Flash->render('labelsErrors') ?>

    <?= $this->Form->create($label, [
        'type' => 'post',
        'url' => [
            'controller' => 'Labels',
            'action' => 'add',
        ],
        'idPrefix' => 'labels-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => 'カテゴリーの登録',
        'data-confirm-message' => 'カテゴリーの登録をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/Labels/fieldset', [
        'label' => $label,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
        'excludeId' => $excludeId,
    ]) ?>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Labels',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ], [
            'class' => ['cmn-btn', 'is-reset', 'is-gray'],
        ]) ?>
        <?= $this->Form->button('登録', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
