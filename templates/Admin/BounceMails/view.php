<?php
$this->assign('title', '不達メール 詳細');
$this->assign('headerType', 'data');
$this->Breadcrumbs->add(
    '不達メール',
    ['prefix' => 'Admin', 'controller' => 'BounceMails', 'action' => 'list']
);
$this->Breadcrumbs->add(
    '詳細'
);
?>
<section class="form-input">
    <div class="panel-show-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">送信対象</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= h($this->Configure->read('Master.bounceMail.sendExclude.' . $bounceMail->send_exclude_flg)) ?>
                        </p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">メールアドレス</div>
                    </th>
                    <td>
                        <p class="cmn-txt"><?= h($bounceMail->mail) ?></p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">送信しないへ自動切り換え
                        </div>
                    </th>
                    <td>
                        <p class="cmn-txt">残<?= h($bounceMail->remaining_number) ?></p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">不達累積件数</div>
                    </th>
                    <td>
                        <p class="cmn-txt"><?= h($bounceMail->total_number) ?></p>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <section class="search-list mgt-20">
        <fieldset>
            <legend class="ttl-search mgb-20">
                <span class="ttl-s">エラーメール</span>
            </legend>
        </fieldset>
        <aside class="search-parts d-flex mgt-20">
            <div class="search-parts-left f-l">
                <?= $this->element('Admin/Common/search/page_counter') ?>
            </div>
        </aside>
        <aside class="search-parts">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <table class="cmn-table result-table mgt-10 mgb-10">
            <thead>
            <tr>
                <th class="reId">顧客ID</th>
                <th class="w-200">日時</th>
                <th class="w-200">ダウンロード</th>
            </tr>
            </thead>
            <tbody id="pliceTable_tbody">
            <?php foreach ($bounceMailHistories as $bounceMailHistory) : ?>
                <tr>
                    <td>
                        <?= h($bounceMailHistory->user_id) ?></td>
                    <td>
                        <?= h($this->Template->displayDayAndWeek($bounceMailHistory->created, ' H:i:s')) ?></td>
                    <td class="tool">
                        <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_download"/></svg>', [
                            'type' => 'button',
                            'class' => ['js_post_link_plural', 'btn-list', 'fileDown', 'is-download'],
                            'title' => '不達メールのダウンロード',
                            'aria-describedby' => 'ui-id-60',
                            'data-url' => $this->Url->build([
                                'prefix' => 'Admin',
                                'controller' => 'BounceMails',
                                'action' => 'download',
                                'id' => $bounceMailHistory->id,
                            ], ['escape' => false]),
                            'escapeTitle' => false,
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <aside class="search-parts mgt-20 ">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
    </section>
</section>
