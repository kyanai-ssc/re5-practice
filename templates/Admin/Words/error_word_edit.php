<?php
$this->assign('title', 'エラー文言設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add('エラー文言設定');
?>

<?= $this->Flash->render('errorWordsFinish') ?>
<?= $this->Flash->render('errorWordsErrors') ?>


<?= $this->Form->create(new ArrayObject(['words' => $words]), [
    'type' => 'post',
    'url' => [
        'controller' => 'Words',
        'action' => 'error-word-edit',
    ],
    'idPrefix' => 'error-words-edit',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'context' => ['table' => 'Words'],
    'data-confirm-title' => 'エラー文言の編集を実施します',
    'data-confirm-message' => 'エラー文言の編集を実施します。よろしいでしょうか。',
]) ?>
<section class="cmn-section ">
    <table class="cmn-table">
        <thead>
        <tr>
            <th class="reId">ID</th>
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
                    'errorWords' => true,
                ]) ?>
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
