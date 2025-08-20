<?php
$this->assign('title', $this->Tr->t('pageTitle/waitingCancellation/list'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/waitingCancellation/list')
);
?>
<section class="contents-area l-main">
    <?= $this->Flash->render('waitingCancellationFinish') ?>
    <?= $this->Flash->render('waitingCancellationErrors') ?>
    <div class="mgt-20">
        <div class="cmn-list_head sticky pc-only">
            <ul>
                <li><?= $this->Tr->h('waitingCancellation/eventName') ?></li>
                <li><?= $this->Tr->h('waitingCancellation/usageTimestamp') ?></li>
                <li></li>
            </ul>
            <!-- .is-listOnly -->
        </div>
        <?php if (!empty($waitingCancellations->toArray())) : ?>
            <div class="cmn-list_body">
                <ul>
                    <?php foreach ($waitingCancellations as $waitingCancellation) : ?>
                        <li class="list_body_line_wrap">
                            <!-- .schedule-header -->
                            <ul class="list_body_line">
                                <li class="b-link">
                                    <?php if ($this->Authority->isAuthority(true, 'Events', 'view')): ?>
                                        <?= $this->Html->link($waitingCancellation->event->name,
                                            ['prefix' => 'User', 'controller' => 'Events', 'action' => 'view', 'id' => $waitingCancellation->event_id, 'frame' => 1],
                                            ['target' => '_blank']
                                        ); ?>
                                    <?php else : ?>
                                        <span class="pdl-20"><?= h($waitingCancellation->event->name) ?></span>
                                    <?php endif; ?>
                                </li>
                                <li>
                                    <?= h($this->Template->displayDayAndWeek($waitingCancellation->usage_timestamp, 'H:i')) ?>
                                </li>
                                <li class="tac">
                                    <?= $this->Html->link($this->Tr->t('waitingCancellation/releaseBtn'), '#', [
                                        'escapeTitle' => false,
                                        'class' => ['js_post_confirm', 'cmn-btn', 'is-blue'],
                                        'data-confirm-title' => $this->Tr->t('waitingCancellation/dialog/title'),
                                        'data-confirm-message' => $this->Tr->t('waitingCancellation/dialog/message'),
                                        'data-url' => $this->Url->build(['prefix' => 'User',
                                            'controller' => 'WaitingCancellation',
                                            'action' => 'release',
                                            'id' => $waitingCancellation->id,], ['escape' => false]),]) ?>
                                </li>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <aside class="search-parts mgt-20">
                <?= $this->element('User/Common/search/paginator') ?>
            </aside><!-- .search-parts -->
        <?php else : ?>
            <p class="cmn-txt mgt-20"><?= $this->Tr->h('waitingCancellation/noList') ?></p>
        <?php endif; ?>
    </div>
</section>
