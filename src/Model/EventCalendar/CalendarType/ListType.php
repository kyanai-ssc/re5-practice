<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\CalendarType;

use App\Model\Entity\Event;
use App\Model\EventCalendar\AbstractCalendarType;
use App\Model\EventCalendar\EventTimetable;
use App\Model\EventCalendar\PaginateTypeInterface;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\Paging\NumericPaginator;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * ListType class.
 */
class ListType extends AbstractCalendarType implements PaginateTypeInterface
{
    public const PAGINATE_LIMIT = 15;
    public const CALENDAR_PERIOD = 30;

    /**
     * @var array
     */
    protected $timetable = null;

    /**
     * @var array|null
     */
    protected $isListDisplay = null;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->isListDisplay = [];
    }

    /**
     * @inheritDoc
     */
    public function setDate($date)
    {
        if (!$this->isAdmin()) {
            $date = $this->commonData()->getNowDateTime()->format('Y-m-d');
        }

        parent::setDate($date);
    }

    /**
     * @inheritDoc
     */
    public function buildEventQuery(Query $query)
    {
        $query->order([
            'Events.sort_no' => 'ASC',
            'Events.date_from' => 'ASC',
            'Events.date_to' => 'ASC',
            'Events.time_from' => 'ASC',
            'Events.time_to' => 'ASC',
            'Events.id' => 'ASC',
        ], true);

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function createCalendar()
    {
        if (!isset($this->dateFrom)) {
            throw new CakeException();
        }
        $paginator = $this->paginator;
        if (!isset($paginator)) {
            throw new CakeException();
        }

        // 祝日取得
        $publicHolidays = $this->getPublicHolidays($this->getDateFrom(), $this->getDateTo());

        // タイムテーブルを生成
        $events = [];
        $timetable = [];

        // 予約枠取得
        $request = $paginator->getController()->getRequest();
        $params = $request->getQueryParams();
        $page = (int)$params['page'];
        $pageCount = $this->getPageCount();
        while (true) {
            // 表示可能予約枠が15件以上 または 最大ページ数を超えた場合は処理を抜ける
            if (static::PAGINATE_LIMIT <= count($events) || $pageCount < $page) {
                break;
            }

            $params['page'] = $page;
            $paginator->getController()->setRequest($request->withQueryParams($params));
            $this->searchEvents();

            foreach ($this->getEvents() as $event) {
                $event->setPublicHolidays($publicHolidays);

                $dateTimeFrom = new FrozenTime($this->dateFrom->format('Y-m-d'));
                if ($dateTimeFrom < $event->getDateTimeFrom()) {
                    $dateTimeFrom = new FrozenTime($event->getDateTimeFrom()->format('Y-m-d'));
                }
                $dateTimeTo = clone $dateTimeFrom;
                $dateTimeTo = $dateTimeTo->addDays(static::CALENDAR_PERIOD);

                $eventTimetable = new EventTimetable($event, $dateTimeFrom, $dateTimeTo, $this->isAdmin());
                $eventTimetable->setLimitDisplayTime($this->limitDisplayTime);
                $eventTimetable->setLimitDisplayable(true);
                if ($eventTimetable->hasTimetable()) {
                    $events[$event->get('id')] = $event;
                    $timetable[$event->get('id')] = $eventTimetable;
                }
            }
            $page++;
        }

        // 在庫計算
        $checkStockTimetable = [];
        foreach ($timetable as $eventTimetable) {
            if (!$this->isListDisplay($eventTimetable)) {
                $checkStockTimetable[] = $eventTimetable;
            }
        }
        $this->applyReservations($checkStockTimetable);

        // 予約データ設定
        $this->applyAdminCalendarData($checkStockTimetable);

        $this->events = $events;
        $this->timetable = $timetable;
    }

    /**
     * @inheritDoc
     */
    public function hasTimetable()
    {
        if (!empty($this->timetable)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function paginateValueOptions()
    {
        $valueOptions = [
            'sort' => [],
            'direction' => [],
            'limit' => [static::PAGINATE_LIMIT => static::PAGINATE_LIMIT],
        ];

        return $valueOptions;
    }

    /**
     * @inheritDoc
     */
    public function paginateDefaultValues()
    {
        $defaultValues = [
            'sort' => null,
            'direction' => null,
            'limit' => static::PAGINATE_LIMIT,
            'page' => 1,
        ];

        return $defaultValues;
    }

    /**
     * @inheritDoc
     */
    public function paginateMaxLimit()
    {
        return static::PAGINATE_LIMIT;
    }

    /**
     * @inheritDoc
     */
    protected function setDatePeriod()
    {
        if (!isset($this->date)) {
            $this->dateFrom = null;

            return;
        }

        $this->dateFrom = clone $this->date;
    }

    /**
     * @inheritDoc
     */
    protected function getColorChipIds(): array
    {
        $colorChipIds = [];
        foreach ($this->timetable as $eventTimetable) {
            $eventUnit = $eventTimetable->getFirstUnit();
            if (!isset($eventUnit)) {
                throw new CakeException();
            }

            $colorChip = $eventUnit->getColorChip();
            $colorChipIds[$colorChip['id']] = $colorChip['id'];
        }

        return $colorChipIds;
    }

    /**
     * タイムテーブルを取得
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @return \App\Model\EventCalendar\EventTimetable
     */
    public function getTimetable(Event $event)
    {
        return Hash::get($this->timetable, $event->get('id'));
    }

    /**
     * 一覧表示の判定
     *
     * @param \App\Model\EventCalendar\EventTimetable $eventTimetable タイムテーブル
     * @return bool
     */
    public function isListDisplay(EventTimetable $eventTimetable)
    {
        $event = $eventTimetable->getEvent();
        if (!isset($this->isListDisplay[$event->get('id')])) {
            $this->isListDisplay[$event->get('id')] = !$event->isSingleUnit();
        }

        return $this->isListDisplay[$event->get('id')];
    }

    /**
     * タイムテーブルのクラスを取得
     *
     * @param \App\Model\EventCalendar\EventTimetable $eventTimetable タイムテーブル
     * @param bool $selectCalendar カレンダー選択
     * @return array
     */
    public function getTimetableHtmlClass(EventTimetable $eventTimetable, bool $selectCalendar = false)
    {
        $class = $this->getCommonUnitHtmlClass($eventTimetable->getFirstUnit(), $selectCalendar);
        if (!$selectCalendar) {
            if ($this->isAdmin()) {
                $class[] = 'js_show_calendar_detail';
            } else {
                $class[] = 'js_make_reservation';
            }
        }

        return $class;
    }

    /**
     * CSSを生成
     *
     * @param \App\Model\EventCalendar\EventTimetable $eventTimetable タイムテーブル
     * @return array
     */
    public function createCss(EventTimetable $eventTimetable)
    {
        $eventUnit = $eventTimetable->getFirstUnit();
        if (!isset($eventUnit)) {
            throw new CakeException();
        }

        if (
            $this->isListDisplay($eventTimetable)
            && $eventTimetable->getEvent()->get('background_color_type') === Event::BACKGROUND_COLOR_TYPE_DEFAULT
        ) {
            $css = [];
        } else {
            $colorTip = $eventUnit->getColorChip();
            $css = [
                'background-color' => $colorTip['color_code'],
            ];
        }

        return $css;
    }

    /**
     * タイムテーブルポップアップの検索データを取得
     *
     * @param \App\Model\EventCalendar\EventTimetable $eventTimetable タイムテーブル
     * @return array
     */
    public function getTimetablePopupSearchData(EventTimetable $eventTimetable)
    {
        if (!isset($this->date)) {
            throw new CakeException();
        }

        $date = new FrozenTime($this->date->format('Y-m-d'));
        if (!$this->isAdmin()) {
            $firstReceptionDateTime = $eventTimetable->getEvent()->getFirstReceptionDateTime();
            if ($date < $firstReceptionDateTime) {
                $date = $firstReceptionDateTime;
            }
        }

        $searchData = [
            'id' => $eventTimetable->getEvent()->get('id'),
            'date' => $date->format('Y/m/d'),
        ];
        if ($this->isAdmin() && !$this->limitDisplayTime) {
            $searchData['display_all_time'] = Configure::readOrFail('Master.common.flg.on');
        }
        if ($this->isAdmin() && isset($this->displayItem)) {
            $searchData['display_item'] = $this->displayItem;
        }

        return $searchData;
    }

    /**
     * 最大ページ数を取得
     *
     * @return int
     */
    public function getPageCount()
    {
        $paginator = new NumericPaginator();

        $paginator->paginate($this->getTableLocator()->get('Events'), $this->paginateDefaultValues(), [
            'finder' => [
                'calendar' => [
                    'inputs' => [
                        'calendar_type' => $this->getCalendarType(),
                        'date_from' => $this->getDateFrom(),
                        'date_to' => $this->getDateTo(),
                    ] + (array)$this->searchData,
                    'calendarQueryBuilder' => [$this, 'buildEventQuery'],
                ],
            ],
            'maxLimit' => $this->paginateMaxLimit(),
        ]);

        $pagingData = $paginator->getPagingParams();

        return $pagingData['Events']['pageCount'];
    }
}
