<?php
$this->assign('title', '文言設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add('文言設定');
 ?>

<?= $this->Flash->render('wordsFinish') ?>
<?= $this->Flash->render('wordsErrors') ?>

<?= $this->Form->create(new ArrayObject(['words' => $words]), [
    'type' => 'post',
    'url' => [
        'controller' => 'Words',
        'action' => 'word-edit',
    ],
    'idPrefix' => 'words-edit',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'context' => ['table' => 'Words'],
    'data-confirm-title' => '文言設定の編集',
    'data-confirm-message' => '文言設定の編集をおこなってよろしいですか？',

]) ?>
<section class="cmn-section ">
    <table class="cmn-table input-box">
        <thead>
        <tr>
            <th class="reId">コード番号</th>
            <th>設定箇所</th>
            <th>初期文言</th>
            <th>設定文言</th>
        </tr>
        <tbody>

        <?php if (isset($words)): ?>
            <?php foreach ($words as $wordIndex => $wordData): ?>
                <?= $this->element('Admin/Words/fieldset', [
                    'word' => $words,
                    'wordIndex' => $wordIndex,
                    'wordData' => $wordData,
                    'errorWords' => false,
                ]) ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <?= $this->Form->error('words'); ?>
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
