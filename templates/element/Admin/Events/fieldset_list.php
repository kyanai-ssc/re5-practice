<table class="input-box">
    <tbody>
    <tr class="field-input">
        <th class="ttl-input">
            <div class="ttl-input-wrap">予約枠ID</div>
        </th>
        <td>
            <?= $this->Form->control('id', [
                'type' => 'text',
                'label' => false,
            ]) ?>
        </td>

    </tr>
    <tr class="field-input">
        <th class="ttl-input">
            <div class="ttl-input-wrap">カテゴリー</div>
        </th>
        <td>
            <?= $this->Label->renderSelect([
                'type' => $this->Configure->read('Master.label.type.other'),
            ]) ?>
        </td>
    </tr>
    <tr class="field-input">
        <th class="ttl-input">
            <div class="ttl-input-wrap">絞り込みキーワード</div>
        </th>
        <td>

            <?= $this->element('Admin/Events/fieldset_tags', [
                'tagLists' => $valueOptions['tagList'],
            ]) ?>
        </td>
    </tr>
    <tr class="field-input">
        <th class="ttl-input">
            <div class="ttl-input-wrap">予約枠名</div>
        </th>
        <td>
            <?= $this->Form->control('name', [
                'type' => 'text',
                'label' => false,
            ]) ?>
        </td>
    </tr>
    <?php if ($this->Setting->getSystemSetting()->canCoordinateVideoMeeting()): ?>
        <tr class="field-input">
            <th class="ttl-input">
                <div class="ttl-input-wrap">ビデオ会議主催者</div>
            </th>
            <td>
                <?= $this->Form->control('organizer_id', [
                    'type' => 'select',
                    'label' => false,
                    'class' => ['select'],
                    'options' => $valueOptions['organizerList'],
                    'empty' => true,
                ]) ?>
            </td>
        </tr>
    <?php endif; ?>
    <?php if ($this->Setting->getSystemSetting()->useSmartLock()) : ?>
        <tr class="field-input">
            <th class="ttl-input">
                <div class="ttl-input-wrap">
                    <?php if ($this->SmartLock->useRemoteLock()) : ?>
                        デバイスキー
                    <?php elseif ($this->SmartLock->useAkerun()) : ?>
                        Akerun ID
                    <?php endif; ?>
                </div>
            </th>
            <td>
                <?= $this->Form->control('event_smart_lock.smart_lock_device_key', [
                    'type' => 'text',
                    'label' => false,
                ]) ?>
            </td>
        </tr>
    <?php endif; ?>
    <tr class="field-input">
        <th class="ttl-input">
            <div class="ttl-input-wrap">利用期間</div>
        </th>
        <td class="d-flex">
            <?= $this->Form->control('schedule_date_from', [
                'type' => 'text',
                'class' => ['js-datepicker-type-range-start']
            ]) ?>
            <span class="txt mgl-10 mgr-10">～</span>
            <?= $this->Form->control('schedule_date_to', [
                'type' => 'text',
                'class' => ['js-datepicker-type-range-end']
            ]) ?>
        </td>
    </tr>

    <tr class="field-input">
        <th class="ttl-input">
            <div class="ttl-input-wrap">公開日</div>
        </th>
        <td class="d-flex">
            <?= $this->Form->control('public_from', [
                'type' => 'text',
                'class' => ['js-datepicker-type-range-start']
            ]) ?>
            <span class="txt mgl-10 mgr-10">～</span>
            <?= $this->Form->control('public_to', [
                'type' => 'text',
                'class' => ['js-datepicker-type-range-end']
            ]) ?>
        </td>
    </tr>
    <tr class="field-input">
        <th class="ttl-input">
            <div class="ttl-input-wrap">予約枠タイプ</div>
        </th>
        <td>
            <?= $this->Template->checkbox('type', [
                'type' => 'multicheckbox',
                'label' => false,
                'options' => $valueOptions['type'],
            ]) ?>
        </td>
    </tr>
    <tr class="field-input">
        <th class="ttl-input">
            <div class="ttl-input-wrap">公開設定</div>
        </th>
        <td>
            <?= $this->Template->checkbox('public_flg', [
                'type' => 'multicheckbox',
                'label' => false,
                'options' => $valueOptions['publicFlg'],
            ]) ?>
        </td>
    </tr>
    </tbody>
</table>
