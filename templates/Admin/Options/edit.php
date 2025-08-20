<?php
$this->assign('title', 'オプション設定 編集');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    'オプション設定',
    ['prefix' => 'Admin', 'controller' => 'Options', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '編集'
);
?>
<section class="form-input">
    <?= $this->Flash->render('optionsErrors') ?>

    <?= $this->Form->create($option, [
        'type' => 'post',
        'url' => [
            'controller' => 'Options',
            'action' => 'edit',
            'id' => $option->get('id'),
        ],
        'idPrefix' => 'options-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => 'オプションの編集',
        'data-confirm-message' => 'オプションの編集をおこなってよろしいですか？',
    ]) ?>


    <?= $this->element('Admin/Options/fieldset', [
        'option' => $option,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
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
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
