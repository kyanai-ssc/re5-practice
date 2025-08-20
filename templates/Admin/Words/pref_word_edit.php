<?php
$this->assign('title', '都道府県文言設定');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('都道府県文言設定');
?>

<?= $this->Flash->render('prefWordsFinish') ?>
<?= $this->Flash->render('prefWordsErrors') ?>

<?= $this->Form->create(new ArrayObject(['words' => $words]), [
    'type' => 'post',
    'url' => [
        'controller' => 'Words',
        'action' => 'pref-word-edit',
    ],
    'idPrefix' => 'pref-words-edit',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'context' => ['table' => 'Prefectures'],
    'data-confirm-title' => '都道府県文言設定の編集',
    'data-confirm-message' => '都道府県文言設定の編集をおこなってよろしいですか？',
]) ?>
<section class="cmn-section ">
    <table class="cmn-table input-table">
        <thead>
        <tr>
            <th>設定対象都道府県名</th>
            <th>表示文言</th>
        </tr>
        </thead>
        <tbody>
        <?php if (isset($words)): ?>
            <?php foreach ($words as $wordIndex => $wordData): ?>
                <tr class="field-input <?= h($wordIndex) ?>">
                    <td>
                        <?= $this->Form->hidden('words.' . $wordIndex . '.id') ?>
                        <?= h($wordData['default_name']) ?>
                    </td>
                    <td>
                        <?= $this->Form->control('words.' . $wordIndex . '.name', [
                            'type' => 'text',
                            'label' => false,
                        ]) ?>
                    </td>
                </tr>
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
