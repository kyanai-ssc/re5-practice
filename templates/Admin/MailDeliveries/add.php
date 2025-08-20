<?php
$this->assign('title', 'メール配信登録');
$this->assign('headerType', 'data');

$this->Breadcrumbs->add(
    '顧客一覧',
    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'list']
);

$this->Breadcrumbs->add(
    'メール配信登録'
);

$this->Html->script('admin/mail-deliveries/fieldset', [
    'block' => true,
]);
?>

<section class="form-input">
    <?= $this->Flash->render('mailDeliveriesErrors') ?>
    <?= $this->Form->create($mailDelivery, [
        'type' => 'post',
        'url' => [
            'controller' => 'MailDeliveries',
            'action' => 'add',
        ],
        'idPrefix' => 'mailDeliverys-add',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>

    <?= $this->Token->getTokenError(); ?>
    <?= $this->Token->getTokenTag(); ?>

    <?= $this->Form->hidden('checked', ['value' => $checked]); ?>

    <?= $this->element('Admin/MailDeliveries/fieldset', [
        'mailDelivery' => $mailDelivery,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery')
        ],
            [
                'class' => ['cmn-btn', 'is-reset', 'is-gray'],
            ]) ?>

        <?= $this->Form->button('確認', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
<?= $this->Form->end() ?>
