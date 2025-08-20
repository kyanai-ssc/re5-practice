<?php
$this->assign('title', 'オプション設定 新規登録');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add(
    'オプション設定',
    ['prefix' => 'Admin', 'controller' => 'Options', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '新規登録'
);
?>
<section class="form-input">
    <?= $this->Flash->render('optionsErrors') ?>

    <?= $this->Form->create($option, [
        'type' => 'post',
        'url' => [
            'controller' => 'Options',
            'action' => 'add',
        ],
        'idPrefix' => 'options-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => 'オプションの登録',
        'data-confirm-message' => 'オプションの登録をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/Options/fieldset', [
        'admin' => $option,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Options',
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
