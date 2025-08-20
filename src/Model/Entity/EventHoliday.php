<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventHoliday Entity
 *
 * @property int $id
 * @property int|null $event_id
 * @property \Cake\I18n\FrozenDate|null $date_from
 * @property \Cake\I18n\FrozenDate|null $date_to
 * @property \Cake\I18n\FrozenTime|null $time_from
 * @property \Cake\I18n\FrozenTime|null $time_to
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event $event
 * @property \App\Model\Entity\EventHolidayExcludeDate[] $event_holiday_exclude_dates
 * @property \App\Model\Entity\EventHolidayWeek[] $event_holiday_weeks
 */
class EventHoliday extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_id' => false,
        'date_from' => true,
        'date_to' => true,
        'time_from' => true,
        'time_to' => true,
        'created' => false,
        'modified' => false,
        'event' => false,
        'event_holiday_exclude_dates' => true,
        'event_holiday_weeks' => true,
    ];

    /**
     * 時間を24時表記に変更
     *
     * @param mixed $time 値
     * @return string 00:00～24:00の文字列
     */
    protected function _getTimeTo($time)
    {
        return $this->formatTime24($time, true);
    }

    /**
     * 時間を24時表記に変更
     *
     * @param mixed $time 値
     * @return string 00:00～24:00の文字列
     */
    protected function _getTimeFrom($time)
    {
        return $this->formatTime24($time);
    }
}
