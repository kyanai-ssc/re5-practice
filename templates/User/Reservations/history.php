<?php

use \App\Model\Entity\Event;
use App\Model\Entity\SystemSetting;

$this->assign('title', $this->Tr->t('pageTitle/reservationsHistory'));
$this->Breadcrumbs->add(
    $this->Tr->t('pageTitle/reservationsHistory')
);

$this->Html->script('user/common/history', [
    'block' => true,
]);
$this->Html->script('user/reservations/history', [
    'block' => true,
]);
?>
<section class="contents-area l-main l-search">
    <h3 class="ttl-sec with-showBtn"><?= $this->Tr->h('reservationsHistory/search') ?></h3>
    <button type="button" id="showBtn"></button>
    <div id="history-group-wrap" class="history-group-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'history',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'history_search',
            'novalidate' => true,
        ]) ?>
        <dl>
            <dt><?= $this->Tr->h('reservationsHistory/usedatetime') ?></dt>
            <dd>
                <div class="dataInput d-flex">
                    <?= $this->Form->control('usage_timestamp.from', [
                        'type' => 'text',
                        'class' => ['js-datepicker-time', 'js-characters-change']
                    ]) ?>
                    <span class="txt mgl-10 mgr-10"><?= $this->Tr->h('common/fromToSeparate') ?></span>
                    <?= $this->Form->control('usage_timestamp.to', [
                        'type' => 'text',
                        'class' => ['js-datepicker-time', 'js-characters-change']
                    ]) ?>
                </div>
            </dd>
        </dl>
        <?php if (!empty($valueOptions['reservationStatusId'])) : ?>
        <dl>
            <dt><?= $this->Tr->h('reservationsHistory/status') ?></dt>
            <dd>
                <ul class="statusInput d-flex">
                    <?= $this->Template->checkbox('status', [
                        'type' => 'multicheckbox',
                        'label' => false,
                        'options' => $valueOptions['reservationStatusId'],
                    ]) ?>
                </ul>
            </dd>
            <?php endif; ?>
        </dl>
        <div class="btn-group">
            <?= $this->Form->button($this->Tr->t('search/reset'), [
                'type' => 'button',
                'class' => ['cmn-btn', 'is-reset', 'js_change_url'],
                'data-url' => $this->Url->build([
                    'prefix' => 'User',
                    'controller' => 'Reservations',
                    'action' => 'history',
                ], ['escape' => false]),
            ]) ?>
            <?= $this->Form->button($this->Tr->t('search/history'), ['type' => 'submit', 'class' => 'cmn-btn is-blue']) ?>
        </div>
        <?= $this->Form->end(); ?>
    </div>
    <!-- .history-group-wrap -->
</section>
<section class="contents-area l-main l-calendar">
    <div class="schedule-header sticky pc-only">
        <div class="is-listOnly pc-only">
            <div class="history_list_head">
                <ul>
                    <li class="h-statusR"><?= $this->Tr->h('reservationsHistory/status') ?></li>
                    <li class="h-name"><?= $this->Tr->h('reservationsHistory/eventName') ?></li>
                    <li class="h-dayTime"><?= $this->Tr->h('reservationsHistory/usedatetime') ?></li>
                    <?php if ($this->Setting->getSystemSetting()->payment_use_flg === SystemSetting::PAYMENT_USE_FLG_ON) : ?>
                        <li class="h-statusP"><?= $this->Tr->h('reservationsHistory/paymentStatus') ?></li>
                    <?php endif; ?>
                    <li class="h-confirm"><?= $this->Tr->h('reservationsHistory/detail') ?></li>
                </ul>
            </div>
            <!-- .reserve_list_head -->
        </div>
        <!-- .is-listOnly -->
    </div>
    <?php if (!empty($reservations)) : ?>
        <!-- .schedule-header -->
        <div class="history_list_body">
            <?php foreach ($reservations as $reservation) : ?>
                <ul>
                    <li class="list_body_line_wrap clearfix">
                        <ul class="list_body_line">
                            <li class="b-statusR <?= h($this->Configure->read('Master.reservation.statusClass.' . $reservation->reservation_status->status_type)) ?>">
                                <?= h($this->Master->getReservationStatusName($reservation->reservation_status_id)) ?>
                            </li>
                            <?php if ($this->Authority->isAuthority(true, 'Events', 'view')): ?>
                                <li class="b-name">

                                    <?= $this->Html->link($reservation->event->name,
                                        ['prefix' => 'User', 'controller' => 'Events', 'action' => 'view', 'id' => $reservation->event_id]
                                    ); ?>

                                </li>
                            <?php else : ?>
                                <li class="b-name for_txt">
                                    <?= $this->Text->truncate($reservation->event->name, 50, ['tooltip' => true, 'escape' => true]) ?>
                                </li>
                            <?php endif; ?>
                            <li class="b-dayTime">
                                <?php if (!empty($reservation->reservation_event_plans) && is_array($reservation->reservation_event_plans)) : ?>
                                    <?= h($this->Template->displayDayAndWeek($reservation->usage_timestamp_from)) ?>
                                    <?= h($reservation->usage_timestamp_from->format('H:i')) ?>
                                    <?= $this->Tr->h('common/fromToSeparate') ?>
                                    <p><?= $this->Text->truncate($this->Template->viewArrayToString(array_column($reservation->reservation_event_plans, 'event_plan'), 'name'), 18, ['tooltip' => true, 'escape' => true]) ?></p>
                                <?php else: ?>
                                    <?php if ($reservation->event->usage_time_notation === Event::USAGE_TIME_NOTATION_END_TIME) : ?>
                                        <?php if ($reservation->usage_timestamp_from->format('ymd') === $reservation->usage_timestamp_to->format('ymd')) : ?>
                                            <?= h($this->Template->displayDayAndWeek($reservation->usage_timestamp_from)) ?>
                                            <?= $this->Template->getFromToDisplay(
                                                h($reservation->usage_timestamp_from->format('H:i')),
                                                h($reservation->usage_timestamp_to->format('H:i')),
                                                ' ' . $this->Tr->h('common/fromToSeparate') . ' '
                                            ) ?>
                                        <?php else : ?>
                                            <?= $this->Template->getFromToDisplay(
                                                h($this->Template->displayDayAndWeek($reservation->usage_timestamp_from, 'H:i')),
                                                h($this->Template->displayDayAndWeek($reservation->usage_timestamp_to, 'H:i')),
                                                ' ' . $this->Tr->h('common/fromToSeparate') . ' ',
                                                true,
                                                false
                                            ) ?>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <?= h($this->Template->displayDayAndWeek($reservation->usage_timestamp_from, 'H:i')) ?>
                                        <?= $this->Tr->h('common/fromToSeparate') ?>
                                        <?php if ($reservation->event->type === Event::TYPE_TIME) : ?>
                                            <?= h($reservation->usage_time) ?> <?= $this->Tr->h('reservation/dateTimeMinute') ?>
                                        <?php else : ?>
                                            <?= h($reservation->usage_day) ?> <?= $this->Tr->h('reservation/dateTimeDay') ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </li>
                            <?php if ($this->Setting->getSystemSetting()->payment_use_flg === SystemSetting::PAYMENT_USE_FLG_ON): ?>
                                <li class="b-statusP <?= h($this->Configure->read('Master.payment.statusClass.' . $reservation->get('payment_status_id'))) ?>">
                                    <?php if (!is_null($reservation->payment_status_id)) : ?>
                                        <?= h($this->Master->getPaymentStatusName($reservation->payment_status_id)) ?>
                                    <?php else: ?>
                                        <span>&nbsp;</span>
                                    <?php endif; ?>
                                </li>
                            <?php endif; ?>
                            <li class="b-confirm">
                                <?php if ($this->Authority->isAuthority(true, 'Reservations', 'view')) : ?>
                                    <?= $this->Html->link($this->Tr->t('reservationsHistory/detailBtn'), [
                                        'prefix' => 'User',
                                        'controller' => 'Reservations',
                                        'action' => 'view',
                                        'id' => $reservation->id,
                                    ], [
                                        'escapeTitle' => false,
                                        'class' => ['cmn-btn', 'is-blue']
                                    ]) ?>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </li>
                </ul>
            <?php endforeach; ?>
        </div>
        <aside class="search-parts mgt-20">
            <?= $this->element('User/Common/search/paginator') ?>
        </aside><!-- .search-parts -->
    <?php else : ?>
        <p class="cmn-txt mgt-20"><?= $this->Tr->h('reservationsHistory/noList') ?></p>
    <?php endif; ?>

</section>
