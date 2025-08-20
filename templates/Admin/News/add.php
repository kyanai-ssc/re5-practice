<?php
$this->assign('title', 'お知らせ 新規登録');
$this->assign('headerType', 'data');

$this->Breadcrumbs->add(
    'お知らせ',
    ['prefix' => 'Admin', 'controller' => 'News', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '新規登録'
);

$this->Html->script('admin/news/fieldset', [
    'block' => true,
]);
?>
<section class="form-input">
    <?= $this->Flash->render('newsErrors') ?>

    <?= $this->Form->create($news, [
        'type' => 'post',
        'url' => [
            'controller' => 'News',
            'action' => 'add',
        ],
        'idPrefix' => 'news-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => 'お知らせの登録',
        'data-confirm-message' => 'お知らせの登録をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/News/fieldset', [
        'news' => $news,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'News',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ], [
            'class' => ['cmn-btn', 'is-reset', 'is-gray'],
        ]) ?>
        <?= $this->Form->button('プレビュー', [
            'type' => 'button',
            'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_news_preview_btn'],
        ]) ?>

        <?= $this->Form->button('登録', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>

    <?= $this->Form->create(null, [
        'type' => 'post',
        'target' => '_blank',
        'novalidate' => true,
        'url' => [
            'controller' => 'News',
            'action' => 'preview',
            'id' => 0
        ],
        'id' => 'js_preview_news',
    ]) ?>

    <?php $this->Form->unlockField('preview_title'); ?>
    <?php $this->Form->unlockField('preview_contents'); ?>
    <?php $this->Form->unlockField('preview_public_from'); ?>

    <?= $this->Form->end() ?>

</section>
