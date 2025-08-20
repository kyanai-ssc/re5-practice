<div class="schedule-header sticky js_search_header">
    <?php if (!empty($calendarForm->getData('id')) && !empty($calendar->getDisplayEventForQueryId($calendarForm->getData('id')))): ?>
        <?= $this->Form->button($calendar->getDisplayEventForQueryId($calendarForm->getData('id')), [
            'type' => 'submit',
            'class' => ['idBtn', 'js_event_id', 'cmn-btn', 'is-blue'],
            'escapeTitle' => false
        ]) ?>
    <?php endif; ?>
    <div class="is-top d-flex">
        <?php if ($showDate): ?>
            <div class="select-day">
                <?php if (!is_null($calendar->getDatePrevious())): ?>
                    <?= $this->Form->button('', [
                        'type' => 'button',
                        'id' => 'prev',
                        'class' => ['btn-calender', 'is-prev', 'js_change_date'],
                        'data-date' => $calendar->getDatePrevious()->format('Y/m/d'),
                    ]) ?>
                <?php endif; ?>
                <?php if (isset($currentDate)): ?>
                    <button type="button" class="current-day js-datepicker tooltip js_date_select"
                            title="<?= $this->Tr->h('reservation/calendar/dateBtn') ?>" data-toggle
                            value="<?= h($calendar->getDateFrom()) ?>">
                    <span class="icon"><svg class="icon-calendar">
                            <use xlink:href="#icon_calendar"></use>
                        </svg></span>
                        <?= h($currentDate) ?>
                    </button>
                <?php endif; ?>
                <?php if (!is_null($calendar->getDateNext())): ?>
                    <?= $this->Form->button('', [
                        'type' => 'button',
                        'id' => 'next',
                        'class' => ['btn-calender', 'is-next', 'js_change_date'],
                        'data-date' => $calendar->getDateNext()->format('Y/m/d'),
                    ]) ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (count($valueOptions['calendarType']) > 1): ?>
            <div class="viewChange">
                <ul>
                    <?php foreach (array_keys($valueOptions['calendarType']) as $calendarType): ?>
                        <li class="<?php if (((string)$calendar->getCalendarType()) === ((string)$calendarType)): ?>select<?php endif; ?>">
                            <a class="icon js_calendar_type" href="#" data-calendar-type="<?= h($calendarType) ?>">
                                <svg class="icon-scNav">
                                    <use
                                        xlink:href="#<?= h($this->Configure->read('Master.event.calendarTypeSvg.' . $calendarType)) ?>"/>
                                </svg>
                                <?= $this->Tr->h('reservation/calendar/typeBtn/' . $this->Configure->read('Master.event.calendarTypeWordKey.' . $calendarType)) ?>
                                <?php if ($this->Configure->check('Master.event.calendarTypeWeekDayText.' . $calendarType)): ?>
                                    <span><?= h($this->Configure->read('Master.event.calendarTypeWeekDayText.' . $calendarType)) ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <?= $this->element('User/Reservations/calendar_color_chips', [
        'colorChips' => $calendar->getColorChips(),
    ]) ?>
    <?php if ($showPager): ?>
        <div class="mgt-20 mgb-20">
            <?= $this->element('User/Common/search/paginator') ?>
        </div>
    <?php endif; ?>
    <?= $additionHtml ?>
</div>
