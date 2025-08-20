<?php
$this->assign('title', '予約枠のカラー設定');
$this->assign('headerType', 'master');

$this->Html->script('vendor/jpicker/jpicker.min', [
    'block' => true,
]);

$this->Html->script('admin/color-chips/edit', [
    'block' => true,
]);

$this->Breadcrumbs->add('予約枠のカラー設定');
?>

<section class="form-input">
    <?= $this->Flash->render('colorChipsFinish') ?>
    <?= $this->Flash->render('colorChipsErrors') ?>

    <?= $this->Form->create(new ArrayObject(['colorChips' => $colorChips]), [
        'type' => 'post',
        'url' => [
            'controller' => 'ColorChips',
            'action' => 'edit',
        ],
        'idPrefix' => 'colorChips-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'context' => ['table' => 'ColorChips'],
        'data-confirm-title' => '予約枠のカラーの編集',
        'data-confirm-message' => '予約枠のカラーの編集をおこなってよろしいですか？',
    ]) ?>
    <?php $this->Form->unlockField('colorChips'); ?>

    <div class="panel-show-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">カラー</div>
                    </th>
                    <td>
                        <div class="colorInput">
                            <div class="colorInput-box">
                                <table class="colorInput-detail" id="colorTable">
                                    <thead>
                                    <tr>
                                        <th class="w30">&nbsp;</th>
                                        <th>タイプ</th>
                                        <th>カラー名</th>
                                        <th>カラー</th>
                                        <th>表示</th>
                                        <th>削除</th>
                                    </tr>
                                    </thead>
                                    <tbody class="js_colorChips_container js_sortable">
                                    <?php if (isset($colorChips)): ?>
                                        <?php foreach ($colorChips as $colorChipIndex => $colorChipData): ?>
                                            <?= $this->element('Admin/ColorChips/fieldset', [
                                                'colorChip' => $colorChips,
                                                'colorChipIndex' => $colorChipIndex,
                                                'colorChipData' => $colorChipData,
                                            ]) ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?= $this->Form->error('colorChips'); ?>
                                    </tbody>
                                </table>
                                <?= $this->Form->button('カラー追加', [
                                    'type' => 'button',
                                    'class' => ['js_add_input', 'cmn-btn', 'is-addCell', 'mgt-20'],
                                    'data-container' => '.js_colorChips_container',
                                    'data-html' => $this->element('Admin/ColorChips/fieldset', [
                                        'colorChip' => $colorChips,
                                        'colorChipIndex' => 'replace-index',
                                        'colorChipData' => null,
                                    ]),
                                    'data-index-element' => '.js_colorChips_index',
                                    'data-index-replace' => 'replace-index',
                                ]) ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </fieldset>
    </div>
    <div class="btn-box mgt-20">
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
