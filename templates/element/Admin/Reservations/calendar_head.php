<div class="schedule-header-wrap js_search_header">
    <aside class="schedule-header showWrap">
        <?php if ($showPager): ?>
            <div class="is-top clearfix">
                <div class="select-day f-l mgb-10">
                    <div class="search-parts-left f-l mgt-10">
                        <?php if (isset($currentDate)): ?>
                            <button type="button" class="current-day tooltip js_date_select" title="日付を変える" data-toggle
                                  value="<?= h($calendar->getDateFrom()) ?>">
                    <span class="icon"><svg class="icon-calendar">
                            <use xlink:href="#icon_calendar"></use>
                        </svg></span>
                    <?= h($currentDate) ?>
                    </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="is-top clearfix">
            <?php if ($showPager): ?>
                <div class="search-parts-left f-l">
                    <?= $this->element('Admin/Common/search/paginator') ?>
                </div>
            <?php else: ?>
                <div class="select-day f-l">
                    <?php if (!is_null($calendar->getDatePrevious())): ?>
                        <?= $this->Form->button('', [
                            'type' => 'button',
                            'id' => 'prev',
                            'class' => ['btn-calender', 'is-prev', 'js_change_date'],
                            'data-date' => $calendar->getDatePrevious()->format('Y/m/d'),
                        ]) ?>
                    <?php endif; ?>
                    <?php if (isset($currentDate)): ?>
                        <button type="button" class="current-day tooltip js_date_select" title="日付を変える" data-toggle
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
            <div class="f-r">
                <?= $this->Form->button('<span class="icon"><svg class="icon-calendar"><use xlink:href="#icon_refresh"/></svg></span>台帳更新', [
                    'type' => 'button',
                    'id' => 'prev',
                    'class' => ['cmn-btn', 'is-pink', 'js_reload_calendar'],
                    'escapeTitle' => false,
                ]) ?>
            </div>
            <div class="f-r select-block">
                <div class="f-r mgr-10">
                    <?= $this->Template->checkbox('display_all_time', [
                        'type' => 'checkbox',
                        'class' => ['cmn-check', 'js_display_all_time'],
                        'label' => [
                            'class' => ['cmn-check', 'btn-tool'],
                            'text' => '24時間表示',
                        ],
                    ]) ?>
                    <?= $this->Form->control('calendar_type', [
                        'type' => 'select',
                        'class' => ['select', 'js_calendar_type'],
                        'options' => $valueOptions['calendarType'],
                        'value' => $calendar->getCalendarType(),
                    ]) ?>
                </div>
            </div>
            <div class="f-r select-block">
                <p class="cmn-txt">台帳表示項目</p>
                <?= $this->Form->control('display_item', [
                    'type' => 'select',
                    'class' => ['select', 'js_display_item'],
                    'options' => $valueOptions['displayItem'],
                    'empty' => true,
                ]) ?>
            </div>
        </div>
        <?= $this->element('Admin/Reservations/calendar_color_chips', [
            'colorChips' => $calendar->getColorChips(),
        ]) ?>
    </aside>
    <?php if (isset($horizontalScroll) && $horizontalScroll): ?>
        <div class="fixedTable-option">
            <aside class="fixedTable-arrow">
                <?= $this->Form->button('', [
                    'type' => 'button',
                    'id' => 'left-button',
                    'class' => ['fixedTable-scroll-btn', 'is-prev', 'cmn-btn'],
                ]) ?>
                <?= $this->Form->button('', [
                    'type' => 'button',
                    'id' => 'right-button',
                    'class' => ['fixedTable-scroll-btn', 'is-next', 'cmn-btn'],
                ]) ?>
            </aside>
        </div>
    <?php endif; ?>
</div>
