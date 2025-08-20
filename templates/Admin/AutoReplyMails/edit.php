<?php
$this->assign('title', '自動返信メール設定 編集');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(
    '自動返信メール設定',
    ['prefix' => 'Admin', 'controller' => 'AutoReplyMails', 'action' => 'list']
);

$this->Breadcrumbs->add('編集');
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
            'action' => 'edit',
            'id' => $autoReplyMail->get('id'),
        ],
        'idPrefix' => 'autoReplyMails-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '自動返信メールの編集',
        'data-confirm-message' => '自動返信メールの編集をおこなってよろしいですか？',
    ]) ?>
    <?= $this->element('Admin/AutoReplyMails/fieldset', [
        'autoReplyMail' => $autoReplyMail,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
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
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
