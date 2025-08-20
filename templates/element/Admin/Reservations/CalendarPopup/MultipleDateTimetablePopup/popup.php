<div class="tooltip" title="予約枠時間一覧" data-popup-type="<?= h($calendarPopup->getPopupType()) ?>"
     data-search-data="<?= h(json_encode($calendarPopup->getPopupSearchData())) ?>">
    <?php if ($this->Authority->isAuthority(true, 'Events', 'edit')): ?>
        <h4 class="ttl-popup">
            <a href="<?= $this->Url->build([
                'prefix' => 'Admin',
                'controller' => 'Events',
                'action' => 'edit',
                'id' => $calendarPopup->getEvent()->get('id'),
            ]) ?>" target="_blank">
                <?php if ($calendarPopup->getEvent()->has('label')): ?>
                    <span class="name-label">[<?= h($calendarPopup->getEvent()->get('label')->get('name')) ?>]</span>
                <?php endif; ?>
                <span class="name-detail"><?= h($calendarPopup->getEvent()->get('name')) ?>
            </a>
        </h4>
    <?php else: ?>
        <h4 class="ttl-popup ttl-popup-no-link">
            <?php if ($calendarPopup->getEvent()->has('label')): ?>
                <span class="name-label">[<?= h($calendarPopup->getEvent()->get('label')->get('name')) ?>]</span>
            <?php endif; ?>
            <span class="name-detail"><?= h($calendarPopup->getEvent()->get('name')) ?>
        </h4>
    <?php endif; ?>
    <div class="date-wrap clearfix">
        <div class="select-day f-l">
            <?= $this->Form->button('', [
                'type' => 'button',
                'id' => 'prev',
                'class' => ['btn-calender', 'is-prev', 'js_change_popup_date'],
                'data-date' => $calendarPopup->getDatePrevious()->format('Y/m/d'),
            ]) ?>
            <button type="button" class="current-day tooltip js_date_select" title="日付を変える" data-toggle
                  value="<?= h($calendarPopup->getDateFrom()) ?>">
                    <span class="icon"><svg class="icon-calendar">
                            <use xlink:href="#icon_calendar"></use>
                        </svg></span>
                    <?= h($this->Template->displayDayAndWeek($calendarPopup->getDateFrom())) ?>
                    </button>
            <?= $this->Form->button('', [
                'type' => 'button',
                'id' => 'next',
                'class' => ['btn-calender', 'is-next', 'js_change_popup_date'],
                'data-date' => $calendarPopup->getDateNext()->format('Y/m/d'),
            ]) ?>
        </div>
    </div>
    <?= $this->element('Admin/Reservations/calendar_color_chips', [
        'colorChips' => $calendarPopup->getColorChips(),
    ]) ?>
    <div class="list-typeA">
        <div class="is-listOnly pc-only">
            <div class="reserve_list_head">
                <ul>
                    <li class="h-time">
                        時間
                    </li>
                    <li class="h-status">
                        空き情報
                    </li>
                </ul>
            </div>
        </div>
        <div class="reserve_list_body">
            <ul>
                <?php foreach ($calendarPopup->getTimetable()->getTimetable() as $eventUnit): ?>
                    <li class="list_body_line_wrap clearfix js_apply_style" data-style="<?= h(json_encode($calendarPopup->createCss($eventUnit))) ?>">
                        <ul class="list_body_line">
                            <li class="b-time">
                                <?= h($eventUnit->getDateTimeFrom()->format('H:i')) ?> ～
                            </li>
                            <li class="b-status">
                                <a
                                    href="#"
                                    class="<?= h(implode(' ', $calendarPopup->getUnitHtmlClass($eventUnit, $selectCalendar))) ?>"
                                    <?php if (!$selectCalendar): ?>
                                        data-search-data="<?= h(json_encode($calendarPopup->getDetailSearchData($eventUnit))) ?>"
                                    <?php else: ?>
                                        data-calendar-data="<?= h(json_encode($calendarPopup->getSelectCalendarData($eventUnit))) ?>"
                                    <?php endif; ?>
                                >
                                    <?= $this->element('Admin/Reservations/calendar_unit_data', [
                                        'calendar' => $calendarPopup,
                                        'eventUnit' => $eventUnit,
                                    ]) ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
