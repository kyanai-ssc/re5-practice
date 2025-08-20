<?php
$this->assign('title', '決済ステータス文言設定');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('決済ステータス文言設定');
?>

<?= $this->Flash->render('paymentStatusWordsFinish') ?>
<?= $this->Flash->render('paymentStatusWordsErrors') ?>

<?= $this->Form->create(new ArrayObject(['words' => $words]), [
    'type' => 'post',
    'url' => [
        'controller' => 'Words',
        'action' => 'payment-status-word-edit',
    ],
    'idPrefix' => 'payment-status-word-edit',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'context' => ['table' => 'Words'],
    'data-confirm-title' => '決済ステータス文言設定の編集',
    'data-confirm-message' => '決済ステータス文言設定の編集をおこなってよろしいですか？',
]) ?>
<section class="cmn-section ">
    <table class="cmn-table input-table">
        <thead>
        <tr>
            <th>デフォルト表示名</th>
            <th>表示文言</th>
        </tr>
        </thead>
        <tbody>
        <?php if (isset($words)): ?>
            <?php foreach ($words as $wordIndex => $wordData): ?>
                <tr class="field-input <?= h($wordIndex) ?>">
                    <td>
                        <?= $this->Form->hidden('words.' . $wordIndex . '.id') ?>
                        <?= h($this->Configure->read('Master.payment.status.' . $wordData['type'])) ?>
                    </td>
                    <td>
                        <?= $this->Form->control('words.' . $wordIndex . '.name', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['textbox_wFull']
                        ]) ?>
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
