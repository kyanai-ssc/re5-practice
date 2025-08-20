<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\Traits;

use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenDate;
use DateInterval;
use DatePeriod;
use Traversable;

/**
 * CalendarPeriod trait.
 */
trait CalendarPeriodTrait
{
    /**
     * @var int|null
     */
    protected $calendarPeriod = null;

    /**
     * 表示日数を取得
     *
     * @return int
     */
    public function getCalendarPeriod()
    {
        if (!isset($this->calendarPeriod)) {
            throw new CakeException();
        }

        return $this->calendarPeriod;
    }

    /**
     * 表示日を取得
     *
     * @return \Traversable
     */
    public function getCalendarDate(): Traversable
    {
        if (!isset($this->calendarPeriod) || !isset($this->dateFrom)) {
            throw new CakeException();
        }

        $dateTo = new FrozenDate($this->dateFrom->format('Y-m-d'));
        $dateTo = $dateTo->addDays($this->calendarPeriod);

        return new DatePeriod($this->dateFrom, new DateInterval('P1D'), $dateTo);
    }

    /**
     * カレンダーの期間を設定
     *
     * @return void
     */
    protected function setDatePeriod()
    {
        if (!isset($this->calendarPeriod)) {
            throw new CakeException();
        }
        if (!isset($this->date)) {
            $this->dateFrom = null;
            $this->dateTo = null;
            $this->datePrevious = null;
            $this->dateNext = null;

            return;
        }

        $dateFrom = $this->date;
        $dateTo = clone $dateFrom;
        $dateTo = $dateTo->addDays($this->calendarPeriod - 1);
        $datePrevious = clone $dateFrom;
        $datePrevious = $datePrevious->subDays($this->calendarPeriod);
        $dateNext = clone $dateTo;
        $dateNext = $dateNext->addDays(1);

        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->datePrevious = $datePrevious;
        $this->dateNext = $dateNext;
    }
}
