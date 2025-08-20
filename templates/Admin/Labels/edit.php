<?php
$this->assign('title', 'カテゴリー設定 編集');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    'カテゴリー設定',
    ['controller' => 'Labels', 'action' => 'list']
);
$this->Breadcrumbs->add(
    'カテゴリー設定 編集'
);
?>
<section class="form-input">
    <?= $this->Flash->render('labelsErrors') ?>

    <?= $this->Form->create($label, [
        'type' => 'post',
        'url' => [
            'controller' => 'Labels',
            'action' => 'edit',
            'id' => $label->get('id'),
        ],
        'idPrefix' => 'labels-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => 'カテゴリーの編集',
        'data-confirm-message' => 'カテゴリーの編集をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/Labels/fieldset', [
        'label' => $label,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
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
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
