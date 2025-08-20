<?php
$this->assign('title', $this->Tr->t('pageTitle/newsListTitle'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/newsListTitle')
);
?>
<section class="contents-area l-main">
    <h3 class="ttl-sec"><?= $this->Tr->h('pageTitle/newsListTitle') ?></h3>
    <?php if (!empty($newsList->count() > 0)) : ?>
        <div class="news-group">
            <ul>
                <?php foreach ($newsList as $news) : ?>
                    <li>
                        <a href="<?= $this->Url->build([
                            'prefix' => 'User',
                            'controller' => 'News',
                            'action' => 'detail',
                            'id' => $news->id,
                        ], ['escape' => false]); ?>">
                            <div class="news-ttl"><?= h($news->title) ?></div>
                            <div class="news-data">
                                <time><?= h($this->Template->displayDayAndWeek($news->public_from, 'H:i')) ?></time>
                                <?php if ($news->isDisplayNews()) : ?>
                                    <span class="labe-new"><?= $this->Tr->h('news/new') ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <aside class="search-parts mgt-20">
            <?= $this->element('User/Common/search/paginator') ?>
        </aside><!-- .search-parts -->
    <?php else : ?>
        <p class="cmn-txt mgt-20"><?= $this->Tr->h('news/noList') ?></p>
    <?php endif; ?>
    <!-- .news-group -->
    <?php if ($this->Authority->isAuthority(true, 'Index', 'index')) : ?>
        <p class="cmn-txt mgt-20 tac">
            <?= $this->Template->userTopBtn() ?>
        </p>
    <?php endif; ?>
</section>
