<?php

use App\Model\Entity\Event;
?>

<?= $this->Html->script('admin/events/fieldset', ['block' => true]); ?>
<?= $this->Html->script('admin/events/fieldset_together', ['block' => true]); ?>

<div class="form-input-set mgt-20">
    <fieldset>
        <h3 class="ttl-s mgt-20 mgb-20">基本設定</h3>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.name', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'name',
                        'checked' => isset($eventsInputs['update']['name']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約枠名
                    </div>
                </th>
                <td class="js_together_edit_name">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.label', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'label',
                        'checked' => isset($eventsInputs['update']['label']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        カテゴリー
                    </div>
                </th>
                <td class="js_together_edit_label">
                    <?= $this->Label->renderSelect([
                        'type' => $this->Configure->read('Master.label.type.other'),
                        'labelId' => isset($eventsInputs['label_id']) ? $eventsInputs['label_id'] : null,
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.tag', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'tag',
                        'checked' => isset($eventsInputs['update']['tag']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        絞り込みキーワード
                    </div>
                </th>
                <td class="js_together_edit_tag">
                    <?= $this->element('Admin/Events/fieldset_tags', [
                        'tagLists' => $tagLists,
                    ]) ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php if ($this->Setting->getSystemSetting()->useSmartLock()): ?>
        <h3 class="ttl-s mgt-20 mgb-20">
            <?= h($this->SmartLock->getName()) ?>設定
        </h3>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.event_smart_lock.smart_lock_device_key', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'event_smart_lock_smart_lock_device_key',
                        'checked' => isset($eventsInputs['update']['event_smart_lock']['smart_lock_device_key']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        <?php if ($this->SmartLock->useRemoteLock()): ?>
                            デバイスキー
                        <?php elseif ($this->SmartLock->useAkerun()): ?>
                            Akerun ID
                        <?php endif; ?>
                    </div>
                </th>
                <td class="js_together_edit_event_smart_lock_smart_lock_device_key">
                    <div class="d-flex">
                        <?= $this->Form->control('event_smart_lock.smart_lock_device_key', [
                            'type' => 'text',
                            'label' => false,
                        ]) ?>
                    </div>
                </td>
            </tr>
            <?php if ($this->SmartLock->useAkerun()): ?>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.event_smart_lock.smart_lock_key_url_flg', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'event_smart_lock_smart_lock_key_url_flg',
                        'checked' => isset($eventsInputs['update']['event_smart_lock']['smart_lock_key_url_flg']),
                        'hiddenField' => false,

                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        URL合鍵
                    </div>
                </th>
                <td class="js_together_edit_event_smart_lock_smart_lock_key_url_flg">
                    <?= $this->Template->radio('event_smart_lock.smart_lock_key_url_flg', [
                        'type' => 'radio',
                        'label' => false,
                        'options' => $valueOptions['common'],
                    ]) ?>
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <h3 class="ttl-s mgt-20 mgb-20">在庫数設定</h3>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.stock_unit', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'stock_unit',
                        'checked' => isset($eventsInputs['update']['stock_unit']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">在庫の単位</div>
                </th>
                <td class="js_together_edit_stock_unit">
                    <?= $this->Form->control('stock_unit', [
                        'type' => 'text',
                        'label' => false,
                        'class' => ['textbox_w50']
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.stock_display_type', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'stock_display_type',
                        'checked' => isset($eventsInputs['update']['stock_display_type']),
                        'hiddenField' => false,

                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        在庫数表示設定
                    </div>
                </th>
                <td class="js_together_edit_stock_display_type">
                    <?= $this->Template->radio('stock_display_type', [
                        'type' => 'radio',
                        'options' => $valueOptions['stockDisplayType'],
                        'class' => ['js_change_symbolic_flg'],
                    ]) ?>

                    <?php
                    $eventStockMarksClass = 'hidden';
                    if (isset($eventsInputs['stock_display_type']) && (string)$eventsInputs['stock_display_type'] === (string)Event::STOCK_DISPLAY_TYPE_ICON) {
                        $eventStockMarksClass = '';
                    }
                    ?>
                    <div
                        class="js_toggle_vent_stock_marks <?= h($eventStockMarksClass) ?> js_toggle_vent_stock_marks_<?= h(Event::STOCK_DISPLAY_TYPE_ICON) ?>">
                        <div>
                            <?= $this->FormError->errorWithoutNested('event_stock_marks') ?>
                            <table class="pliceInput-detail">
                                <thead>
                                <tr class="field-input">
                                    <th>記号</th>
                                    <th>切り替えタイミング</th>
                                    <th>削除</th>
                                </tr>
                                </thead>
                                <tbody class="js_event_stock_marks_container">
                                <?php if (isset($eventsInputs['event_stock_marks'])): ?>
                                    <?php foreach ($eventsInputs['event_stock_marks'] as $eventStockMarkIndex => $eventStockMarkData): ?>
                                        <?= $this->element('Admin/Events/fieldset_event_stock_marks', [
                                            'event' => $eventsInputs,
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
        <?php if ($isChangeCharge) : ?>
            <h3 class="ttl-s mgt-20 mgb-20">料金設定</h3>
            <table class="input-box">
                <tbody>
                <tr class="field-input">
                    <th class="ttl-input is-check">
                        <?= $this->Template->checkbox('update.charge', [
                            'type' => 'checkbox',
                            'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                            'value' => 1,
                            'class' => ['js_together_edit'],
                            'data-input' => 'charge',
                            'checked' => isset($eventsInputs['update']['charge']),
                            'hiddenField' => false,

                        ]) ?>
                    </th>
                    <th class="ttl-input">
                        <div class="ttl-input-wrap">
                            料金
                        </div>
                    </th>
                    <td class="js_together_edit_charge">
                        <?= $this->Form->control('charge', [
                            'type' => 'text',
                            'label' => false,
                            'class' => ['textbox_w150']
                        ]) ?>
                    </td>
                </tr>
                </tbody>
            </table>
        <?php endif; ?>
        <h3 class="ttl-s mgt-20 mgb-20">予約受付設定</h3>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.waiting_cancellation_flg', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'waiting_cancellation_flg',
                        'checked' => isset($eventsInputs['update']['waiting_cancellation_flg']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        キャンセル待ち通知
                    </div>
                </th>
                <td class="js_together_edit_waiting_cancellation_flg">
                    <?= $this->Template->radio('waiting_cancellation_flg', [
                        'type' => 'radio',
                        'options' => $valueOptions['common'],
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.reservation_status_id', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'reservation_status_id',
                        'checked' => isset($eventsInputs['update']['reservation_status_id']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約登録時のステータス
                    </div>
                </th>
                <td class="js_together_edit_reservation_status_id">
                    <?= $this->Template->radio('reservation_status_id', [
                        'type' => 'radio',
                        'options' => $valueOptions['reservationStatusId'],
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.form_pattern_id', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'form_pattern_id',
                        'checked' => isset($eventsInputs['update']['form_pattern_id']),
                        'hiddenField' => false,

                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約内容の表示パターン
                    </div>
                </th>
                <td class="js_together_edit_form_pattern_id">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.reception_period_number', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'reception_period_number',
                        'checked' => isset($eventsInputs['update']['reception_period_number']),
                        'hiddenField' => false,

                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約受付開始タイミング
                    </div>
                </th>
                <td class="js_together_edit_reception_period_number">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.registration_deadline', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'registration_deadline',
                        'checked' => isset($eventsInputs['update']['registration_deadline']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約受付締切タイミング
                    </div>
                </th>
                <td class="js_together_edit_registration_deadline">
                    <div class="d-flex">
                        <?= $this->Form->control('registration_deadline_criterion', [
                            'type' => 'radio',
                            'options' => $valueOptions['deadlineCriterion'],
                        ]) ?>
                        <span class="txt mgl-10 mgr-10">の</span>
                        <?php
                        $registrationDeadlineClass = 'hidden';
                        if (isset($eventsInputs['registration_deadline_type']) && (string)$eventsInputs['registration_deadline_type'] === (string)Event::DEADLINE_TYPE_DAY) {
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.editing_deadline', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'editing_deadline',
                        'checked' => isset($eventsInputs['update']['editing_deadline']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約変更締切タイミング
                    </div>
                </th>
                <td class="js_together_edit_editing_deadline">
                    <div class="d-flex">
                        <?= $this->Form->control('editing_deadline_criterion', [
                            'type' => 'radio',
                            'options' => $valueOptions['deadlineCriterion'],
                        ]) ?>
                        <span class="txt mgl-10 mgr-10">の</span>
                        <?php
                        $editingDeadlineClass = 'hidden';
                        if (isset($eventsInputs['editing_deadline_type']) && (string)$eventsInputs['editing_deadline_type'] === (string)Event::DEADLINE_TYPE_DAY) {
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.cancellation_deadline', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'cancellation_deadline',
                        'checked' => isset($eventsInputs['update']['cancellation_deadline']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約キャンセル締切タイミング
                    </div>
                </th>
                <td class="js_together_edit_cancellation_deadline">
                    <div class="d-flex">
                        <?= $this->Form->control('cancellation_deadline_criterion', [
                            'type' => 'radio',
                            'options' => $valueOptions['deadlineCriterion'],
                        ]) ?>
                        <span class="txt mgl-10 mgr-10">の</span>
                        <?php
                        $cancellationDeadlineClass = 'hidden';
                        if (isset($eventsInputs['cancellation_deadline_type']) && (string)$eventsInputs['cancellation_deadline_type'] === (string)Event::DEADLINE_TYPE_DAY) {
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.reservation_limit_future', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'reservation_limit_future',
                        'checked' => isset($eventsInputs['update']['reservation_limit_future']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約回数の上限
                    </div>
                </th>
                <td class="js_together_edit_reservation_limit_future">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.reservation_limit_month', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'reservation_limit_month',
                        'checked' => isset($eventsInputs['update']['reservation_limit_month']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約回数の上限（1月あたり）
                    </div>
                </th>
                <td class="js_together_edit_reservation_limit_month">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.reservation_limit_day', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'reservation_limit_day',
                        'checked' => isset($eventsInputs['update']['reservation_limit_day']),
                        'hiddenField' => false,

                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約回数の上限（1日あたり）
                    </div>
                </th>
                <td class="js_together_edit_reservation_limit_day">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.reservation_limit_all', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'reservation_limit_all',
                        'checked' => isset($eventsInputs['update']['reservation_limit_all']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約回数の上限（過去含む全て）
                    </div>
                </th>
                <td class="js_together_edit_reservation_limit_all">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.duplication_check_flg', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'duplication_check_flg',
                        'checked' => isset($eventsInputs['update']['duplication_check_flg']),
                        'hiddenField' => false,

                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        同日時の重複予約防止
                    </div>
                </th>
                <td class="js_together_edit_duplication_check_flg">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.background_color_type', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'background_color_type',
                        'checked' => isset($eventsInputs['update']['background_color_type']),
                        'hiddenField' => false,

                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        「空きあり」のカラー
                    </div>
                </th>
                <td class="js_together_edit_background_color_type">
                    <?= $this->Template->radio('background_color_type', [
                        'type' => 'radio',
                        'label' => false,
                        'options' => $valueOptions['backgroundColorType'],
                        'class' => ['js_change_background_color'],
                    ]) ?>
                    <?php
                    $colorCodeClass = 'hidden';
                    if (isset($eventsInputs['background_color_type']) && (string)$eventsInputs['background_color_type'] === (string)Event::BACKGROUND_COLOR_TYPE_COLOR_CODE) {
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.background_color_replace_front', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'background_color_replace_front',
                        'checked' => isset($eventsInputs['update']['background_color_replace_front']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        「空きあり」以外（予約サイト）
                    </div>
                </th>
                <td class="js_together_edit_background_color_replace_front btn-inline">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.background_color_replace_admin', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'background_color_replace_admin',
                        'checked' => isset($eventsInputs['update']['background_color_replace_admin']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        「空きあり」以外（管理画面）
                    </div>
                </th>
                <td class="js_together_edit_background_color_replace_admin btn-inline">
                    <?= $this->Template->checkbox('background_color_replace_admin', [
                        'type' => 'select',
                        'multiple' => 'checkbox',
                        'options' => $valueOptions['backgroundColorReplace'],
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.format_type_display', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'format_type_display',
                        'checked' => isset($eventsInputs['update']['format_type_display']),
                        'hiddenField' => false,

                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        カレンダー表示
                    </div>
                </th>
                <td class="js_together_edit_format_type_display btn-inline">
                    <?= $this->Template->checkbox('format_type_display', [
                        'type' => 'select',
                        'multiple' => 'checkbox',
                        'options' => $valueOptions['formatTypeDisplay'],
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.usage_time_notation', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'usage_time_notation',
                        'checked' => isset($eventsInputs['update']['usage_time_notation']),
                        'hiddenField' => false,

                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        予約履歴表示タイプ
                    </div>
                </th>
                <td class="js_together_edit_usage_time_notation">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.public_from_to', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'public_from_to',
                        'checked' => isset($eventsInputs['update']['public_from_to']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        公開期間
                    </div>
                </th>
                <td class="js_together_edit_public_from_to">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.public_flg', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'public_flg',
                        'checked' => isset($eventsInputs['update']['public_flg']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        公開設定
                    </div>
                </th>
                <td class="js_together_edit_public_flg">
                    <?= $this->Template->radio('public_flg', [
                        'type' => 'radio',
                        'options' => $valueOptions['publicFlg'],
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.qr_code_flg', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'qr_code_flg',
                        'checked' => isset($eventsInputs['update']['qr_code_flg']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        QRコード
                    </div>
                </th>
                <td class="js_together_edit_qr_code_flg">
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.event_images', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'event_images',
                        'checked' => isset($eventsInputs['update']['event_images']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        画像
                    </div>
                </th>
                <td class="js_together_edit_event_images">
                    <div class="addInput">
                        <dl>
                            <?php for ($eventImagesIndex = 0; $eventImagesIndex < $this->Configure->read('Master.event.images.num'); $eventImagesIndex++) : ?>
                                <dt>画像<?= h($eventImagesIndex + 1) ?>枚目</dt>
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.event_remarks', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'event_remarks',
                        'checked' => isset($eventsInputs['update']['event_remarks']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        注釈
                    </div>
                </th>
                <td class="js_together_edit_event_remarks">
                    <?php if (count($valueOptions['formItemId']) >= 1) : ?>
                        <div class="groupInput">
                            <div class="js_event_remarks_container groupInput-box">
                                <?php if (isset($eventsInputs['event_remarks'])): ?>
                                    <?php foreach ($eventsInputs['event_remarks'] as $eventRemarkIndex => $eventRemarkData): ?>
                                        <?= $this->element('Admin/Events/fieldset_remark', [
                                            'event' => $eventsInputs,
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
                <th class="ttl-input is-check">
                    <?= $this->Template->checkbox('update.description', [
                        'type' => 'checkbox',
                        'label' => ['class' => ['cmn-check', 'btn-tool'], 'text' => '編集する'],
                        'value' => 1,
                        'class' => ['js_together_edit'],
                        'data-input' => 'description',
                        'checked' => isset($eventsInputs['update']['description']),
                        'hiddenField' => false,
                    ]) ?>
                </th>
                <th class="ttl-input">
                    <div class="ttl-input-wrap">
                        説明文
                    </div>
                </th>
                <td class="js_together_edit_description">
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
    </fieldset>
</div>
