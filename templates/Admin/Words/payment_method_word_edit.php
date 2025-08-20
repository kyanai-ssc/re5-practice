<?php
$this->assign('title', '決済方法文言設定');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('決済方法文言設定');

?>
<?= $this->Flash->render('paymentMethodWordsFinish') ?>
<?= $this->Flash->render('paymentMethodWordsErrors') ?>

<?= $this->Form->create(new ArrayObject(['words' => $words]), [
    'type' => 'post',
    'url' => [
        'controller' => 'Words',
        'action' => 'payment-method-word-edit',
    ],
    'idPrefix' => 'payment-method-word-edit',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'context' => ['table' => 'Words'],
    'data-confirm-title' => '決済方法文言設定の編集',
    'data-confirm-message' => '決済方法文言設定の編集をおこなってよろしいですか？',

]) ?>
<section class="cmn-section ">
    <table class="cmn-table input-table">
        <thead>
        <tr>
            <th>デフォルト表示名</th>
            <th>表示文言</th>
            <th>予約サイト表示</th>
        </tr>
        </thead>
        <tbody>
        <?php if (isset($words)): ?>
            <?php foreach ($words as $wordIndex => $wordData): ?>
                <tr class="field-input <?= h($wordIndex) ?>">
                    <?= $this->Form->hidden('words.' . $wordIndex . '.id') ?>
                    <td>
                        <?= h($this->Configure->read('Master.payment.method.' . $wordData['type'])) ?>
                        <?php if ($this->Setting->isPaymentServiceGmo() && !$wordData->isPaymentServiceMethod()): ?>
                            <div class="desc-wrap">
                                <p>
                                    GMOペイメントゲートウェイ社の決済システムをご利用の場合は使用できません。
                                </p>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $this->Form->control('words.' . $wordIndex . '.name', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['textbox_wFull']
                        ]) ?>
                    </td>
                    <td>
                        <?= h($this->Configure->read('Master.payment.display.' . $wordData['display_flg'])) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <div class="btn-box mgt-20">
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => 'cmn-btn btn is-blue'
        ]) ?>
    </div>
</section>
<?= $this->Form->end() ?>
