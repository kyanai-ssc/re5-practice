<?php
$this->assign('title', 'メール配信内容確認');
$this->assign('headerType', 'data');
$this->Breadcrumbs->add(
    '顧客一覧',
    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'list']
);

$this->Breadcrumbs->add(
    'メール配信内容確認'
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
            'action' => 'add-conf',
        ],
        'idPrefix' => 'mailDeliverys-add',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>

    <?= $this->Token->getTokenError(); ?>
    <?= $this->Token->getTokenTag(); ?>

    <?= $this->element('Admin/MailDeliveries/fieldset_conf', [
        'mailDelivery' => $mailDelivery,
        'valueOptions' => $valueOptions,
        'mode' => 'add',
    ]) ?>

    <h3 class="ttl-s mgt-50 mgb-20">テストメール</h3>
    <div class="panel-show-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">テスト</div>
                    </th>
                    <td>
                        <?= $this->Form->control('test_mail', [
                            'type' => 'text',
                            'label' => false,
                        ]) ?>

                        <?= $this->Form->button('左のメールアドレス宛にテストメール送信', [
                            'type' => 'button',
                            'class' => ['js_send_test_mail', 'cmn-btn', 'is-blue', 'is-circle'],
                        ]) ?>

                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>

    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'MailDeliveries',
            'action' => 'add',
            '?' => $this->Configure->read('Setting.formInput.backQuery'),
        ],
            [
                'class' => ['cmn-btn', 'is-reset', 'is-gray'],
            ]) ?>

        <?= $this->Form->button('登録', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
<?= $this->Form->end() ?>
