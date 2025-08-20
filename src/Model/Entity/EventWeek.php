<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventWeek Entity
 *
 * @property int $id
 * @property int $event_id
 * @property int $week
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event $event
 */
class EventWeek extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_id' => false,
        'week' => true,
        'created' => false,
        'modified' => false,
        'event' => false,
    ];
}
