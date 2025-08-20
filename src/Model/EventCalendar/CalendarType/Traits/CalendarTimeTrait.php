<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\CalendarType\Traits;

use App\Utility\DateTimeUtility;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;

/**
 * CalendarTime trait.
 */
trait CalendarTimeTrait
{
    /**
     * @var array|null
     */
    protected $calendarTime = null;

    /**
     * @var array|null
     */
    protected $calendarTimeRange = null;

    /**
     * カレンダーの表示時間帯を取得
     *
     * @return array
     */
    public function getCalendarTimeRange()
    {
        if (!isset($this->calendarTimeRange)) {
            /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
            $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

            $timeFrom = '00:00:00';
            $timeTo = '00:00:00';
            if ($this->limitDisplayTime) {
                $timeFrom = $siteSettingsTable->getData()->getCalendarTimeFromHour()->format('H:i:s');
                $timeTo = $siteSettingsTable->getData()->getCalendarTimeToHour()->format('H:i:s');
            }
            $calendarTimeFrom = new FrozenTime(
                $this->commonData()->getNowDateTime()->format('Y-m-d') . ' ' . $timeFrom
            );
            $calendarTimeTo = new FrozenTime($this->commonData()->getNowDateTime()->format('Y-m-d') . ' ' . $timeTo);
            if ($calendarTimeFrom >= $calendarTimeTo) {
                $calendarTimeTo = $calendarTimeTo->addDays(1);
            }

            $calendarTimeRange = [];
            $current = $calendarTimeFrom;
            $limit = $calendarTimeTo;
            while ($current < $limit) {
                $calendarTimeRange[] = clone $current;
                $current = $current->addHours(1);
            }

            $this->calendarTimeRange = $calendarTimeRange;
        }

        return $this->calendarTimeRange;
    }

    /**
     * スクロール対象時間の判定
     *
     * @param string|\DateTimeInterface $time 時間
     * @return bool
     */
    public function isScrollTime($time)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        $time = DateTimeUtility::convertToTimeObject($time);
        if (!isset($time)) {
            throw new CakeException();
        }

        $calendarTimeDefault = $siteSettingsTable->getData()->getCalendarTimeDefault();
        if (!isset($calendarTimeDefault)) {
            return false;
        }
        if ($time->format('G') !== $calendarTimeDefault->format('G')) {
            return false;
        }

        return true;
    }

    /**
     * 現在時刻の判定
     *
     * @param string|\DateTimeInterface $time 時間
     * @return bool
     */
    public function isCurrentTime($time)
    {
        $time = DateTimeUtility::convertToTimeObject($time);
        if (!isset($time)) {
            throw new CakeException();
        }

        if ($time->format('G') !== $this->commonData()->getNowDateTime()->format('G')) {
            return false;
        }

        return true;
    }

    /**
     * タイムテーブル内のカラーチップIDを取得
     *
     * @param array $timetable タイムテーブル
     * @return array
     */
    protected function getColorChipIdsByTimetable($timetable)
    {
        $colorChipIds = [];
        foreach ($timetable as $timetableByDate) {
            foreach ($timetableByDate as $eventTimetable) {
                foreach ($eventTimetable->getTimetable() as $eventUnit) {
                    $colorChip = $eventUnit->getColorChip();
                    $colorChipIds[$colorChip['id']] = $colorChip['id'];
                }
            }
        }

        return $colorChipIds;
    }

    /**
     * カレンダー表示時間内の枠の時間帯を取得
     *
     * @param \DateTimeInterface $date 日付
     * @param \DateTimeInterface $dateTimeFrom 枠の開始日時
     * @param \DateTimeInterface $dateTimeTo 枠の終了日時
     * @return array
     */
    protected function getDateTimeWithinCalendarTime($date, $dateTimeFrom, $dateTimeTo)
    {
        $calendarTime = $this->getCalendarTime($date->format('Y-m-d'));

        $from = $dateTimeFrom;
        if ($dateTimeFrom < $calendarTime['from']) {
            $from = $calendarTime['from'];
        }
        $to = $dateTimeTo;
        if ($dateTimeTo > $calendarTime['to']) {
            $to = $calendarTime['to'];
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * 枠の縦方向の位置を計算
     *
     * @param \Cake\I18n\FrozenTime $dateTimeFrom 開始日時
     * @return int
     */
    protected function calculateUnitTop($dateTimeFrom)
    {
        $calendarTime = $this->getCalendarTime($dateTimeFrom->format('Y-m-d'));
        $top = $calendarTime['from']->diffInMinutes($dateTimeFrom);

        return $top;
    }

    /**
     * 枠の高さを計算
     *
     * @param \Cake\I18n\FrozenTime $dateTimeFrom 開始日時
     * @param \Cake\I18n\FrozenTime $dateTimeTo 終了日時
     * @return int
     */
    protected function calculateUnitHeight($dateTimeFrom, $dateTimeTo)
    {
        $height = $dateTimeFrom->diffInMinutes($dateTimeTo);

        return $height;
    }

    /**
     * カレンダーの表示時間を取得
     *
     * @param string|\DateTimeInterface $date 日付
     * @return array
     */
    protected function getCalendarTime($date)
    {
        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        if (!isset($this->calendarTime)) {
            /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
            $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

            $calendarTime = [];
            foreach ($this->getCalendarDate() as $calendarDate) {
                $timeFrom = '00:00:00';
                $timeTo = '00:00:00';
                if ($this->limitDisplayTime) {
                    $timeFrom = $siteSettingsTable->getData()->getCalendarTimeFromHour()->format('H:i:s');
                    $timeTo = $siteSettingsTable->getData()->getCalendarTimeToHour()->format('H:i:s');
                }
                $calendarTimeFrom = new FrozenTime($calendarDate->format('Y-m-d') . ' ' . $timeFrom);
                $calendarTimeTo = new FrozenTime($calendarDate->format('Y-m-d') . ' ' . $timeTo);
                if ($calendarTimeFrom >= $calendarTimeTo) {
                    $calendarTimeTo = $calendarTimeTo->addDays(1);
                }

                $calendarTime[$calendarDate->format('Y-m-d')] = [
                    'from' => $calendarTimeFrom,
                    'to' => $calendarTimeTo,
                ];
            }

            $this->calendarTime = $calendarTime;
        }

        return Hash::get($this->calendarTime, $date->format('Y-m-d'));
    }

    /**
     * その日の予約枠がカレンダーの時間帯に重複するか判定
     *
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 予約枠
     * @param string|\DateTimeInterface $date 日付
     * @return bool
     */
    public function isOverlapCalendarTimeRange($eventUnit, $date): bool
    {
        $range = $this->getCalendarTime($date);

        return $eventUnit->getDateTimeFrom() < $range['to'] && $eventUnit->getDateTimeTo() > $range['from'];
    }
}
