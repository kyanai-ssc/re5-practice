<?php if (((string)$calendarForm->getData('s')) !== ((string)$this->Configure->read('Master.common.flg.off'))
    && (!empty($formType) || !empty($tagList) || !empty($eventNameList))
): ?>
    <section class="contents-area l-main l-search">
        <h3 class="ttl-sec with-showBtn"><?= $this->Tr->h('calendar/searchTitle') ?></h3>
        <?= $this->Form->button('', [
            'type' => 'button',
            'id' => ['showBtn'],
        ]) ?>
        <div id="search-group-wrap" class="search-group-wrap">
            <?= $this->Form->create($calendarForm, [
                'type' => 'post',
                'url' => [
                    'prefix' => 'User',
                    'controller' => 'Reservations',
                    'action' => 'calendar',
                ],
                'idPrefix' => 'reservations_calendar',
                'novalidate' => true,
                'class' => ['js_calendar_form'],
            ]) ?>
            <?php if (!empty($formType)): ?>
                <?= $this->Label->renderSelect([
                    'type' => $this->Configure->read('Master.label.type.other'),
                    'public' => true,
                    'onlyPublic' => true,
                    'labelId' => $calendarForm->getData('label_id'),
                    'userLabelId' => $this->CommonData->getUserLabelId(),
                ]) ?>
            <?php endif; ?>
            <?php if (!empty($tagList)): ?>
                <?= $this->element('User/Tags/search', [
                    'tagList' => $valueOptions['tagGroup'],
                ]) ?>
            <?php endif; ?>
            <?php if (!empty($eventNameList)): ?>
                <?= $this->element('User/Events/search', [
                    'eventNameList' => $valueOptions['eventNameList'],
                ]) ?>
            <?php endif; ?>
            <div class="btn-group">
                <?php
                $calendarQuery = [
                    'date' => $calendarForm->getData('date'),
                    'calendar_type' => $calendarForm->getData('calendar_type'),
                ]; ?>
                <?php if (isset($calendarFrame)): ?>
                    <?php $calendarQuery['frame'] = $calendarFrame; ?>
                <?php endif; ?>
                <?php if ($selectCalendar): ?>
                    <?php $calendarQuery['select_calendar'] = $this->Configure->read('Master.common.flg.on'); ?>
                <?php endif; ?>
                <?php if (is_scalar($calendarForm->getData('edit_reservation_id'))): ?>
                    <?php $calendarQuery['edit_reservation_id'] = $calendarForm->getData('edit_reservation_id'); ?>
                <?php endif; ?>
                <?= $this->Form->button($this->Tr->t('reservation/calendar/resetBtn'), [
                    'type' => 'reset',
                    'class' => ['js_change_url', 'is-reset', 'cmn-btn'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'User',
                        'controller' => 'Reservations',
                        'action' => 'calendar',
                        '?' => $calendarQuery,
                    ], ['escape' => false]),
                ]) ?>
                <?= $this->Form->button($this->Tr->t('reservation/calendar/searchBtn'), [
                    'type' => 'submit',
                    'class' => ['cmn-btn', 'is-blue'],
                ]) ?>

            </div>
            <div class="hidden">
                <?= $this->Form->hidden('calendar_type') ?>
                <?= $this->Form->hidden('date') ?>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </section>
<?php endif; ?>
<?= $this->element($calendar->getTemplatePath(), [
    'calendar' => $calendar,
    'valueOptions' => $valueOptions,
    'selectCalendar' => $selectCalendar,
]) ?>
<div class="hidden">
    <span class="js_calendar_search_data" data-search="<?= h(json_encode($calendarForm->getData())) ?>"></span>
    <?php if ($this->Authority->isAuthority(true, 'Reservations', 'add') && $this->Setting->getSiteSetting()->canAccessUserReservation()): ?>
        <span class="js_has_reservation_authority"></span>
    <?php endif; ?>
</div>
