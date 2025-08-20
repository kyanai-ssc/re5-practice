<?php
$this->assign('title', $this->Tr->t('pageTitle/eventsDetail'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/eventsDetail')
);
$this->Html->script('user/events/detail', [
    'block' => true,
]);
if ($eventFrame) {
    $this->assign('noNavi', true);
}
?>
<section class="contents-area l-main">
    <div class="l-detail edit-area">
        <div class="detail-info">
            <div class="detail-top-box">
                <?php if ($event->has('label')): ?>
                    <dl class="detail-name">
                        <dt>
                            <?= $this->Tr->h('eventsDetail/labelName') ?>
                        </dt>
                        <dd>
                            <?= h($event->get('label')->get('name')) ?>
                        </dd>
                    </dl>
                <?php endif; ?>
                <dl class="detail-name">
                    <dt>
                        <?= $this->Tr->h('eventsDetail/eventName') ?>
                    </dt>
                    <dd>
                        <?= h($event->get('name')) ?>
                    </dd>
                </dl>
            </div>
            <?php if (!empty($event->get('event_remarks'))): ?>
                <?php foreach ($event->get('event_remarks') as $eventRemarks) : ?>
                    <h3 class="ttl-sec"><?= h($eventRemarks->get('name')) ?></h3>
                    <fieldset class="wysiwyg-area">
                        <?= $eventRemarks->get('remark') ?>
                    </fieldset>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if (((string)$event->get('description')) !== ''): ?>
            <div class="detail-txt wysiwyg-area">
                <?= $event->get('description') ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($event->get('event_images'))): ?>
            <div class="detail-img">
                <ul class="bxslider js_image_slider">
                    <?php foreach ($event->get('event_images') as $eventImage): ?>
                        <li>
                            <?= $this->Html->image($eventImage->get('url')) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (!$eventFrame): ?>
            <p class="cmn-txt mgt-40 tac mgb-20 btn-wrap clearfix">
                <?php if ($this->Authority->isAuthority(true, 'Reservations', 'calendar')) : ?>
                    <?= $this->Html->link(
                        $this->Tr->t('common/backBtn'),
                        [
                            'prefix' => 'User',
                            'controller' => 'Reservations',
                            'action' => 'calendar',
                            '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                        ],
                        ['class' => ['cmn-btn', 'is-gray']]
                    ); ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</section>
