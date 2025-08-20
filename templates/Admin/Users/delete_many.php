<?php


$this->assign('title', '顧客 一括削除確認');
$this->assign('headerType', 'data');

$this->Breadcrumbs->add(
    '顧客一覧',
    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'list']
);

$this->Breadcrumbs->add(
    '顧客 一括削除確認'
);

?>
<aside class="cmn-msg is-caution">
    <p>
        <svg class="icon is-msg">
            <use xlink:href="#icon_info"></use>
        </svg>
        削除確認画面遷移後に一覧画面に遷移した場合、チェック情報が更新されている可能性がありますのでご注意ください。
    </p>
</aside>

<section class="form-input">
    <?= $this->Flash->render('usersDeleteManyErrors') ?>
    <?= $this->Form->create($deleteManyForm, [
        'type' => 'post',
        'url' => [
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'deleteMany',
        ],
        'idPrefix' => 'users-delete-many',
        'novalidate' => true,
        'class' => ['js_submit_confirm'],
        'data-confirm-message' => '上記の情報で該当データをまとめて削除します。\nよろしいでしょうか？',
        'data-confirm-title' => 'データの削除',
        'data-confirm-html' => '削除したデータの復旧はできません。',
    ]) ?>

    <?= $this->Form->hidden('checked', ['value' => $checked]); ?>
    <div class="panel-show-set">
        <fieldset>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">削除対象件数</div>
                    </th>
                    <td>
                        <p class="cmn-txt"><?= h($users->count()) ?>件</p>
                    </td>
                </tr>
                <tr class="field-input">
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">削除対象ダウンロード</div>
                    </th>
                    <td>
                        <p class="cmn-txt">
                            <?= $this->Html->link('削除対象を確認する', ['controller' => 'Users', 'action' => 'downloadChecked', 'prefix' => 'Admin']) ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <div class="btn-box mgt-20">
        <?= $this->Html->link('戻る', [
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery')
        ], ['class' => ['cmn-btn', 'is-reset', 'is-gray']]) ?>
        <?= $this->Form->button('一括削除', [
            'type' => 'post',
            'class' => ['cmn-btn', 'is-pink', 'is-circle']
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>
