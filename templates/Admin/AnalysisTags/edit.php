<?php
$this->assign('title', '計測タグ設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add(h($this->fetch('title')));
?>

<section class="form-input">
    <?= $this->Flash->render('analysisTagsFinish') ?>
    <?= $this->Flash->render('analysisTagsErrors') ?>

    <?= $this->Form->create(new ArrayObject(['analysisTags' => $analysisTags]), [
        'type' => 'post',
        'url' => [
            'controller' => 'AnalysisTags',
            'action' => 'edit',
        ],
        'idPrefix' => 'analysisTags-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'context' => ['table' => 'AnalysisTags'],
        'data-confirm-title' => '計測タグの編集',
        'data-confirm-message' => '計測タグの編集をおこなってよろしいですか？',

    ]) ?>

    <div class="form-input-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <?php foreach ($analysisTags as $index => $analysisTag) : ?>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div
                                class="ttl-input-wrap"><?= h($valueOptions['analysisTagName'][$analysisTag->get('type')]) ?><?= $this->Template->isRequire('analysisTags.' . $index . '.contents') ?></div>
                        </th>
                        <td>
                            <?= $this->Form->hidden('analysisTags.' . $index . '.id', ['value' => $analysisTag->get('id')]) ?>
                            <?= $this->Form->hidden('analysisTags.' . $index . '.type', ['value' => $analysisTag->get('type')]) ?>
                            <?= $this->Form->control('analysisTags.' . $index . '.contents', [
                                'type' => 'textarea',
                                'label' => false,
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
