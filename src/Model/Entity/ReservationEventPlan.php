<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * ReservationEventPlan Entity
 *
 * @property int $id
 * @property int $reservation_id
 * @property int $event_plan_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Reservation $reservation
 * @property \App\Model\Entity\EventPlan $event_plan
 */
class ReservationEventPlan extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'reservation_id' => true,
        'event_plan_id' => true,
        'created' => false,
        'modified' => false,
        'reservation' => false,
        'event_plan' => false,
    ];
}
