<?php

use App\Model\Entity\AccessSummary;

$this->assign('title', 'トップページ');
$this->Html->script('admin/index/announce', [
    'block' => true,
]);
?>

<div class="cmn-contents-wrap l-main">
    <aside class="cmn-msg is-info hidden" id="announce">
        <p class="mgb-10"><span>お知らせ</span></p>
        <div id="announce-body-base" class="hidden">
            <p>
                <a href="#" data-html=":body" data-title=":title" class="js_common_dialog">:title</a>
            </p>
        </div>
        <div id="announce-body">
        </div>
    </aside>
    <section class="panel-show">
        <h3 class="ttl-s mgt-40 mgb-20">ご利用状況</h3>
        <table class="cmn-table top-table">
            <thead>
            <tr>
                <th>ご契約プラン</th>
                <th>会員登録数</th>
                <th>メール配信数（月）</th>
            </tr>
            </thead>
            <tbody>
            <tr class="parent">
                <td>
                    <?= h($this->Configure->read('Master.systemSetting.contractPlan.' . $systemSetting->contract_plan)) ?>
                </td>
                <td>
                    <?= h($userCount) ?>
                    /
                    <?php if (!is_null($this->Configure->read('Master.systemSetting.restriction.user.' . $systemSetting->contract_plan))): ?>
                    <?= h($this->Configure->read('Master.systemSetting.restriction.user.' . $systemSetting->contract_plan)) ?></td>
                <?php else: ?>
                    無制限
                <?php endif; ?>
                <td>
                    <?= h($mailCount) ?>
                    /
                    <?php if (!is_null($restrictionMail)): ?>
                        <?= h($restrictionMail) ?>
                    <?php else: ?>
                        無制限
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php if (isset($appSetting) && $appSetting->api_secret !== ''): ?>
        <h3 class="ttl-s mgt-50 mgb-20">アプリ設定</h3>
        <table class="cmn-table top-table">
            <thead>
            <tr>
                <th>API シークレット</th>
            </tr>
            </thead>
            <tbody>
            <tr class="parent">
                <td><?= h($appSetting->getDecryptApiSecret($appSetting->api_secret)); ?></td>
            </tr>
            </tbody>
        </table>
        <?php endif; ?>
        <h3 class="ttl-s mgt-50 mgb-20">前日のアクセス数</h3>
        <table class="cmn-table top-table">
            <thead>
            <tr>
                <th>会員登録数</th>
                <th>予約数</th>
                <th>カレンダー画面表示数</th>
            </tr>
            </thead>
            <tbody>
            <tr class="parent">
                <?php if ($accessSummaries instanceof AccessSummary) : ?>
                    <td><?= h($accessSummaries->users) ?>件</td>
                    <td><?= h($accessSummaries->reservations) ?>件</td>
                    <td><?= h($accessSummaries->calendar) ?>件</td>
                <?php else : ?>
                    <td>0件</td>
                    <td>0件</td>
                    <td>0件</td>
                <?php endif; ?>
            </tr>
            </tbody>
        </table>
    </section><!-- .panel-show -->
</div>
