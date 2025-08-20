<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventHolidayExcludeDate Entity
 *
 * @property int $id
 * @property int $event_holiday_id
 * @property \Cake\I18n\FrozenDate $date
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\EventHoliday $event_holiday
 */
class EventHolidayExcludeDate extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_holiday_id' => false,
        'date' => true,
        'created' => false,
        'modified' => false,
        'event_holiday' => false,
    ];

    /**
     * 日付を文字列に
     *
     * @param mixed $date 値
     * @return string YYYY-MM-DD HH:mm
     */
    protected function _getDate($date)
    {
        return $this->formatDateToString($date);
    }
}
