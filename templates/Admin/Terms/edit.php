<?php
$this->assign('title', '利用規約・個人情報取り扱い・特商法 設定');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add(h($this->fetch('title')));
$this->Html->script('admin/system/edit', [
    'block' => true,
]);
?>

<section class="form-input">
    <?= $this->Flash->render('termsFinish') ?>
    <?= $this->Flash->render('termsErrors') ?>

    <?= $this->Form->create(new ArrayObject(['terms' => $terms]), [
        'type' => 'post',
        'url' => [
            'controller' => 'Terms',
            'action' => 'edit',
        ],
        'idPrefix' => 'terms-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'context' => ['table' => 'Terms'],
        'data-confirm-title' => '利用規約・個人情報取り扱い・特商法の編集',
        'data-confirm-message' => '利用規約・個人情報取り扱い・特商法の編集をおこなってよろしいですか？',

    ]) ?>
    <div class="form-input-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <?php foreach ($terms as $index => $term) : ?>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                <?= h($valueOptions['termName'][$term->get('type')]) ?>
                                <?= $this->Template->isRequire('terms.' . $index . '.contents') ?>
                            </div>
                        </th>
                        <td>
                            <?= $this->Form->hidden('terms.' . $index . '.id') ?>
                            <?= $this->Form->control('terms.' . $index . '.contents', [
                                'type' => 'textarea',
                                'label' => false,
                                'rows' => 10,
                                'class' => ['wysiwyg']
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
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
