<?php

use App\Model\Entity\SiteSetting;

$this->assign('title', 'お知らせ 設定編集');
$this->assign('headerType', 'data');

$this->Breadcrumbs->add(
    'お知らせ',
    ['prefix' => 'Admin', 'controller' => 'News', 'action' => 'list']
);

$this->Breadcrumbs->add('設定編集');

$this->Html->script('admin/news/setting', [
    'block' => true,
]);
?>
<section class="form-input">
    <?= $this->Flash->render('newsSettingErrors') ?>

    <?= $this->Form->create($siteSetting, [
        'type' => 'post',
        'url' => $this->Url->build(["controller" => "news", "action" => "setting"]),
        'idPrefix' => 'news-setting',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'context' => ['validator' => 'newsSetting'],
        'data-confirm-title' => '設定の編集を実施します',
        'data-confirm-message' => '設定の編集を実施します。よろしいでしょうか。',
    ]) ?>

    <div class="form-input-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            NEW表示
                            <?= $this->Template->isRequire('news_new_period_number_hour') ?>
                        </div>
                    </th>
                    <td class="d-flex">
                        <div class="js_news_new_period_number_hour">
                            <?= $this->Form->control('news_new_period_number_hour', [
                                'type' => 'select',
                                'label' => false,
                                'class' => ['select'],
                                'options' => $valueOptions['newsNewPeriodTime'],
                            ]) ?>
                        </div>
                        <div class="js_news_new_period_number_day">
                            <?= $this->Form->control('news_new_period_number_day', [
                                'type' => 'select',
                                'label' => false,
                                'class' => ['select'],
                                'options' => $valueOptions['newsNewPeriodDay'],
                            ]) ?>
                        </div>

                        <?= $this->Form->control('news_new_period_type', [
                            'type' => 'select',
                            'label' => false,
                            'class' => ['select'],
                            'options' => $valueOptions['newsNewPeriodType'],
                            'data-time' => SiteSetting::NEWS_NEW_PERIOD_TYPE_TIME,
                            'data-day' => SiteSetting::NEWS_NEW_PERIOD_TYPE_DAY,
                        ]) ?>

                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">トップページ表示数<?= $this->Template->isRequire('top_news_number') ?></div>
                    </th>
                    <td>
                        <?= $this->Form->control('top_news_number', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['textbox_w150']
                        ]) ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'News',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ], [
            'class' => ['cmn-btn', 'is-reset', 'is-gray'],
        ]) ?>
        <?= $this->Form->button('編集', [
            'type' => 'submit',
            'class' => ['cmn-btn', 'is-blue'],
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
