<?php

use App\Model\Entity\RecaptchaSetting;

$this->assign('title', 'reCAPTCHAv3設定');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('reCAPTCHAv3設定');
$this->Html->script('admin/recaptcha/edit', [
    'block' => true,
]);
?>

<?= $this->Flash->render('recaptchaFinish') ?>
<?= $this->Flash->render('recaptchaErrors') ?>

<?= $this->Form->create($recaptchaSetting, [
    'type' => 'post',
    'url' => [
        'controller' => 'Recaptcha',
        'action' => 'edit',
    ],
    'idPrefix' => 'edit',
    'novalidate' => true,
    'class' => ['js_submit_confirm'],
    'context' => ['table' => 'RecaptchaSettings'],
    'data-confirm-title' => 'reCAPTCHAv3設定の編集',
    'data-confirm-message' => 'reCAPTCHAv3設定の編集をおこなってよろしいですか？',
]) ?>
    <section class="form-input">
        <div class="panel-show-set">
            <fieldset>
                <h3 class="ttl-s mgt-20 mgb-20">reCAPTCHAv3設定</h3>
                <div class="recaptcha_test">
                    <?= $this->Form->button('登録した設定のテスト', [
                        'type' => 'button',
                        'class' => ['cmn-btn', 'is-blue', 'js_open_window'],
                        'data-url' => $this->Url->build([
                            'prefix' => 'Admin',
                            'controller' => 'Recaptcha',
                            'action' => 'test',
                        ], ['escape' => false]),
                    ]) ?>
                </div>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">利用設定<?= $this->Template->isRequire('use_flg', ['always' => true]) ?></div>
                        </th>
                        <td>
                            <div class="js_use_flg_container"
                                data-on="<?= h(RecaptchaSetting::USE_FLG_ON) ?>"
                                data-off="<?= h(RecaptchaSetting::USE_FLG_OFF) ?>"
                            >
                                <?= $this->Template->radio('use_flg', [
                                    'type' => 'radio',
                                    'options' => $valueOptions['useFlg'],
                                ]) ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap js_required_on_use">サイトキー<?= $this->Template->isRequire('site_key', ['always' => true]) ?></div>
                        </th>
                        <td>
                            <?= $this->Form->control('site_key', [
                                'type' => 'text',
                                'label' => false,
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap  js_required_on_use">シークレットキー<?= $this->Template->isRequire('secret_key', ['always' => true]) ?></div>
                        </th>
                        <td>
                            <?= $this->Form->control('secret_key', [
                                'type' => 'text',
                                'value' => '',
                                'label' => false,
                            ]) ?>
                            <div class="desc-wrap">
                                <p>
                                    ※サイトキーのみ修正する場合もシークレットキーを再度入力してください。
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
