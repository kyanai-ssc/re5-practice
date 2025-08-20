<?php
$this->assign('headerType', 'master');
$this->assign('title', $this->Configure->read('Master.form.formType.' . $this->request->getParam('id')) . 'の項目設定');

$this->Html->script('admin/form-groups/fieldset', [
    'block' => true,
]);

$this->Breadcrumbs->add($this->Configure->read('Master.form.formType.' . $this->request->getParam('id')) . 'の項目設定');

?>
<section class="panel-show">
    <aside class="cmn-msg is-caution">
        <p>
            <svg class="icon is-msg">
                <use xlink:href="#icon_info"></use>
            </svg>
            グループや項目を削除すると、入力されていたデータも一緒に消えます。
        </p>
        <p>
            <svg class="icon is-msg">
                <use xlink:href="#icon_info"></use>
            </svg>
            100項目以上項目が設定されますと動作が不安定になる可能性がございます。項目の設定数は100個より少なく設定されることを推奨いたします。
        </p>
    </aside>

    <?= $this->Flash->render('formGroupsFinish') ?>
    <?= $this->Flash->render('formGroupsErrors') ?>
    <?= $this->Form->create(new ArrayObject(['form_groups' => $formGroups]), [
        'type' => 'post',
        'url' => $this->Url->build([
            'prefix' => 'Admin',
            'controller' => 'FormGroups',
            'action' => 'edit',
            'id' => $this->request->getParam('id'),
        ], ['escape' => false]),
        'idPrefix' => 'form-groups-edit',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'context' => ['table' => 'FormGroups'],
        'data-item-setting-title' => '項目設定',
        'data-confirm-title' => $this->Configure->read('Master.form.formType.' . $this->request->getParam('id')) . 'の項目設定の編集を実施します',
        'data-confirm-message' => $this->Configure->read('Master.form.formType.' . $this->request->getParam('id')).'の項目設定の編集をおこなってよろしいですか？',
    ]) ?>
    <?= $this->Token->getTokenTag() ?>
    <?= $this->Token->getTokenError() ?>
    <div class="panel-show-wrap mgt-20">
        <?= $this->element('Admin/FormGroups/fieldset', [
            'formType' => $this->request->getParam('id'),
            'formGroups' => $formGroups,
            'valueOptions' => $valueOptions,
            'mode' => 'edit',
        ]) ?>
    </div>
    <div class="btn-box mgt-20">
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
