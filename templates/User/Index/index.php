<?php
$this->assign('title', $this->Tr->t('pageTitle/index'));
?>
<?php if ($this->Setting->getSiteSetting()->key_visual_url !== null) : ?>
    <section class="l-main l-mainVisual">
        <?= $this->Html->image($this->Setting->getSiteSetting()->key_visual_url) ?>
    </section>
<?php endif; ?>
<?php if ((!empty($formType) || !empty($tagList) || !empty($eventNameList)) && $this->Authority->isAuthority(true, 'Reservations', 'calendar')) : ?>
    <section class="contents-area l-main l-search">
        <h3 class="ttl-sec with-showBtn"><?= $this->Tr->h('index/searchTitle') ?></h3>
        <button type="button" id="showBtn"></button>
        <div id="search-group-wrap" class="search-group-wrap">
            <?= $this->Form->create(null, [
                'type' => 'post',
                'url' => $this->Template->getTopUrl(),
                'novalidate' => true,
            ]) ?>
            <?php if (!empty($formType)) : ?>
                <?= $this->Label->renderSelect([
                    'onlyPublic' => true,
                    'type' => $this->Configure->read('Master.label.type.other'),
                    'public' => true,
                    'userLabelId' => $this->CommonData->getUserLabelId(),
                ]) ?>
            <?php endif; ?>
            <!-- .search-group.-home-label-container -->
            <?php if (!empty($tagList)) : ?>
                <?= $this->element('User/Tags/search') ?>
            <?php endif; ?>
            <?php if (!empty($eventNameList)) : ?>
                <?= $this->element('User/Events/search') ?>
            <?php endif; ?>
            <div class="btn-group">
                <?= $this->Html->link($this->Tr->h('search/reset'), $this->Template->getTopUrl(), [
                    'class' => ['cmn-btn', 'is-reset', 'is-gray'],
                ]) ?>
                <button type="submit" class="cmn-btn is-blue"><?= $this->Tr->h('search/reservation') ?></button>
            </div>
            <?= $this->Form->end(); ?>
        </div>
        <!-- .search-group-wrap -->
    </section>
<?php endif; ?>
<?php if (!is_array($newsList) && $newsList->count() > 0): ?>
    <section class="contents-area l-main">
        <h3 class="ttl-sec"><?= $this->Tr->h('news/title') ?></h3>
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
        <!-- .news-group -->
        <p class="cmn-txt mgt-20 tac">
            <?= $this->Html->link($this->Tr->t('common/viewList'),
                ['prefix' => 'User', 'controller' => 'News', 'action' => 'list'],
                ['class' => ['cmn-btn', 'is-blue']]
            ); ?>
        </p>
    </section>
<?php endif; ?>
<?php if ($this->Setting->getSiteSetting()->get('top_information')) : ?>
    <section class="contents-area l-main">
        <div class="edit-area wysiwyg-area">
            <div class="cmn-txt mgb-20"><?= $this->Setting->getSiteSetting()->get('top_information') ?></div>
        </div>
    </section>
<?php endif; ?>
