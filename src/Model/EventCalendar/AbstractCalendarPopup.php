<?php
declare(strict_types=1);

namespace App\Model\EventCalendar;

use App\Model\EventCalendar\Traits\CalendarDetailTrait;
use App\Model\EventCalendar\Traits\TimetableTrait;
use App\Utility\CommonData\CommonDataTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Exception\CakeException;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * CalendarPopup abstract class.
 */
abstract class AbstractCalendarPopup
{
    use CalendarDetailTrait;
    use CommonDataTrait;
    use LocatorAwareTrait;
    use TimetableTrait;

    public const TYPE_EVENT_LIST = 1;
    public const TYPE_SINGLE_DATE_TIMETABLE = 2;
    public const TYPE_MULTIPLE_DATE_TIMETABLE = 3;

    /**
     * @var int
     */
    protected $popupType = null;

    /**
     * @var string
     */
    protected $popupTypeName = null;

    /**
     * @var array
     */
    protected $events = null;

    /**
     * @var \App\Model\Entity\Event|null
     */
    protected $event = null;

    /**
     * @var \Cake\I18n\FrozenDate|null
     */
    protected $date = null;

    /**
     * @var bool
     */
    protected $isMultipleEventType = false;

    /**
     * @var array|null
     */
    protected $searchData = null;

    /**
     * Constructor.
     *
     * @param int $popupType ポップアップタイプ
     * @param bool $adminFlg 管理者側フラグ
     */
    public function __construct(int $popupType, bool $adminFlg = false)
    {
        $className = namespaceSplit(static::class);

        $this->popupType = $popupType;
        $this->popupTypeName = $className[1];
        $this->adminFlg = $adminFlg;
        $this->limitDisplayTime = false;
        $this->events = [];

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
     * ポップアップタイプを取得
     *
     * @return int
     */
    public function getPopupType()
    {
        return $this->popupType;
    }

    /**
     * ポップアップタイプ名を取得
     *
     * @return string
     */
    public function getPopupTypeName()
    {
        return $this->popupTypeName;
    }

    /**
     * 予約枠を取得
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * 予約枠を設定
     *
     * @param array $events 予約枠
     * @return void
     */
    public function setEvents($events)
    {
        if (!$this->isMultipleEventType) {
            foreach ($events as $event) {
                $this->event = $event;
                $this->events = [$event];
            }
        } else {
            $this->events = $events;
        }
    }

    /**
     * 単一の予約枠を取得
     *
     * @return \App\Model\Entity\Event|null
     */
    public function getEvent()
    {
        return $this->event;
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
     * @param string|\DateTimeInterface $date 日付
     * @return void
     */
    public function setDate($date)
    {
        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        $this->date = $date;
    }

    /**
     * 複数枠を取得するタイプの判定
     *
     * @return bool
     */
    public function isMultipleEventType()
    {
        return $this->isMultipleEventType;
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
        $eventsTable = $this->getTableLocator()->get('Events');

        if (!isset($this->searchData['id'])) {
            throw new CakeException();
        }

        $query = $eventsTable->find('calendarPopup', [
            'inputs' => [
                'id' => $this->searchData['id'],
            ],
        ]);
        $this->setEvents($query->toArray());
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

        return $prefix . '/Reservations/CalendarPopup/' . $this->getPopupTypeName() . '/popup';
    }

    /**
     * タイムテーブルを生成
     *
     * @return void
     */
    abstract public function createTimetable();
}
