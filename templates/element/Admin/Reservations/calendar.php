<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($calendarForm, [
            'type' => 'post',
            'url' => [
                'prefix' => 'Admin',
                'controller' => 'Reservations',
                'action' => 'calendar',
            ],
            'idPrefix' => 'reservations_calendar',
            'novalidate' => true,
            'class' => ['js_calendar_form'],
        ]) ?>
        <div class="ttl-panel-show">
            <span class="ttl-s">検索フォーム</span>
            <?= $this->Form->button('', [
                'type' => 'button',
                'class' => ['showBtn'],
            ]) ?>
        </div>
        <div class="showWrap">
            <div class="panel-show-set mgt-10">
                <fieldset>
                    <legend class="ttl-search mgb-20">
                    </legend>
                    <table class="input-box" id="event-search-form">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">
                                    予約枠ID
                                </div>
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
                                <div class="ttl-input-wrap">
                                    カテゴリー
                                </div>
                            </th>
                            <td>
                                <?= $this->Label->renderSelect([
                                    'type' => $this->Configure->read('Master.label.type.other'),
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">
                                    絞り込みキーワード
                                </div>
                            </th>
                            <td>
                                <div class="tagInput">
                                    <dl class="tagInput-box">
                                        <?= $this->Form->hidden('tag_id', [
                                            'value' => '',
                                            'secure' => $this->Form::SECURE_SKIP,
                                        ]) ?>
                                        <?php foreach ($valueOptions['tagGroup'] as $tagGroupId => $tagGroup): ?>
                                            <dt>
                                                <?= h($tagGroup['name']) ?>
                                                <button type="button" class="showBtn"></button>
                                            </dt>
                                            <dd class="showWrap btn-inline">
                                                <?php foreach ($tagGroup['tag'] as $tagId => $tagName) : ?>
                                                    <?= $this->Template->checkbox('tag_id.' . $tagGroupId . '.' . $tagId, [
                                                        'type' => 'checkbox',
                                                        'value' => $tagId,
                                                        'hiddenField' => false,
                                                        'label' => [
                                                            'class' => ['cmn-check'],
                                                            'text' => $tagName,
                                                        ],
                                                    ]) ?>
                                                <?php endforeach; ?>
                                            </dd>
                                        <?php endforeach; ?>
                                    </dl>
                                </div>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">
                                    予約枠名
                                </div>
                            </th>
                            <td>
                                <?= $this->Form->control('name', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">
                                    公開期間
                                </div>
                            </th>
                            <td class="d-flex">
                                <?= $this->Form->control('public_from', [
                                    'type' => 'text',
                                    'class' => ['js-datepicker-type-range-start'],
                                ]) ?>
                                <span class="txt mgl-10 mgr-10">～</span>
                                <?= $this->Form->control('public_to', [
                                    'type' => 'text',
                                    'class' => ['js-datepicker-type-range-end'],
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
                                <div class="ttl-input-wrap">
                                    公開設定
                                </div>
                            </th>
                            <td>
                                <?= $this->Template->checkbox('public_flg', [
                                    'type' => 'multicheckbox',
                                    'options' => $valueOptions['publicFlg'],
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">
                                    予約台帳表示日
                                </div>
                            </th>
                            <td>
                                <?= $this->Form->control('date', [
                                    'type' => 'text',
                                    'class' => ['js-datepicker'],
                                    'label' => false,
                                ]) ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </div>
            <div class="btn-box mgt-20">
                <?php $resetQuery = [
                    'user_id' => $calendarForm->getData('user_id'),
                ]; ?>
                <?php if ($selectCalendar): ?>
                    <?php $resetQuery += [
                        'select_calendar' => $this->Configure->read('Master.common.flg.on'),
                    ]; ?>
                <?php endif; ?>
                <?php if (is_scalar($calendarForm->getData('edit_reservation_id'))): ?>
                    <?php $resetQuery += [
                        'edit_reservation_id' => $calendarForm->getData('edit_reservation_id'),
                    ]; ?>
                <?php endif; ?>
                <?= $this->Form->button('リセット', [
                    'type' => 'reset',
                    'class' => ['js_change_url', 'cmn-btn', 'is-reset'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'Reservations',
                        'action' => 'calendar',
                        '?' => $resetQuery,
                    ], ['escape' => false]),
                ]) ?>
                <?= $this->Form->button('検索', [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue'],
                ]) ?>
            </div>
        </div>
        <div class="hidden">
            <?= $this->Form->hidden('calendar_type') ?>
            <?= $this->Form->hidden('display_item') ?>
            <?= $this->Form->hidden('display_all_time') ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>
<?= $this->element($calendar->getTemplatePath(), [
    'calendar' => $calendar,
    'valueOptions' => $valueOptions,
    'selectCalendar' => $selectCalendar,
]) ?>
<div class="hidden">
    <span class="js_calendar_search_data" data-search="<?= h(json_encode($calendarForm->getData())) ?>"></span>
</div>
