<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventHolidayWeek Entity
 *
 * @property int $id
 * @property int $event_holiday_id
 * @property int $week
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\EventHoliday $event_holiday
 */
class EventHolidayWeek extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_holiday_id' => false,
        'week' => true,
        'created' => false,
        'modified' => false,
        'event_holiday' => true,
    ];
}
