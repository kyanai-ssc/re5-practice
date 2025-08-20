<?php

use App\Model\Entity\Event;
use App\Model\Entity\FormGroup;

$this->assign('title', '予約 登録内容確認');
$this->Html->script('admin/reservations/confirm', [
    'block' => true,
]);
$this->assign('headerType', 'data');
$this->Breadcrumbs->add(
    '予約台帳',
    ['prefix' => 'Admin', 'controller' => 'Reservations', 'action' => 'calendar']
);
$this->Breadcrumbs->add(
    '登録内容確認'
);

?>
<section class="form-input">
    <?= $this->Flash->render('reservationsError') ?>
    <?= $this->Form->create($continuousForm, [
        'type' => 'post',
        'url' => [
            'controller' => 'Reservations',
            'action' => 'add-conf',
            '?' => [
                'key' => $continuousForm->getContinuousParameter('key'),
            ],
        ],
        'idPrefix' => 'reservations-add-conf',
        'novalidate' => true,
        'class' => ['js_submit_once'],
    ]) ?>
    <?= $this->Token->getTokenTag() ?>
    <?= $this->Token->getTokenError() ?>
    <?php if (((string)$continuousForm->getContinuousParameter('key')) !== ''): ?>
        <?= $this->element('Admin/Reservations/detail', [
            'reservationForm' => $continuousForm->getReservationForm($continuousForm->getContinuousParameter('key')),
            'valueOptions' => $continuousForm->getReservationForm($continuousForm->getContinuousParameter('key'))->getFieldValueOptions(),
            'mode' => 'addConf',
        ]) ?>
    <?php else: ?>
        <fieldset class="mgt-10">
            <div class="ttl-panel-show">
                    <span class="ttl-s">
                        <?= h($this->Configure->read('Master.form.formType.' . FormGroup::FORM_TYPE_USER)) ?>
                    </span>
                <button type="button" class="showBtn"></button>
            </div>
            <div class="showWrap">
                <?= $this->element('Admin/Common/fieldset/input_items_detail', [
                    'formGroups' => $continuousForm->getUserFormGroups(),
                    'options' => [
                        'user' => $continuousForm->getUserEntity(),
                        'adminFlg' => $continuousForm->isAdmin(),
                        'mode' => 'addConf',
                    ],
                ]) ?>
            </div>
        </fieldset>
    <?php endif; ?>
    <?php if ($continuousForm->isContinuous()): ?>
        <div class="mgt-20">
            <div>
                <?php foreach ($continuousForm->getErrors() as $name => $errors): ?>
                    <?php if ($name === 'reservations'): ?>
                        <?php foreach (array_keys($errors) as $index => $key): ?>
                            [<?= h($index + 1) ?>行目]
                            <?= $this->Form->error('reservations.' . $key) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <table class="cmn-table input-table">
                <thead>
                <tr>
                    <th class="w-100">編集</th>
                    <th>カテゴリー</th>
                    <th>予約枠名</th>
                    <th>利用日時</th>
                    <th>予約数</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($continuousForm->getReservationForm() as $key => $reservationForm): ?>
                    <tr>
                        <td class="tool">
                            <ul>
                                <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_info"></use></svg>', [
                                        'type' => 'button',
                                        'title' => '詳細',
                                        'class' => ['btn-tool', 'is-info', 'js_reservation_view_continuous'],
                                        'data-continuous-key' => $key,
                                        'data-title' => '未確定の予約情報',
                                        'escapeTitle' => false,
                                    ]) ?>
                                </li>
                                <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', [
                                        'type' => 'button',
                                        'title' => '編集',
                                        'class' => ['btn-tool', 'is-editor', 'js_change_url'],
                                        'data-url' => $this->Url->build([
                                            'prefix' => 'Admin',
                                            'controller' => 'Reservations',
                                            'action' => 'add',
                                            '?' => [
                                                    'key' => $key,
                                                ] + $this->Configure->read('Setting.formInput.backQuery'),
                                        ], ['escape' => false]),
                                        'escapeTitle' => false,
                                    ]) ?>
                                </li>
                                <li>
                                    <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                        'type' => 'button',
                                        'title' => '削除',
                                        'class' => ['btn-tool', 'is-delete', 'js_reservation_remove_continuous'],
                                        'data-continuous-key' => $key,
                                        'data-title' => '未確定の予約情報の削除',
                                        'escapeTitle' => false,
                                    ]) ?>
                                </li>
                            </ul>
                        </td>
                        <td>
                            <?php if ($reservationForm->getReservationEntity()->getEventEntity()->has('label')): ?>
                                <?= h($reservationForm->getReservationEntity()->getEventEntity()->get('label')->get('name')) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= h($reservationForm->getReservationEntity()->getEventEntity()->get('name')) ?>
                        </td>
                        <td>
                            <?= h($this->Template->displayDayAndWeek($reservationForm->getReservationEntity()->get('usage_timestamp_from'), 'H:i')) ?>～<br/>
                            <?= h($this->Template->displayDayAndWeek($reservationForm->getReservationEntity()->get('usage_timestamp_to'), 'H:i')) ?>
                            <?php if (((string)$reservationForm->getReservationEntity()->getEventEntity()->get('type')) === ((string)Event::TYPE_TIME)): ?>
                                <?= h($reservationForm->getReservationEntity()->get('usage_time')) ?>分
                            <?php endif; ?>
                            <?php if (((string)$reservationForm->getReservationEntity()->getEventEntity()->get('type')) === ((string)Event::TYPE_DAY)): ?>
                                <?= h($reservationForm->getReservationEntity()->get('usage_day')) ?>日
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= h($reservationForm->getReservationEntity()->get('number')) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?= $this->element('Admin/Common/fieldset/mail_check') ?>
    <div class="btn-box mgt-20">
        <?php if (!$continuousForm->isContinuous()): ?>
            <?= $this->Html->link(
                '戻る',
                [
                    'prefix' => 'Admin',
                    'controller' => 'Reservations',
                    'action' => 'add',
                    '?' => [
                            'key' => $continuousForm->getContinuousParameter('key'),
                        ] + $this->Configure->read('Setting.formInput.backQuery'),
                ],
                [
                    'class' => ['cmn-btn', 'is-reset', 'is-gray'],
                ]
            ) ?>
            <?= $this->Form->button('登録', [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue'],
            ]) ?>
            <?php if ($continuousForm->canContinuous()): ?>
                <?= $this->Form->button('続けて予約', [
                    'type' => 'button',
                    'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_reservation_set_continuous'],
                ]) ?>
            <?php endif; ?>
        <?php else: ?>
            <?= $this->Form->button('上記の予約を取消', [
                'type' => 'button',
                'class' => ['cmn-btn', 'is-gray', 'js_reservation_remove_all_continuous'],
            ]) ?>
            <?= $this->Form->button('登録', [
                'type' => 'submit',
                'class' => ['cmn-btn', 'is-blue'],
            ]) ?>
            <?php if ($continuousForm->canContinuous()): ?>
                <?= $this->Form->button('続けて予約', [
                    'type' => 'button',
                    'class' => ['cmn-btn', 'is-blue', 'is-circle', 'js_change_url'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'Reservations',
                        'action' => 'calendar',
                        '?' => $this->Configure->read('Setting.searchInput.searchQuery') + [
                            'user_id' => $continuousForm->getUserEntity()->get('id'),
                        ],
                    ], ['escape' => false]),
                ]) ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="hidden">
        <input type="hidden" class="js_reservation_current_continuous_key" value="<?= h($continuousForm->getContinuousParameter('key')) ?>"/>
    </div>
    <?= $this->Form->end() ?>
</section>
