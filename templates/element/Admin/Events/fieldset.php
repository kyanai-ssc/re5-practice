<?php

use \App\Model\Entity\Event;
?>

<?= $this->Html->script('admin/events/fieldset'); ?>
<?php $this->Form->unlockField('event_remarks'); ?>
<?php $this->Form->unlockField('event_plans'); ?>
<?php $this->Form->unlockField('event_stock_marks'); ?>
<?php $this->Form->unlockField('js_type_once'); ?>
<?php $this->Form->unlockField('js_type_time'); ?>
<?php $this->Form->unlockField('js_type_day'); ?>
<?php $this->Form->unlockField('js_plan_type_multi'); ?>
<?php $this->Form->unlockField('js_plan_type_single'); ?>

<input type="hidden" name="js_type_time" value="<?= h(Event::TYPE_TIME) ?>">
<input type="hidden" name="js_type_day" value="<?= h(Event::TYPE_DAY) ?>">
<input type="hidden" name="js_plan_type_multi" class="js_plan_type_multi" value="<?= h(Event::PLAN_MULTIPLE) ?>">
<input type="hidden" name="js_plan_type_single" class="js_plan_type_single" value="<?= h(Event::PLAN_SINGLE) ?>">

<?= $this->FormError->errorWithoutNested('event_stock_settings'); ?>

<div class="form-input-set">
    <fieldset>
        <div class="cmn-tab js-tab">
            <ul class="tab-li">
                <li class="js_tab_event"><a href="#event_tab">予約枠設定</a></li>
                <li class="js_tab_event_holiday"><a href="#event_holidays_tab">個別休業設定</a></li>
                <li class="js_tab_event_stock_settings"><a href="#event_stock_settings_tab">例外日設定</a></li>
            </ul>
            <div id="event_tab" class="tab-content">
                <h3 class="ttl-s mgt-20 mgb-20">基本設定</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">予約枠名<?= $this->Template->isRequire('name') ?></div>
                        </th>
                        <td>
                            <?= $this->Form->control('name', [
                                'type' => 'text',
                                'label' => false,
                            ]) ?>
                            <?php if ($this->Setting->getSystemSetting()->usePayment()): ?>
                                <?php if ($this->Setting->hasPaymentSetting() && $this->Setting->getPaymentSetting()->isPaymentServiceSb()) : ?>
                                    <div class="desc-wrap">
                                        <p>決済方法によって一部文字列がある場合に決済エラーとなってしまいます。詳細は<a href="<?= $this->Setting->getManualLink() . '/#smoothplay4' ?>" target="_blank">こちら</a>をご確認ください</p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">予約枠タイプ<?= $this->Template->isRequire('type') ?></div>
                        </th>
                        <td>
                            <?php if ($mode === 'edit') : ?>
                                <div class="cmn-txt">
                                    <?= h($valueOptions['type'][$event->type]) ?>
                                    <?= $this->Form->control('type', [
                                        'type' => 'hidden',
                                        'class' => 'js_change_format_type',
                                    ]) ?>
                                </div>
                            <?php else : ?>

                                <?= $this->Form->control('type', [
                                    'type' => 'select',
                                    'options' => $valueOptions['type'],
                                    'class' => ['js_change_format_type', 'select'],
                                ]) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">カテゴリー<?= $this->Template->isRequire('label_id') ?></div>
                        </th>
                        <td>
                            <?= $this->Label->renderSelect([
                                'type' => $this->Configure->read('Master.label.type.other'),
                                'labelId' => $event->label_id,
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">絞り込みキーワード<?= $this->Template->isRequire('event_tags') ?></div>
                        </th>
                        <td>
                            <?= $this->element('Admin/Events/fieldset_tags', [
                                'tagLists' => $tagLists,
                            ]) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <?php if ($this->SmartLock->useRemoteLock()): ?>
                    <h3 class="ttl-s mgt-20 mgb-20">RemoteLOCK設定</h3>
                    <table class="input-box">
                        <tbody>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">デバイスキー</div>
                            </th>
                            <td>
                                <?= $this->Form->control('event_smart_lock.smart_lock_device_key', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                                <div class="desc-wrap">
                                    <p>
                                        デバイスキーの取得の仕方については<a href="<?= h($this->Configure->read('Env.manual.domain')) ?>/<?= h($this->Configure->read('Setting.smartLock.manualUrl.remoteLock')) ?>" target="_blank">マニュアル</a>をご確認ください。
                                    </p>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
                <?php if ($this->SmartLock->useAkerun()): ?>
                    <h3 class="ttl-s mgt-20 mgb-20">Akerun設定</h3>
                    <table class="input-box">
                        <tbody>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">Akerun ID</div>
                            </th>
                            <td>
                                <?= $this->Form->control('event_smart_lock.smart_lock_device_key', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                                <div class="desc-wrap">
                                    <p>
                                        Akerun IDの取得の仕方については<a href="<?= h($this->Configure->read('Env.manual.domain')) ?>/<?= h($this->Configure->read('Setting.smartLock.manualUrl.akerun')) ?>" target="_blank">マニュアル</a>をご確認ください。
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">URL合鍵<?= $this->Template->isRequire('event_smart_lock.smart_lock_key_url_flg') ?></div>
                            </th>
                            <td>
                                <?= $this->Form->control('event_smart_lock.smart_lock_key_url_flg', [
                                    'type' => 'radio',
                                    'options' => $valueOptions['common'],
                                    'default' => Event::COMMON_FLG_ON,
                                ]) ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
                <?php if ($this->Setting->getSystemSetting()->canCoordinateVideoMeeting()): ?>
                    <h3 class="ttl-s mgt-20 mgb-20">ビデオ会議設定</h3>
                    <table class="input-box">
                        <tbody>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">ビデオ会議主催者</div>
                            </th>
                            <td>
                                <?= $this->Form->control('organizer_id', [
                                    'type' => 'select',
                                    'options' => $valueOptions['organizers'],
                                    'class' => ['select'],
                                    'empty' => '連携しない'
                                ]) ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
                <h3 class="ttl-s mgt-20 mgb-20">利用期間設定</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">利用期間<?= $this->Template->isRequire('date_from') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('date_from', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['js-datepicker-type-range-start',]
                                ]) ?>
                                <span class="txt mgl-10 mgr-10">から</span>
                                <?= $this->Form->control('date_to', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['js-datepicker-type-range-end',]
                                ]) ?>
                                <span class="txt mgl-10">の期間</span>
                            </div>
                            <div class="desc-wrap">
                                <p>開始日のみ必須です</p>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">利用曜日<?= $this->Template->isRequire('event_weeks') ?></div>
                        </th>
                        <td class="btn-inline">
                            <?= $this->FormError->errorWithoutNested('event_weeks') ?>
                            <?php $weekIndex = 0; ?>
                            <?php foreach ($valueOptions['week'] as $key => $week) : ?>
                                <?= $this->Template->checkbox('event_weeks.' . $weekIndex++ . '.week', [
                                    'type' => 'checkbox',
                                    'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => $week],
                                    'value' => $key,
                                    'id' => 'weeks-' . $key,
                                ]) ?>
                            <?php endforeach; ?>
                            <div class="desc-wrap">
                                <p>チェックがない場合、全曜日に表示されます。</p>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">実施時間<?= $this->Template->isRequire('time_to') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?php if ($mode === 'edit' && $reserve['future'] && $event->type === Event::TYPE_DAY) : ?>
                                    <p class="cmn-txt">
                                        <?= h($event->time_from) ?>
                                        <span class="txt mgl-10 mgr-10">から</span>
                                        <?= h($event->time_to) ?>
                                        <?= $this->Form->hidden('time_to') ?>
                                        <?= $this->Form->hidden('time_from') ?>
                                    </p>
                                <?php else : ?>
                                    <?= $this->Form->control('time_from', [
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => ['js-timepicker'],
                                    ]) ?>
                                    <span class="txt mgl-10 mgr-10">から</span>
                                    <?= $this->Form->control('time_to', [
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => ['js-timepicker'],
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                            <div class="desc-wrap">
                                <p>基本設定の「予約状況表 表示時間」外の実施時間は、予約サイトで非表示となりますが</p>
                                <p>直接URLを指定すれば予約できます。詳しくはマニュアルで確認ください。</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <h3 class="ttl-s mgt-20 mgb-20">在庫数設定</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">在庫数<?= $this->Template->isRequire('stock') ?></div>
                        </th>
                        <td>
                            <?= $this->Form->control('stock', [
                                'type' => 'text',
                                'label' => false,
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">在庫の単位<?= $this->Template->isRequire('stock_unit') ?></div>
                        </th>
                        <td>
                            <?= $this->Form->control('stock_unit', [
                                'type' => 'text',
                                'label' => false,
                                'class' => ['textbox_w50']
                            ]) ?>
                            <div class="desc-wrap">
                                <p>予約枠の数字の後に付く単位となります。（例）「個」・「部屋」・「席」など</p>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                在庫数表示設定<?= $this->Template->isRequire('stock_display_type') ?></div>
                        </th>
                        <td>
                            <?= $this->Template->radio('stock_display_type', [
                                'type' => 'radio',
                                'options' => $valueOptions['stockDisplayType'],
                                'class' => ['js_change_symbolic_flg'],
                            ]) ?>

                            <?php
                            $eventStockMarksClass = 'hidden';
                            if ($event->stock_display_type === Event::STOCK_DISPLAY_TYPE_ICON) {
                                $eventStockMarksClass = '';
                            }
                            ?>
                            <div
                                class="js_toggle_vent_stock_marks <?= h($eventStockMarksClass) ?> js_toggle_vent_stock_marks_<?= h(Event::STOCK_DISPLAY_TYPE_ICON) ?>">
                                <div>
                                    <?= $this->FormError->errorWithoutNested('event_stock_marks') ?>
                                    <table class="pliceInput-detail">
                                        <thead>
                                        <tr>
                                            <th>記号</th>
                                            <th>切り替えタイミング</th>
                                            <th>削除</th>
                                        </tr>
                                        </thead>
                                        <tbody class="js_event_stock_marks_container">
                                        <?php if (isset($event->event_stock_marks)): ?>
                                            <?php foreach ($event->event_stock_marks as $eventStockMarkIndex => $eventStockMarkData): ?>
                                                <?= $this->element('Admin/Events/fieldset_event_stock_marks', [
                                                    'event' => $event,
                                                    'eventStockMarkIndex' => $eventStockMarkIndex,
                                                    'eventStockMarkData' => $eventStockMarkData,
                                                ]) ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <?= $this->Form->button('記号追加', [
                                        'type' => 'button',
                                        'class' => ['js_add_input', 'cmn-btn', 'is-addCell', 'mgt-20'],
                                        'data-container' => '.js_event_stock_marks_container',
                                        'data-html' => $this->element('Admin/Events/fieldset_event_stock_marks', [
                                            'event' => $event,
                                            'eventStockMarkIndex' => '%INDEX%',
                                            'event' => null,
                                        ]),
                                        'data-index-element' => '.js_event_stock_marks_index',
                                        'data-index-replace' => '%INDEX%',
                                    ]) ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <h3 class="ttl-s mgt-20 mgb-20">料金設定</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">料金設定<?= $this->Template->isRequire('time_plan') ?></div>
                        </th>
                        <td>
                            <?php if ($mode === 'edit' && $reserve['all']): ?>
                                <p class="cmn-txt">
                                    <?= h($this->Configure->read('Master.event.plan.' . $event->time_plan)) ?>
                                    <?= $this->Form->hidden('time_plan', ['class' => ['js_plan_type']]) ?>
                                </p>
                            <?php else : ?>
                                <?= $this->Template->radio('time_plan', [
                                    'type' => 'radio',
                                    'label' => false,
                                    'options' => $valueOptions['plan'],
                                    'class' => ['js_plan_type'],
                                ]) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="field-input js_plan_type_not_multi">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                料金<?= $this->Template->isRequire('charge', ['always' => true]) ?></div>
                        </th>
                        <td>
                            <?= $this->Form->control('charge', [
                                'type' => 'text',
                                'label' => false,
                                'class' => ['textbox_w150']
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input js_plan_type_multi">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">プラン設定<?= $this->Template->isRequire('event_plans') ?></div>
                        </th>
                        <td>
                            <?php if ($reserve['all'] && isset($timePlan) && $timePlan === Event::PLAN_MULTIPLE) : ?>
                                <div class="pliceInput-box">
                                    <?= h($this->Configure->read('Master.event.multipleTimePlanType.' . $event->multiple_time_plan_type)) ?>
                                    <?= $this->Form->hidden('multiple_time_plan_type') ?>
                                    <table class="pliceInput-detail">
                                        <thead>
                                        <tr>
                                            <th>公開設定</th>
                                            <th>プラン名</th>
                                            <th>料金</th>
                                            <th>利用<span class="js_disp_time">時間</span><span class="js_disp_day">日</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody class="js_event_plans_container">
                                        <?php if (isset($event->event_plans)): ?>
                                            <?php foreach ($event->event_plans as $eventPlanIndex => $eventPlanData): ?>
                                                <?= $this->element('Admin/Events/fieldset_event_plans', [
                                                    'event' => $event,
                                                    'eventPlanIndex' => $eventPlanIndex,
                                                    'eventPlanData' => $eventPlanData,
                                                    'reserve' => $reserve,
                                                    'type' => $event->get('type'),
                                                ]) ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <div class="js_disp_time">
                                        <?= $this->Form->button('プラン追加', [
                                            'type' => 'button',
                                            'class' => ['js_add_input', 'cmn-btn', 'is-addCell', ' mgt-20'],
                                            'data-container' => '.js_event_plans_container',
                                            'data-html' => $this->element('Admin/Events/fieldset_event_plans', [
                                                'eventPlanIndex' => '%INDEX%',
                                                'eventPlanData' => null,
                                                'event' => $event,
                                                'type' => Event::TYPE_TIME,
                                                'reserve' => null,
                                            ]),
                                            'data-index-element' => '.js_event_plans_index',
                                            'data-index-replace' => '%INDEX%',
                                        ]) ?>
                                    </div>
                                    <div class="js_disp_day">
                                        <?= $this->Form->button('追加', [
                                            'type' => 'button',
                                            'class' => ['js_add_input', 'cmn-btn', 'is-addCell', ' mgt-20'],
                                            'data-container' => '.js_event_plans_container',
                                            'data-html' => $this->element('Admin/Events/fieldset_event_plans', [
                                                'eventPlanIndex' => '%INDEX%',
                                                'eventPlanData' => null,
                                                'event' => $event,
                                                'type' => Event::TYPE_DAY,
                                                'reserve' => null,
                                            ]),
                                            'data-index-element' => '.js_event_plans_index',
                                            'data-index-replace' => '%INDEX%',
                                        ]) ?>
                                    </div>
                                </div>
                            <?php elseif (($mode === 'edit' && !$reserve['all']) || $mode === 'add') : ?>
                                <div id="multiple_time_plan_type" class="pliceInput-box btn-inline">
                                    <?= $this->Template->radio('multiple_time_plan_type', [
                                        'type' => 'radio',
                                        'label' => false,
                                        'options' => $valueOptions['multipleTimePlanType'],
                                    ]) ?>
                                    <table class="pliceInput-detail">
                                        <tr>
                                            <th>公開設定</th>
                                            <th>プラン名</th>
                                            <th>料金</th>
                                            <th>利用<span class="js_disp_time">時間</span><span class="js_disp_day">日</span>
                                            </th>
                                            <th></th>
                                        </tr>
                                        <tbody class="js_event_plans_container">
                                        <?php if (isset($event->event_plans)): ?>
                                            <?php foreach ($event->event_plans as $eventPlanIndex => $eventPlanData): ?>
                                                <?= $this->element('Admin/Events/fieldset_event_plans', [
                                                    'event' => $event,
                                                    'eventPlanIndex' => $eventPlanIndex,
                                                    'eventPlanData' => $eventPlanData,
                                                    'reserve' => $reserve,
                                                    'type' => $event->get('type'),
                                                ]) ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <div class="js_disp_time">
                                        <?= $this->Form->button('プラン追加', [
                                            'type' => 'button',
                                            'class' => ['js_add_input', 'cmn-btn', 'is-addCell', 'mgt-20'],
                                            'data-container' => '.js_event_plans_container',
                                            'data-html' => $this->element('Admin/Events/fieldset_event_plans', [
                                                'eventPlanIndex' => '%INDEX%',
                                                'eventPlanData' => null,
                                                'event' => null,
                                                'type' => Event::TYPE_TIME,
                                                'reserve' => null,
                                            ]),
                                            'data-index-element' => '.js_event_plans_index',
                                            'data-index-replace' => '%INDEX%',
                                        ]) ?>
                                    </div>
                                    <div class="js_disp_day">
                                        <?= $this->Form->button('プラン追加', [
                                            'type' => 'button',
                                            'class' => ['js_add_input', 'cmn-btn', 'is-addCell', ' mgt-20'],
                                            'data-container' => '.js_event_plans_container',
                                            'data-html' => $this->element('Admin/Events/fieldset_event_plans', [
                                                'eventPlanIndex' => '%INDEX%',
                                                'eventPlanData' => null,
                                                'event' => null,
                                                'type' => Event::TYPE_DAY,
                                                'reserve' => null,
                                            ]),
                                            'data-index-element' => '.js_event_plans_index',
                                            'data-index-replace' => '%INDEX%',
                                        ]) ?>
                                    </div>
                                </div>
                            <?php else : ?>
                                <?= $this->Form->hidden('multiple_time_plan_type') ?>
                            <?php endif; ?>
                            <?= $this->FormError->errorWithoutNested('event_plans'); ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <h3 class="ttl-s mgt-20 mgb-20">予約受付設定</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input js_disp_time">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約時間設定<?= $this->Template->isRequire('event_unit_time', ['always' => true]) ?></div>
                        </th>
                        <td>
                            <div class="d-flex mgb-10">
                                <?php if ($mode === 'edit' && $reserve['future']) : ?>
                                    <p class="cmn-txt">
                                        <?= h($event->event_unit_time) ?>
                                        <?= $this->Form->hidden('event_unit_time') ?>
                                    </p>
                                <?php else: ?>
                                    <?= $this->Form->control('event_unit_time', [
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => ['textbox_w150'],
                                    ]) ?>
                                <?php endif; ?>
                                <span class="txt mgl-10 mgr-10">分単位のコマ割り（5の倍数で指定）</span>
                            </div>
                            <div class="js_plan_type_not_multi">
                                <div class="d-flex mgb-10">
                                    <?php if ($mode === 'edit' && $reserve['future']) : ?>
                                        <p class="cmn-txt">
                                            <?= h($event->usage_unit_time) ?>
                                            <?= $this->Form->hidden('usage_unit_time') ?>
                                        </p>
                                    <?php else: ?>
                                        <?= $this->Form->control('usage_unit_time', [
                                            'type' => 'text',
                                            'label' => false,
                                            'class' => ['textbox_w150'],
                                        ]) ?>
                                    <?php endif; ?>
                                    <span class="txt mgl-10 mgr-10">分単位の予約（コマ割りの倍数で指定）</span>
                                </div>
                                <div class="d-flex mgb-10">
                                    <?= $this->Form->control('usage_time_from', [
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => ['textbox_w150'],
                                    ]) ?>
                                    <span class="txt mgl-10 mgr-10">分～</span>
                                    <?= $this->Form->control('usage_time_to', [
                                        'type' => 'text',
                                        'label' => false,
                                        'class' => ['textbox_w150'],
                                    ]) ?>
                                    <span class="txt mgl-10">分が選択可能</span>
                                </div>
                            </div>

                            <?= $this->Form->button('設定内容を確認する', [
                                'type' => 'button',
                                'class' => ['js_usage_time_preview', 'cmn-btn', 'is-blue', 'is-circle'],
                            ]) ?>
                            <div class="hide">
                                <div id="js_frameWindowForPreview"></div>
                            </div>

                            <div class="desc-wrap">
                                <p>※管理画面からの予約はコマ割り分数ごとの予約ができます。</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                    <tbody class="js_disp_day">
                    <tr class="field-input js_plan_type_not_multi">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約時間設定<?= $this->Template->isRequire('usage_unit_day', ['always' => true]) ?>
                            </div>
                        </th>
                        <td>
                            <div class="mgb-10">
                                <?= $this->Form->control('usage_unit_day', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w150'],
                                ]) ?>
                                <span class="txt mgl-10">日単位の予約</span>
                            </div>
                            <div class="d-flex">
                                <?= $this->Form->control('usage_day_from', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w150'],
                                ]) ?>
                                <span class="txt mgl-10 mgr-10">日～</span>
                                <?= $this->Form->control('usage_day_to', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w150'],
                                ]) ?>
                                <span class="txt mgl-10">日が選択可能</span>
                            </div>
                            <div class="desc-wrap">
                                <p>※管理画面からの予約は1日単位で予約ができます。</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                    <tbody>
                    <tr class="field-input js_disp_time">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">インターバル<?= $this->Template->isRequire('interval_time') ?></div>
                        </th>
                        <td>
                            <?= $this->Form->control('interval_time', [
                                'type' => 'text',
                                'label' => false,
                                'class' => ['textbox_w150']
                            ]) ?>
                            <span class="txt mgl-10">分（コマ割りの倍数で指定）</span>
                        </td>
                    </tr>
                    <tr class="field-input js_disp_day">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">インターバル<?= $this->Template->isRequire('interval_day') ?></div>
                        </th>
                        <td>
                            <?= $this->Form->control('interval_day', [
                                'type' => 'text',
                                'label' => false,
                                'class' => ['textbox_w150']
                            ]) ?>
                            <span class="txt mgl-10">日（1日単位で指定）</span>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                キャンセル待ち通知<?= $this->Template->isRequire('waiting_cancellation_flg') ?></div>
                        </th>
                        <td>
                            <?= $this->Template->radio('waiting_cancellation_flg', [
                                'type' => 'radio',
                                'options' => $valueOptions['common'],
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                受付可能な予約数<?= $this->Template->isRequire('stock_range_from') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('stock_range_from', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w50']
                                ]) ?>
                                <span class="txt mgl-10 mgr-10">から</span>
                                <?= $this->Form->control('stock_range_to', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w50']
                                ]) ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">予約登録時のステータス<?= $this->Template->isRequire('public_flg') ?></div>
                        </th>
                        <td>
                            <?= $this->Template->radio('reservation_status_id', [
                                'type' => 'radio',
                                'options' => $valueOptions['reservationStatusId'],
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約内容の表示パターン<?= $this->Template->isRequire('form_pattern_id') ?>
                            </div>
                        </th>
                        <td>
                            <?= $this->Form->control('form_pattern_id', [
                                'type' => 'select',
                                'label' => false,
                                'class' => ['select'],
                                'options' => $valueOptions['reservationFormPatternId'],
                            ]) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <h3 class="ttl-s mgt-20 mgb-20">予約期限設定</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約受付開始タイミング<?= $this->Template->isRequire('reception_period_number') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('reception_period_number', [
                                    'type' => 'text',
                                    'class' => ['textbox_w150']
                                ]) ?>
                                <span class="txt mgl-10 mgr-10">日前の</span>
                                <?= $this->Form->control('reception_period_time', [
                                    'type' => 'select',
                                    'options' => $valueOptions['receptionPeriodTime'],
                                    'class' => ['select'],
                                    'empty' => '----'
                                ]) ?>
                                <span class="txt mgl-10">から予約可能</span>
                            </div>
                            <?php if ($this->Setting->getSystemSetting()->usePayment()): ?>
                                <?php if ($this->Setting->hasPaymentSetting() && $this->Setting->getPaymentSetting()->isPaymentServiceSb()) : ?>
                                    <div class="desc-wrap">
                                        <p>※決済方法ごとの返金可能期間は以下の通りとなります。</p>
                                        <p>クレジットカード：決済日を含めて6ヶ月後まで<br>
                                        PayPay：決済日翌日を1日目として365日<br>
                                        ApplePay：決済日を含めて90日後まで<br>
                                        auPAY：決済日を含む翌々月末まで</p>
                                    </div>
                                <?php elseif ($this->Setting->hasPaymentSetting() && $this->Setting->getPaymentSetting()->isPaymentServiceGmo()) : ?>
                                    <div class="desc-wrap">
                                        <p>※クレジット決済の返金可能期間は決済日から180日以内となります。</p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約受付締切タイミング<?= $this->Template->isRequire('registration_deadline_number') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('registration_deadline_criterion', [
                                    'type' => 'radio',
                                    'options' => $valueOptions['deadlineCriterion'],
                                    'default' => Event::CRITERION_FROM,
                                ]) ?>
                                <span class="txt mgl-10 mgr-10">の</span>
                                <?php
                                $registrationDeadlineClass = 'hidden';
                                if ($event->registration_deadline_type === Event::DEADLINE_TYPE_DAY) {
                                    $registrationDeadlineClass = '';
                                }
                                ?>
                                <?= $this->Form->control('registration_deadline_number', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w150']
                                ]) ?>
                                <div class="mgl-10">
                                    <?= $this->Form->control('registration_deadline_type', [
                                        'type' => 'select',
                                        'options' => $valueOptions['deadlineType'],
                                        'class' => ['js_registration_deadline_type', 'select'],
                                        'data-time' => Event::DEADLINE_TYPE_TIME,
                                        'data-day' => Event::DEADLINE_TYPE_DAY,
                                    ]) ?>
                                </div>
                                <div
                                    class="mgl-10 js_toggle_registration_deadline <?= h($registrationDeadlineClass) ?> js_toggle_registration_deadline_type_<?= h(Event::DEADLINE_TYPE_DAY) ?>">
                                    <?= $this->Form->control('registration_deadline_time', [
                                        'type' => 'select',
                                        'options' => $valueOptions['deadlineTime'],
                                        'class' => ['js_registration_deadline_time', 'select'],
                                    ]) ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約変更締切タイミング<?= $this->Template->isRequire('editing_deadline_number') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('editing_deadline_criterion', [
                                    'type' => 'radio',
                                    'options' => $valueOptions['deadlineCriterion'],
                                    'default' => Event::CRITERION_FROM,
                                ]) ?>
                                <span class="txt mgl-10 mgr-10">の</span>
                                <?php
                                $editingDeadlineClass = 'hidden';
                                if ($event->editing_deadline_type === Event::DEADLINE_TYPE_DAY) {
                                    $editingDeadlineClass = '';
                                }
                                ?>
                                <?= $this->Form->control('editing_deadline_number', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w150']
                                ]) ?>
                                <div class="mgl-10">
                                    <?= $this->Form->control('editing_deadline_type', [
                                        'type' => 'select',
                                        'options' => $valueOptions['deadlineType'],
                                        'class' => ['js_editing_deadline_type', 'select'],
                                        'data-time' => Event::DEADLINE_TYPE_TIME,
                                        'data-day' => Event::DEADLINE_TYPE_DAY,
                                    ]) ?>
                                </div>
                                <div
                                    class="mgl-10 js_toggle_editing_deadline <?= h($editingDeadlineClass) ?> js_toggle_editing_deadline_type_<?= h(Event::DEADLINE_TYPE_DAY) ?>">
                                    <?= $this->Form->control('editing_deadline_time', [
                                        'type' => 'select',
                                        'options' => $valueOptions['deadlineTime'],
                                        'class' => ['js_editing_deadline_time', 'select'],
                                    ]) ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約キャンセル締切タイミング<?= $this->Template->isRequire('cancellation_deadline_number') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('cancellation_deadline_criterion', [
                                    'type' => 'radio',
                                    'options' => $valueOptions['deadlineCriterion'],
                                    'default' => Event::CRITERION_FROM,
                                ]) ?>
                                <span class="txt mgl-10 mgr-10">の</span>
                                <?php
                                $cancellationDeadlineClass = 'hidden';
                                if ($event->cancellation_deadline_type === Event::DEADLINE_TYPE_DAY) {
                                    $cancellationDeadlineClass = '';
                                }
                                ?>
                                <?= $this->Form->control('cancellation_deadline_number', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w150']
                                ]) ?>
                                <div class="mgl-10">
                                    <?= $this->Form->control('cancellation_deadline_type', [
                                        'type' => 'select',
                                        'options' => $valueOptions['deadlineType'],
                                        'class' => ['js_cancellation_deadline_type', 'select'],
                                        'data-time' => Event::DEADLINE_TYPE_TIME,
                                        'data-day' => Event::DEADLINE_TYPE_DAY,
                                    ]) ?>
                                </div>
                                <div
                                    class="mgl-10 js_toggle_cancellation_deadline <?= h($cancellationDeadlineClass) ?> js_toggle_cancellation_deadline_type_<?= h(Event::DEADLINE_TYPE_DAY) ?>">
                                    <?= $this->Form->control('cancellation_deadline_time', [
                                        'type' => 'select',
                                        'options' => $valueOptions['deadlineTime'],
                                        'class' => ['js_cancellation_deadline_time', 'select'],
                                    ]) ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <h3 class="ttl-s mgt-20 mgb-20">予約回数制限</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約回数の上限<?= $this->Template->isRequire('reservation_limit_future') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('reservation_limit_future', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w50']
                                ]) ?>
                                <span class="txt mgl-10">回まで</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約回数の上限（1月あたり）<?= $this->Template->isRequire('reservation_limit_month') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('reservation_limit_month', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w50']
                                ]) ?>
                                <span class="txt mgl-10">回まで</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約回数の上限（1日あたり）<?= $this->Template->isRequire('reservation_limit_day') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('reservation_limit_day', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w50']
                                ]) ?>
                                <span class="txt mgl-10">回まで</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約回数の上限（過去含む全て）<?= $this->Template->isRequire('reservation_limit_all') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('reservation_limit_all', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['textbox_w50']
                                ]) ?>
                                <span class="txt mgl-10">回まで</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                同日時の重複予約防止<?= $this->Template->isRequire('duplication_check_flg') ?></div>
                        </th>
                        <td>
                            <?= $this->Template->radio('duplication_check_flg', [
                                'type' => 'radio',
                                'label' => false,
                                'options' => $valueOptions['duplicationCheckFlg'],
                            ]) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <h3 class="ttl-s mgt-20 mgb-20">表示設定</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                「空きあり」のカラー<?= $this->Template->isRequire('background_color_type') ?>
                            </div>
                        </th>
                        <td>
                            <?= $this->Template->radio('background_color_type', [
                                'type' => 'radio',
                                'label' => false,
                                'options' => $valueOptions['backgroundColorType'],
                                'class' => ['js_change_background_color'],
                            ]) ?>
                            <div class="desc-wrap">
                                <p>「予約枠のカラー設定」で作成したカラーに変更できます。</p>
                            </div>
                            <?php
                            $colorCodeClass = 'hidden';
                            if ($event->background_color_type === Event::BACKGROUND_COLOR_TYPE_COLOR_CODE) {
                                $colorCodeClass = '';
                            }
                            ?>
                            <div
                                class="js_toggle_background_color <?= h($colorCodeClass) ?> js_toggle_background_color_<?= h(Event::BACKGROUND_COLOR_TYPE_COLOR_CODE) ?>">
                                <dl>
                                    <dt>カラー選択</dt>
                                    <dd>
                                        <?= $this->Form->control('color_chip_id', [
                                            'type' => 'select',
                                            'class' => ['select'],
                                            'options' => $valueOptions['colorChip'],
                                            'empty' => true,
                                        ]) ?>
                                    </dd>
                                </dl>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                「空きあり」以外（予約サイト）<?= $this->Template->isRequire('background_color_replace_front') ?></div>
                        </th>
                        <td class="btn-inline">
                            <?= $this->Template->checkbox('background_color_replace_front', [
                                'type' => 'select',
                                'multiple' => 'checkbox',
                                'options' => $valueOptions['backgroundColorReplace'],
                            ]) ?>
                            <div class="desc-wrap">
                                <p>チェックを入れた予約状況で、「予約枠のカラー設定」で指定した色が適用されます。</p>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                「空きあり」以外（管理画面）<?= $this->Template->isRequire('background_color_replace_admin') ?></div>
                        </th>
                        <td class="btn-inline">
                            <?= $this->Template->checkbox('background_color_replace_admin', [
                                'type' => 'select',
                                'multiple' => 'checkbox',
                                'options' => $valueOptions['backgroundColorReplace'],
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                カレンダー表示<?= $this->Template->isRequire('format_type_display') ?>
                            </div>
                        </th>
                        <td class="btn-inline">
                            <?= $this->Template->checkbox('format_type_display', [
                                'type' => 'select',
                                'multiple' => 'checkbox',
                                'options' => $valueOptions['formatTypeDisplay'],
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                予約履歴表示タイプ<?= $this->Template->isRequire('usage_time_notation') ?></div>
                        </th>
                        <td>
                            <?= $this->Template->radio('usage_time_notation', [
                                'type' => 'radio',
                                'options' => $valueOptions['usageTimeNotation'],
                            ]) ?>
                            <div class="desc-wrap">
                                <p><?= h($valueOptions['usageTimeNotation'][Event::USAGE_TIME_NOTATION_USE_TIME]) ?>
                                    ：（例）10：00 ～ 120分</p>
                                <p><?= h($valueOptions['usageTimeNotation'][Event::USAGE_TIME_NOTATION_END_TIME]) ?>
                                    ：（例）10：00 ～ 12：00</p>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">公開期間<?= $this->Template->isRequire('public_from') ?></div>
                        </th>
                        <td>
                            <div class="d-flex">
                                <?= $this->Form->control('public_from', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['js-datepicker-time'],
                                ]) ?>
                                <span class="txt mgl-10 mgr-10">から</span>
                                <?= $this->Form->control('public_to', [
                                    'type' => 'text',
                                    'label' => false,
                                    'class' => ['js-datepicker-time',]
                                ]) ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">公開設定<?= $this->Template->isRequire('public_flg') ?></div>
                        </th>
                        <td>
                            <?= $this->Template->radio('public_flg', [
                                'type' => 'radio',
                                'options' => $valueOptions['publicFlg'],
                                'default' => Event::PUBLIC_FLG_ON,
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">表示順<?= $this->Template->isRequire('sort_no') ?></div>
                        </th>
                        <td>
                            <?= $this->Form->control('sort_no', [
                                'type' => 'text',
                                'label' => false,
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">QRコード<?= $this->Template->isRequire('qr_code_flg') ?></div>
                        </th>
                        <td>
                            <?= $this->Template->radio('qr_code_flg', [
                                'type' => 'radio',
                                'options' => $valueOptions['qrCodeFlg'],
                            ]) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <h3 class="ttl-s mgt-20 mgb-20">その他</h3>
                <table class="input-box">
                    <tbody>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                画像<?= $this->Template->isRequire('event_images') ?>
                            </div>
                        </th>
                        <td>
                            <div class="addInput">
                                <dl>
                                    <?php for ($eventImagesIndex = 0; $eventImagesIndex < $this->Configure->read('Master.event.images.num'); $eventImagesIndex++) : ?>
                                        <dt>画像 <?= h($eventImagesIndex + 1) ?>枚目</dt>
                                        <dd>
                                            <?= $this->Form->hidden('event_images.' . $eventImagesIndex . '.id') ?>
                                            <?= $this->Form->control('event_images.' . $eventImagesIndex . '.url', [
                                                'type' => 'text',
                                                'label' => false,
                                            ]) ?>

                                            <?= $this->Form->button('ファイル管理から選択', [
                                                'type' => 'button',
                                                'class' => ['js_file_select_btn', 'cmn-btn', 'is-blue', 'is-circle'],
                                                'data-name' => 'event_images[' . $eventImagesIndex . '][url]',
                                                'data-title' => 'ファイル管理から選択'
                                            ]) ?>
                                        </dd>
                                    <?php endfor; ?>
                                </dl>
                                <div class="hide">
                                    <div id="js_frameWindow"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                注釈<?= $this->Template->isRequire('event_remarks') ?>
                            </div>
                        </th>
                        <td>
                            <?php if (count($valueOptions['formItemId']) >= 1) : ?>
                                <div class="groupInput">
                                    <div class="js_event_remarks_container groupInput-box">
                                        <?php if (isset($event->event_remarks)): ?>
                                            <?php foreach ($event->event_remarks as $eventRemarkIndex => $eventRemarkData): ?>
                                                <?= $this->element('Admin/Events/fieldset_remark', [
                                                    'event' => $event,
                                                    'eventRemarkIndex' => $eventRemarkIndex,
                                                    'eventRemarkData' => $eventRemarkData,
                                                ]) ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?= $this->FormError->errorWithoutNested('event_remarks') ?>
                                    </div>
                                    <?= $this->Form->button('注釈追加', [
                                        'type' => 'button',
                                        'class' => ['js_add_input', 'cmn-btn', 'is-addCell', 'mgt-20'],
                                        'data-container' => '.js_event_remarks_container',
                                        'data-html' => $this->element('Admin/Events/fieldset_remark', [
                                            'event' => $event,
                                            'eventRemarkIndex' => '%INDEX%',
                                            'event' => null,
                                        ]),
                                        'data-index-element' => '.js_event_remarks_index',
                                        'data-index-replace' => '%INDEX%',
                                    ]) ?>
                                </div>
                            <?php else : ?>
                                <p>注釈項目が予約フォームに存在しません。</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                説明文<?= $this->Template->isRequire('description') ?>
                            </div>
                        </th>
                        <td>
                            <?= $this->Form->control('description', [
                                'type' => 'textarea',
                                'label' => false,
                                'class' => ['wysiwyg'],
                                'rows' => 20,
                            ]) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="form-input-set tab-content" id="event_holidays_tab">
                <fieldset>
                    <table class="input-box">
                        <tbody>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">
                                    個別休業設定<?= $this->Template->isRequire('event_holidays') ?></div>
                            </th>
                            <td>
                                <?= $this->element('Admin/EventHolidays/fieldset', [
                                    'eventHoliday' => $event->get('event_holidays'),
                                    'valueOptions' => $valueOptions,
                                    'mode' => 'edit',
                                ]) ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </fieldset>
            </div>
            <div class="form-input-set tab-content" id="event_stock_settings_tab">
                <fieldset>
                    <table class="input-box">
                        <tbody>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">
                                    例外日設定<?= $this->Template->isRequire('event_stock_settings') ?></div>
                            </th>
                            <td>
                                <?= $this->element('Admin/EventStockSettings/fieldset', [
                                    'eventStockSetting' => $event->get('event_stock_settings'),
                                    'valueOptions' => $valueOptions,
                                    'mode' => 'edit',
                                ]) ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </fieldset>
            </div>
        </div>
    </fieldset>
</div>
