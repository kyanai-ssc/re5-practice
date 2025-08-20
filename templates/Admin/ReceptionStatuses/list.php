<?php

$this->assign('title', '受付状況 一覧');
$this->assign('headerType', 'data');

// $this->Html->script('admin/receptionstatuses/list', [
//     'block' => true,
// ]);
$this->Breadcrumbs->add(
    '受付状況 一覧'
);
$this->Html->script('admin/common/tableBtn', [
    'block' => true,
]);
?>
<?= $this->Flash->render('receptionStatusesFinish') ?>
<?= $this->Flash->render('receptionStatusesError') ?>

<section class="panel-show">
    <?= $this->element('Admin/ReceptionStatuses/search', [
        'searchForm' => $searchForm,
    ]) ?>
</section>
<!-- .panel-show -->
<section class="search-list mgt-20">
    <?php if ($searchExec): ?>
        <aside class="search-parts d-flex mgt-20">
            <div class="search-parts-left">
                <?= $this->element('Admin/Common/search/page_counter') ?>
            </div><!-- .search-counter -->
            <div class="search-parts-center">
                <?= $this->element('Admin/Common/search/paginator') ?>
            </div>
            <div class="search-parts-right">
                <ul class="d-flex">
                    <li>
                        <?= $this->element('Admin/Common/search/limit', [
                            'options' => [
                                'class' => ['js_change_search_limit'],
                                'data-url' => $this->Url->build([
                                    'prefix' => 'Admin',
                                    'controller' => 'ReceptionStatuses',
                                    'action' => 'list',
                                    '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                                ], ['escape' => false]),
                            ],
                        ]) ?>
                    </li>
                </ul>
            </div>
        </aside>
        <?php if (count($reservationForms) > 0): ?>
            <?php foreach ($reservationForms as $key => $reservationForm): ?>    
                <?php if ($key === 0) :?>
                <section class="panel-show">
                    <?= $this->element('Admin/ReceptionStatuses/detail', [
                        'reservationForm' => $reservationForm,
                        'mode' => 'detail',
                        'open' => '',
                        'display' => 'display:block;',
                    ]) ?>
                </section>
                <?php else:?>
                    <section class="panel-show">
                    <?= $this->element('Admin/ReceptionStatuses/detail', [
                        'reservationForm' => $reservationForm,
                        'mode' => 'detail',
                        'open' => 'opend',
                        'display' => 'display:none;',
                    ]) ?>
                </section>
                <?php endif ;?>
            <?php endforeach; ?>
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
        <?= $this->element('Admin/Common/search/paginator') ?>
    <?php endif; ?>
</section>
