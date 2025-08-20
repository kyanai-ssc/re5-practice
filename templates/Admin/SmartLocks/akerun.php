<?php
$this->assign('title', 'Akerun設定');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('Akerun設定');
?>

<?= $this->Flash->render('akerunFinish') ?>
<?= $this->Flash->render('akerunErrors') ?>

<?= $this->Form->create($akerun, [
    'type' => 'post',
    'url' => [
        'controller' => 'SmartLocks',
        'action' => 'akerun',
    ],
    'idPrefix' => 'akerun',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'context' => ['table' => 'SmartLocks'],
    'data-confirm-title' => 'Akerun設定の編集',
    'data-confirm-message' => 'Akerun設定の編集をおこなってよろしいですか？',
]) ?>
    <section class="form-input">
        <div class="panel-show-set">
            <fieldset>
                <h3 class="ttl-s mgt-20 mgb-20">Akerun設定</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">Akerunユーザー名連携する会員項目</div>
                        </th>
                        <td>
                            <?= $this->Form->control('form_item_id', [
                                'type' => 'select',
                                'label' => false,
                                'class' => ['select'],
                                'options' => $valueOptions['formItemsForAkerun'],
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">アプリ<?= $this->Template->isRequire('app_use_flg') ?></div>
                        </th>
                        <td>
                            <?= $this->Template->radio('app_use_flg', [
                                'type' => 'radio',
                                'options' => $valueOptions['appUseFlg'],
                            ]) ?>
                            <div class="desc-wrap">
                                <p>
                                    ※すでに登録されている会員には適用されません。<br>
                                    ※すでに登録されている会員に適用するには、事前にAkerun管理画面よりユーザーを作成いただき、作成したAkerunユーザーIDを会員の編集画面から入力してください。
                                </p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </fieldset>
        </div>
        <div class="btn-box mgt-20">
            <?= $this->Form->button('編集', [
                'type' => 'submit',
                'class' => 'cmn-btn btn is-blue'
            ]) ?>
        </div>
    </section>
<?= $this->Form->end() ?>
