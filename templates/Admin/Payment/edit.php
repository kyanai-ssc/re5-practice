<?php
$this->assign('title', '決済情報');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add('決済情報', [
    'prefix' => 'Admin',
    'controller' => 'Payment',
    'action' => 'view',
]);
$this->Breadcrumbs->add('編集');
?>
<section class="form-input">
    <?= $this->Flash->render('paymentSettingErrors') ?>
    <?= $this->Form->create($paymentSetting, [
        'type' => 'post',
        'url' => [
            'controller' => 'Payment',
            'action' => 'edit',
        ],
        'idPrefix' => 'payment-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-title' => '決済情報の編集',
        'data-confirm-message' => '決済情報の編集をおこなってよろしいですか？',
    ]) ?>
        <?= $this->element('Admin/Payment/fieldset', [
            'paymentSetting' => $paymentSetting,
            'mode' => 'edit',
        ]) ?>
        <div class="btn-box mgt-20">
            <?= $this->Html->link(
                '戻る',
                [
                    'prefix' => 'Admin',
                    'controller' => 'Payment',
                    'action' => 'view',
                ],
                [
                    'class' => ['cmn-btn', 'is-reset', 'is-gray'],
                ]
            ) ?>
            <?= $this->Form->button('編集', [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue'],
            ]) ?>
        </div>
    <?= $this->Form->end() ?>
</section>
