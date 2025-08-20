<?php
$this->assign('title', '自動返信メール設定 新規登録');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '自動返信メール設定',
    ['prefix' => 'Admin', 'controller' => 'AutoReplyMails', 'action' => 'list']
);

$this->Breadcrumbs->add('新規登録');
?>

<?= $this->Html->script('admin/auto-reply-mails/fieldset', [
    'block' => true,
]); ?>

<section class="form-input">
    <?= $this->Flash->render('autoReplyMailsErrors') ?>

    <?= $this->Form->create($autoReplyMail, [
        'type' => 'post',
        'url' => [
            'controller' => 'AutoReplyMails',
            'action' => 'add',
        ],
        'idPrefix' => 'autoReplyMails-add',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '自動返信メールの登録',
        'data-confirm-message' => '自動返信メールの登録をおこなってよろしいですか？',
    ]) ?>

    <?= $this->element('Admin/AutoReplyMails/fieldset', [
        'autoReplyMail' => $autoReplyMail,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'AutoReplyMails',
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
