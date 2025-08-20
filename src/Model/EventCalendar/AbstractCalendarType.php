<?php
declare(strict_types=1);

namespace App\Model\EventCalendar;

use App\Controller\Component\PaginationComponent;
use App\Model\Entity\Event;
use App\Model\EventCalendar\Traits\CalendarDetailTrait;
use App\Model\EventCalendar\Traits\TimetableTrait;
use App\Utility\CommonData\CommonDataTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;

/**
 * CalendarType abstract class.
 */
abstract class AbstractCalendarType
{
    use CalendarDetailTrait;
    use CommonDataTrait;
    use LocatorAwareTrait;
    use TimetableTrait;

    /**
     * @var int
     */
    protected $calendarType = null;

    /**
     * @var string
     */
    protected $calendarTypeName = null;

    /**
     * @var array|\Traversable
     */
    protected $events = null;

    /**
     * @var mixed|null
     */
    protected $dateFrom = null;

    /**
     * @var mixed|null
     */
    protected $dateTo = null;

    /**
     * @var mixed|null
     */
    protected $datePrevious = null;

    /**
     * @var mixed|null
     */
    protected $dateNext = null;

    /**
     * @var \Cake\I18n\FrozenDate|null
     */
    protected $date = null;

    /**
     * @var \App\Controller\Component\PaginationComponent|null
     */
    protected $paginator = null;

    /**
     * @var array|null
     */
    protected $searchData = null;

    /**
     * @var array|null
     */
    protected $eventValueOptions = null;

    /**
     * Constructor.
     *
     * @param int $calendarType カレンダータイプ
     * @param bool $adminFlg 管理者側フラグ
     */
    public function __construct(int $calendarType, bool $adminFlg = false)
    {
        $className = namespaceSplit(static::class);

        $this->calendarType = $calendarType;
        $this->calendarTypeName = $className[1];
        $this->adminFlg = $adminFlg;
        $this->events = [];
        $this->limitDisplayTime = false;

        $this->initialize();
    }

    /**
     * 初期化処理
     *
     * @return void
     */
    protected function initialize()
    {
    }

    /**
     * カレンダータイプを取得
     *
     * @return int
     */
    public function getCalendarType()
    {
        return $this->calendarType;
    }

    /**
     * カレンダータイプ名を取得
     *
     * @return string
     */
    public function getCalendarTypeName()
    {
        return $this->calendarTypeName;
    }

    /**
     * 予約枠を取得
     *
     * @return array|\Traversable
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * 予約枠を設定
     *
     * @param array|\Traversable $events 予約枠
     * @return void
     */
    public function setEvents($events)
    {
        $this->events = $events;
    }

    /**
     * 開始日を取得
     *
     * @return \Cake\I18n\FrozenDate|null
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * 終了日を取得
     *
     * @return \Cake\I18n\FrozenDate|null
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * 前の日付を取得
     *
     * @return \Cake\I18n\FrozenDate|null
     */
    public function getDatePrevious()
    {
        return $this->datePrevious;
    }

    /**
     * 次の日付を取得
     *
     * @return \Cake\I18n\FrozenDate|null
     */
    public function getDateNext()
    {
        return $this->dateNext;
    }

    /**
     * 日付を取得
     *
     * @return \Cake\I18n\FrozenDate|null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * 日付を設定
     *
     * @param string|\DateTimeInterface|null $date 日付
     * @return void
     */
    public function setDate($date)
    {
        $date = DateTimeUtility::convertToDateObject($date);

        $this->date = $date;
        $this->setDatePeriod();
    }

    /**
     * ページネーションを設定
     *
     * @param \App\Controller\Component\PaginationComponent $paginator ページネーション
     * @return void
     */
    public function setPaginator(PaginationComponent $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * 検索データを設定
     *
     * @param array $searchData 検索データ
     * @return void
     */
    public function setSearchData(array $searchData)
    {
        $this->searchData = $searchData;
    }

    /**
     * 予約枠を検索
     *
     * @return void
     */
    public function searchEvents()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        $defaultDate = null;
        if (is_null($this->getDate())) {
            if ($this->isAdmin()) {
                $this->setDate($this->commonData()->getNowDateTime()->format('Y-m-d'));
            } else {
                $defaultDate = $siteSettingsTable->getData()->getCalendarDefaultDate();
                $this->setDate($defaultDate);
            }
        }
        $events = $this->executeSearchEventsQuery([$this, 'buildEventQuery']);

        if (isset($defaultDate) && $events->count() === 0) {
            $this->setDate(null);
            $this->dateFrom = new FrozenDate($defaultDate->format('Y-m-d'));
            $result = $this->executeSearchEventsQuery(function ($query) {
                $query->order([
                    'Events.date_from' => 'ASC',
                    'Events.id' => 'ASC',
                ], true);
                $query->limit(1);

                return $query;
            });

            if ($result->count() > 0) {
                $this->setDate($result->first()->get('date_from'));
                $events = $this->executeSearchEventsQuery([$this, 'buildEventQuery']);
            } else {
                $this->setDate($defaultDate);
            }
        }

        $this->setEvents($events);
    }

    /**
     * テンプレートのパスを取得
     *
     * @return string
     */
    public function getTemplatePath()
    {
        $prefix = null;
        if ($this->isAdmin()) {
            $prefix = 'Admin';
        } else {
            $prefix = 'User';
        }

        return $prefix . '/Reservations/CalendarType/' . $this->getCalendarTypeName() . '/calendar';
    }

    /**
     * カレンダーの期間を設定
     *
     * @return void
     */
    protected function setDatePeriod()
    {
    }

    /**
     * 予約枠のクエリを実行
     *
     * @param callable|null $calendarQueryBuilder クエリビルダー
     * @return \Cake\Datasource\ResultSetInterface
     */
    protected function executeSearchEventsQuery($calendarQueryBuilder = null)
    {
        $eventsTable = $this->getTableLocator()->get('Events');

        $result = null;
        if ($this instanceof PaginateTypeInterface) {
            $paginate = [$this->paginator, 'paginate'];
            if (!is_callable($paginate)) {
                throw new CakeException();
            }

            $result = call_user_func($paginate, $eventsTable, [
                'finder' => [
                    'calendar' => [
                        'inputs' => [
                            'calendar_type' => $this->getCalendarType(),
                            'date_from' => $this->getDateFrom(),
                            'date_to' => $this->getDateTo(),
                        ] + (array)$this->searchData,
                        'calendarQueryBuilder' => $calendarQueryBuilder,
                    ],
                ],
                'maxLimit' => $this->paginateMaxLimit(),
            ]);
        } else {
            $query = $eventsTable->find('calendar', [
                'inputs' => [
                    'calendar_type' => $this->getCalendarType(),
                    'date_from' => $this->getDateFrom(),
                    'date_to' => $this->getDateTo(),
                ] + (array)$this->searchData,
                'calendarQueryBuilder' => $calendarQueryBuilder,
            ]);
            $result = $query->all();
        }

        return $result;
    }

    /**
     * 予約枠検索のクエリを生成
     *
     * @param \Cake\ORM\Query $query クエリ
     * @return \Cake\ORM\Query
     */
    abstract public function buildEventQuery(Query $query);

    /**
     * カレンダーを生成
     *
     * @return void
     */
    abstract public function createCalendar();

    /**
     * タイムテーブルの有無を判定
     *
     * @return bool
     */
    abstract public function hasTimetable();

    /**
     * クエリで予約枠IDが指定された際に表示可能な予約枠を取得
     *
     * @param string $id 予約枠ID
     * @return string|void
     */
    public function getDisplayEventForQueryId($id)
    {
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');

        $result = $eventsTable->find('calendar', [
            'inputs' => [
                'id' => $id,
            ],
        ])->first();

        if ($result instanceof Event) {
            return $result->get('name');
        }

        return '';
    }
}
