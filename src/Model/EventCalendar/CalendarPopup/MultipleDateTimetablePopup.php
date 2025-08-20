<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\CalendarPopup;

use App\Model\Entity\Event;
use App\Model\EventCalendar\AbstractCalendarPopup;
use App\Model\EventCalendar\EventTimetable;
use App\Model\EventCalendar\EventUnit;
use App\Model\EventCalendar\Traits\CalendarPeriodTrait;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;

/**
 * MultipleDateTimetablePopup class.
 */
class MultipleDateTimetablePopup extends AbstractCalendarPopup
{
    use CalendarPeriodTrait;

    public const CALENDAR_PERIOD = 1;

    /**
     * @var \Cake\I18n\FrozenDate|null
     */
    protected $dateFrom = null;

    /**
     * @var \Cake\I18n\FrozenDate|null
     */
    protected $dateTo = null;

    /**
     * @var \Cake\I18n\FrozenDate|null
     */
    protected $datePrevious = null;

    /**
     * @var \Cake\I18n\FrozenDate|null
     */
    protected $dateNext = null;

    /**
     * @var \App\Model\EventCalendar\EventTimetable
     */
    protected $timetable = null;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->calendarPeriod = static::CALENDAR_PERIOD;
    }

    /**
     * @inheritDoc
     */
    public function setDate($date)
    {
        parent::setDate($date);

        $this->setDatePeriod();
    }

    /**
     * @inheritDoc
     */
    public function createTimetable()
    {
        if (!isset($this->event) || !isset($this->dateFrom) || !isset($this->dateTo)) {
            throw new CakeException();
        }

        $dateTimeFrom = new FrozenTime($this->dateFrom->format('Y-m-d'));
        $dateTimeTo = new FrozenTime($this->dateTo->format('Y-m-d'));
        $dateTimeTo = $dateTimeTo->addDays(1);

        // 祝日取得
        $publicHolidays = $this->getPublicHolidays($this->getDateFrom(), $this->getDateTo());
        $this->event->setPublicHolidays($publicHolidays);

        // タイムテーブルを生成
        $eventTimetable = new EventTimetable($this->event, $dateTimeFrom, $dateTimeTo, $this->isAdmin());
        $eventTimetable->setLimitDisplayTime($this->limitDisplayTime);
        $eventTimetable->setLimitDisplayable(true);
        if ((string)$this->event->get('type') === ((string)Event::TYPE_DAY)) {
            $eventTimetable->setExcludeOverday(true);
        }

        // 在庫計算
        $this->applyReservations([$eventTimetable], $dateTimeFrom, $dateTimeTo);

        // 予約データ設定
        $this->applyAdminCalendarData([$eventTimetable], $dateTimeFrom, $dateTimeTo);

        $this->timetable = $eventTimetable;
    }

    /**
     * @inheritDoc
     */
    protected function getColorChipIds(): array
    {
        $colorChipIds = [];
        foreach ($this->timetable->getTimetable() as $eventUnit) {
            $colorChip = $eventUnit->getColorChip();
            $colorChipIds[$colorChip['id']] = $colorChip['id'];
        }

        return $colorChipIds;
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
     * タイムテーブルを取得
     *
     * @return \App\Model\EventCalendar\EventTimetable
     */
    public function getTimetable()
    {
        return $this->timetable;
    }

    /**
     * 枠のクラスを取得
     *
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @param bool $selectCalendar カレンダー選択
     * @return array
     */
    public function getUnitHtmlClass(EventUnit $eventUnit, bool $selectCalendar = false)
    {
        $class = $this->getCommonUnitHtmlClass($eventUnit, $selectCalendar);
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
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @return array
     */
    public function createCss(EventUnit $eventUnit)
    {
        $colorTip = $eventUnit->getColorChip();
        $css = [
            'background-color' => $colorTip['color_code'],
        ];

        return $css;
    }

    /**
     * ポップアップの検索データを取得
     *
     * @return array
     */
    public function getPopupSearchData()
    {
        if (!isset($this->event) || !isset($this->date)) {
            throw new CakeException();
        }

        $searchData = [
            'id' => $this->event->get('id'),
            'date' => $this->date->format('Y/m/d'),
        ];

        return $searchData;
    }
}
