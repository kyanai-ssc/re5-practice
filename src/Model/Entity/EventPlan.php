<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventPlan Entity
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property int $usage_time
 * @property int $usage_day
 * @property int $charge
 * @property int $public_flg
 * @property int $sort_no
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event $event
 * @property \App\Model\Entity\ReservationEventPlan[] $reservation_event_plans
 */
class EventPlan extends AppEntity
{
    public const PUBLIC_FLG_ON = 1;
    public const PUBLIC_FLG_OFF = 0;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_id' => false,
        'name' => true,
        'usage_time' => true,
        'usage_day' => true,
        'charge' => true,
        'public_flg' => true,
        'sort_no' => false,
        'created' => false,
        'modified' => false,
        'event' => false,
        'reservation_event_plans' => false,
    ];
}
