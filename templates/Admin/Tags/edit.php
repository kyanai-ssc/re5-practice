<?php
$this->assign('title', '絞り込みキーワード設定 編集');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '絞り込みキーワード設定',
    ['prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '編集'
);?>

<section class="form-input">
<?= $this->Flash->render('tagsErrors') ?>

<?= $this->Form->create($tag, [
    'type' => 'post',
    'url' => [
        'controller' => 'Tags',
        'action' => 'edit',
        'id' => $tag->get('id'),
    ],
    'idPrefix' => 'tags-edit',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'data-confirm-title' => '絞り込みキーワードの編集',
    'data-confirm-message' => '絞り込みキーワードの編集をおこなってよろしいですか？',
]) ?>
    <?= $this->element('Admin/Tags/fieldset', [
        'tag' => $tag,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
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
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
