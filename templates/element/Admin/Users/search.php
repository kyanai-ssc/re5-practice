<?php

use App\Model\Entity\AdminSearchItem;

?>
<div class="panel-show-wrap">
    <?= $this->Form->create($searchForm, [
        'type' => 'post',
        'url' => [
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery') + $selectUserQuery,
        ],
        'idPrefix' => 'users_search',
        'novalidate' => true,
        'class' => ['js_user_search_form'],
    ]) ?>
    <?= $this->element('Admin/Common/form/panel_show', [
        'panelTitle' => '顧客検索',
    ]) ?>
    <div class="showWrap">
        <?= $this->element('Admin/AdminSearchItems/items_search', [
            'searchForm' => $searchForm,
            'searchItems' => $searchForm->getSearchItems(),
        ]) ?>
        <div class="btn-box mgt-20">
            <?= $this->Form->button('リセット', [
                'type' => 'reset',
                'class' => ['js_change_url', 'cmn-btn', 'is-reset'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'Users',
                    'action' => 'list',
                    '?' => $selectUserQuery,
                ], ['escape' => false]),
            ]) ?>
            <?= $this->Form->button('検索', ['type' => 'submit', 'class' => 'cmn-btn is-blue']) ?>
        </div>
        <div class="tool-wrap">
            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_btn_setting"/></svg>', [
                'type' => 'button',
                'title' => '検索項目設定',
                'class' => ['js_select_search_items', 'btn-setting', ' toolBtn', 'tooltip'],
                'data-title' => '検索項目設定',
                'data-type' => AdminSearchItem::TYPE_USER_LIST,
                'escapeTitle' => false,
            ]) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>
