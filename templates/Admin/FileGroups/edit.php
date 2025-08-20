<?php
$this->assign('headerType', 'data');
$this->assign('title', 'ファイル管理 編集');
$this->Breadcrumbs->add(
    'ファイル管理',
    ['prefix' => 'Admin', 'controller' => 'FileGroups', 'action' => 'list']
);
$this->Breadcrumbs->add('編集');

?>
<section class="form-input">
    <?= $this->Flash->render('fileGroupsErrors') ?>
    
    <?= $this->Form->create($fileGroup, [
        'type' => 'post',
        'url' => [
            'controller' => 'FileGroups',
            'action' => 'edit',
            'id' => $fileGroup->get('id'),
        ],
        'idPrefix' => 'fileGroups-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-id' => $fileGroup->id,
        'data-confirm-title' => 'ファイルの編集',
        'data-confirm-message' => 'ファイルの編集をおこなってよろしいですか？',
    ]) ?>

    <?= $this->Form->hidden($tokenName, ['value' => $token, 'class' => ['js_upload_token']]) ?>
    
    <?= $this->element('Admin/FileGroups/fieldset', [
        'fileGroup' => $fileGroup,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
    ]) ?>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'FileGroups',
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
