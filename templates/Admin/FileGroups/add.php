<?php
$this->assign('headerType', 'data');
$this->assign('title', 'ファイル管理 新規登録');
$this->Breadcrumbs->add(
    'ファイル管理',
    ['prefix' => 'Admin', 'controller' => 'FileGroups', 'action' => 'list']
);
$this->Breadcrumbs->add('新規登録');
$this->assign('noNavi', $noNavi);
?>

<section class="form-input">
    <?= $this->Flash->render('fileGroupsErrors') ?>
    <?= $this->Form->create($fileGroup, [
        'type' => 'post',
        'url' => [
            'controller' => 'FileGroups',
            'action' => 'add',
            '?' => $selectFile,
        ],
        'idPrefix' => 'fileGroups-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => 'ファイルの登録',
        'data-confirm-message' => 'ファイルの登録をおこなってよろしいですか？',
    ]) ?>

    <?= $this->Form->hidden($tokenName, ['value' => $token, 'class' => ['js_upload_token']]) ?>

    <?= $this->element('Admin/FileGroups/fieldset', [
        'fileGroup' => $fileGroup,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'FileGroups',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery') + $selectFile,
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
