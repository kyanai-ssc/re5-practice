<?php
if (isset($preview) && $preview) {
    $addPrefix = __('（プレビュー）');
} else {
    $addPrefix = '';
}

$this->assign('title', $addPrefix . $this->Tr->t('pageTitle/newsDetailTitle'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/newsListTitle'),
    ['prefix' => 'User', 'controller' => 'News', 'action' => 'list']
);
$this->Breadcrumbs->add(
    h($news->title)
);
?>

<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= h($news->title) ?></h3>
    <p class="light-txt">
        <?php if (!is_null($news->public_from)): ?><?= h($this->Template->displayDayAndWeek($news->public_from, 'H:i')) ?><?php endif; ?>
        <?php if ($news->isDisplayNews()) : ?><span class="labe-new">NEW</span><?php endif; ?>
    </p>
    <div class="edit-area wysiwyg-area">
        <?= $news->contents ?>
    </div>
    <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
        <?= $this->Html->link(
            $this->Tr->t('news/backBtn'), [
            'prefix' => 'User',
            'controller' => 'News',
            'action' => 'list',
            'page' => $page],
            ['class' => ['cmn-btn', 'is-gray']]
        ); ?>
    </p>
</section>
