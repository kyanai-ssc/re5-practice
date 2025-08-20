<?php
$this->assign('title', '基本設定');
$this->assign('headerType', 'master');

$this->Breadcrumbs->add('基本設定');
$this->Html->script('admin/site-setting/edit', [
    'block' => true,
]);
?>
<section class="form-input">
<?= $this->Flash->render('siteSettingFinish') ?>
<?= $this->Flash->render('siteSettingErrors') ?>

<?= $this->Form->create($siteSetting, [
    'type' => 'post',
    'url' => $this->Url->build(["controller" => "SiteSetting","action" => "edit"]),
    'idPrefix' => 'system-add',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'context' => ['validator' => 'siteSetting'],
    'data-confirm-title' => '基本設定の編集',
    'data-confirm-message' => '基本設定の編集をおこなってよろしいですか？',
]) ?>

   <?= $this->element('Admin/SiteSetting/fieldset', [
        'siteSetting' => $siteSetting,
        'valueOptions' => $valueOptions,
        'mode' => 'edit',
    ]) ?>
    
    <div class="btn-box mgt-20">
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>

