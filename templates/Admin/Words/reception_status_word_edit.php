<?php
$this->assign('title', '受付ステータス文言設定');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('受付ステータス文言設定');

$this->Html->script('admin/reception-status-word/edit', [
    'block' => true,
]);
?>

<?= $this->Flash->render('statusWordsFinish') ?>
<?= $this->Flash->render('statusWordsErrors') ?>


<?= $this->Form->create(new ArrayObject(['words' => $words]), [
    'type' => 'post',
    'url' => [
        'controller' => 'Words',
        'action' => 'reception-status-word-edit',
    ],
    'idPrefix' => 'reception-status-words-edit',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'context' => ['table' => 'ReceptionStatuses'],
    'data-confirm-title' => 'ステータス文言設定の編集',
    'data-confirm-message' => 'ステータス文言設定の編集をおこなってよろしいですか？',
]) ?>
    <section class="cmn-section">
        <table class="cmn-table">
            <thead>
            <tr>
                <th class="w70">&nbsp;</th>
                <th>設定対象ステータス名</th>
                <th>表示文言</th>
                <th>検索表示設定</th>
            </tr>
            <tbody class="js_sortable">
            <?php if (isset($words)): ?>
                <?php foreach ($words as $wordIndex => $wordData): ?>
                    <tr class="<?= h($wordIndex) ?>">
                        <td class="handle">
                            <svg class="icon is-drag">
                                <use xlink:href="#icon_drag"/>
                            </svg>
                        </td>
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
                        <td class="tac">
                            <?= $this->Template->checkbox('words.' . $wordIndex . '.search_display_flg', [
                                'type' => 'checkbox',
                                'id' => 'words-search_display_flg-' . $wordIndex,
                                'label' => ['class' => 'cmn-check btn-tool no-txt-label', 'text' => ''],
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
