<?php
$this->assign('title', 'お知らせ 編集');
$this->assign('headerType', 'data');
$this->Breadcrumbs->add(
    'お知らせ',
    ['controller' => 'News', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '編集'
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
            'action' => 'edit',
            'id' => $news->get('id'),
        ],
        'idPrefix' => 'news-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => 'お知らせの編集',
        'data-confirm-message' => 'お知らせの編集をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/News/fieldset', [
        'news' => $news,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
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

        <?= $this->Form->button('編集', [
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
