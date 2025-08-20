<?php
?>
<div class="panel-show-wrap">
    <?= $this->Form->create($searchForm, [
        'type' => 'post',
        'url' => [
            'prefix' => 'Admin',
            'controller' => 'ReceptionStatuses',
            'action' => 'list',
            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
        ],
        'idPrefix' => 'reservations_search',
        'novalidate' => true,
        'class' => ['js_reservation_search_form'],
    ]) ?>
    <?= $this->element('Admin/Common/form/panel_show', [
        'panelTitle' => '予約検索',
    ]) ?>
    <div class="showWrap">
        <?= $this->element('Admin/ReceptionStatuses/items_search') ?>
        <div class="btn-box mgt-20">
            <?= $this->Form->button('リセット', [
                'type' => 'reset',
                'class' => ['js_change_url', 'cmn-btn', 'is-reset'],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'ReceptionStatuses',
                    'action' => 'list',
                ], ['escape' => false]),
            ]) ?>
            <?= $this->Form->button('検索', ['type' => 'submit', 'class' => 'cmn-btn is-blue']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>
