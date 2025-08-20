<?php
$this->assign('title', '絞り込みキーワード設定 新規登録');
$this->assign('headerType', 'master');


$this->Breadcrumbs->add(
    '絞り込みキーワード設定',
    ['prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '新規登録'
);
?>
<section class="form-input">
<?= $this->Flash->render('tagsErrors') ?>

<?= $this->Form->create($tag, [
    'type' => 'post',
    'url' => [
        'controller' => 'Tags',
        'action' => 'add',
    ],
    'idPrefix' => 'tags-add',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'data-confirm-title' => '絞り込みキーワードの登録',
    'data-confirm-message' => '絞り込みキーワードの登録をおこなってよろしいですか？',
]) ?>
    <?= $this->element('Admin/Tags/fieldset', [
        'tag' => $tag,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Tags',
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
