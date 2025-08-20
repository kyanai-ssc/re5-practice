<?php $this->start('ajax_html'); ?>
    <div class="tooltip" title="選択した時間帯の詳細" data-search-data="<?= h(json_encode([
        'event_id' => $eventUnit->getEvent()->get('id'),
        'usage_timestamp_from' => $eventUnit->getDateTimeFrom()->format('Y/m/d H:i'),
        'usage_timestamp_to' => $eventUnit->getDateTimeTo()->format('Y/m/d H:i'),
        'display_item' => $calendarDetailForm->getCalendarDisplayFormItemId(),
    ])) ?>">
        <div class="popup-content">
            <dl class="popupDetail">
                <dd>
                    <div class="top-wrap d-flex">
                        <table class="info-date">
                            <tr>
                                <th>
                                    予約枠
                                </th>
                                <td>
                                    <?php if ($this->Authority->isAuthority(true, 'Events', 'edit')): ?>
                                        <?= $this->Html->link($eventUnit->getEvent()->get('name'), '#', [
                                            'class' => ['js_open_window'],
                                            'data-url' => $this->Url->build([
                                                'prefix' => 'Admin',
                                                'controller' => 'Events',
                                                'action' => 'edit',
                                                'id' => $eventUnit->getEvent()->get('id'),
                                            ], ['escape' => false]),
                                        ]) ?>
                                    <?php else: ?>
                                        <?= h($eventUnit->getEvent()->get('name')) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    日付
                                </th>
                                <td>
                                    <?= h($this->Template->displayDayAndWeek($eventUnit->getDateTimeFrom())) ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    時間
                                </th>
                                <td>
                                    <?= h($eventUnit->getDateTimeFrom()->format('H:i')) ?>
                                    ～
                                    <?= h($eventUnit->getDateTimeTo()->format('H:i')) ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    状況
                                </th>
                                <td>
                                    <?= $this->element('Admin/Reservations/calendar_unit_stock', [
                                        'eventUnit' => $eventUnit,
                                    ]) ?>
                                </td>
                            </tr>
                        </table>
                        <div class="info-btn">
                            <?php if ($eventUnit->canReserve()): ?>
                                <?= $this->Html->link('予約登録', '#', [
                                    'class' => ['cmn-btn', 'is-newCreate', 'js_open_window'],
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'Reservations',
                                        'action' => 'add',
                                        '?' => [
                                            'event_id' => $eventUnit->getEvent()->get('id'),
                                            'usage_timestamp_from' => $eventUnit->getDateTimeFrom()->format('Y/m/d H:i:s'),
                                            'user_id' => $calendarDetailForm->getData('user_id'),
                                        ]
                                    ], ['escape' => false]),
                                ]) ?>
                            <?php endif; ?>
                            <?php if (count($reservations) > 0): ?>
                                <?= $this->Html->link('予約一覧', '#', [
                                    'class' => ['cmn-btn', 'is-blue', 'js_open_window'],
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'Reservations',
                                        'action' => 'list',
                                        '?' => [
                                            'event_id' => $eventUnit->getEvent()->get('id'),
                                            'usage_timestamp' => [
                                                'from' => $eventUnit->getDateTimeFrom()->format('Y/m/d H:i:s'),
                                                'to' => $eventUnit->getDateTimeTo()->format('Y/m/d H:i:s'),
                                            ],
                                        ],
                                    ], ['escape' => false]),
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (count($reservations) > 0): ?>
                        <div class="bottom-wrap">
                            <aside class="clearfix mgt-20 mgb-20">
                                <?= $this->element('Admin/Common/search/page_counter') ?>
                            </aside>
                            <div class="mgb-20">
                                <?= $this->element('Admin/Common/search/paginator') ?>
                            </div>
                            <div class="status-cahge-wrap">
                                <table class="cmn-table result-table">
                                    <thead>
                                        <tr>
                                            <th class="w100">
                                                <span>予約詳細</span>
                                            </th>
                                            <th>
                                                <div class="sort_wrap">
                                                    <span>予約ID</span>
                                                    <?= $this->element('Admin/Common/search/sort', ['key' => 'id']) ?>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="sort_wrap">
                                                    <span>ステータス</span>
                                                    <?= $this->element('Admin/Common/search/sort', ['key' => 'reservation_status_id']) ?>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="sort_wrap">
                                                    <span>利用時間</span>
                                                    <?= $this->element('Admin/Common/search/sort', ['key' => 'usage_timestamp_from']) ?>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="sort_wrap">
                                                    <span>予約数</span>
                                                    <?= $this->element('Admin/Common/search/sort', ['key' => 'number']) ?>
                                                </div>
                                            </th>
                                            <?php if (!is_null($calendarDetailForm->getCalendarDisplayFormItem())): ?>
                                                <th>
                                                    <span><?= h($calendarDetailForm->getCalendarDisplayFormItem()->get('name')) ?></span>
                                                </th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reservations as $reservation): ?>
                                            <tr class="parent">
                                                <td class="tool tac">
                                                    <ul>
                                                        <?php if ($reservation->canView()): ?>
                                                            <li>
                                                                <?php $detailBtn = $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_info"></use></svg>', [
                                                                    'type' => 'button',
                                                                    'class' => ['btn-tool', 'is-info', 'tooltip', 'js_open_window'],
                                                                    'title' => '詳細',
                                                                    'data-url' => $this->Url->build([
                                                                        'prefix' => 'Admin',
                                                                        'controller' => 'Reservations',
                                                                        'action' => 'view',
                                                                        'id' => $reservation->get('id'),
                                                                    ], ['escape' => false]),
                                                                    'escapeTitle' => false,
                                                                ]) ?>
                                                                <?= $this->Authority->isAuthority($detailBtn, 'Reservations', 'view'); ?>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                    <?php if (!$reservation->canView()): ?>
                                                        <span class="warning">会員削除済み</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= h($reservation->get('id')) ?>
                                                </td>
                                                <td class="status">
                                                    <?= $this->element('Admin/Reservations/status', [
                                                        'reservationId' => $reservation->get('id'),
                                                        'reservationStatusId' => $reservation->get('reservation_status_id'),
                                                        'canChangeStatus' => $reservation->canEdit(),
                                                    ]) ?>
                                                </td>
                                                <td>
                                                    <?= h($this->Template->displayDayAndWeek($reservation->get('usage_timestamp_from'), 'H:i')) ?>～<br/>
                                                    <?= h($this->Template->displayDayAndWeek($reservation->get('usage_timestamp_to'), 'H:i')) ?>
                                                </td>
                                                <td>
                                                    <?= h($reservation->get('number')) ?>
                                                </td>
                                                <?php if (!is_null($calendarDetailForm->getCalendarDisplayFormItem())): ?>
                                                    <td>
                                                        <?= h($calendarDetailForm->getCalendarDisplayFormItem()->getInputTypeItem()->getCalendarOutputValue($reservation)) ?>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="mgt-20">
                                    <?= $this->element('Admin/Common/search/paginator') ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>

                    <?php endif; ?>
                </dd>
            </dl>
        </div>
    </div>
<?php $this->end('ajax_html'); ?>
<?= $this->Ajax->json([
    'html' => $this->fetch('ajax_html'),
]) ?>
