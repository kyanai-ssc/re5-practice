<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventStockMark Entity
 *
 * @property int $id
 * @property int $event_id
 * @property int $number
 * @property int $symbolic
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event $event
 */
class EventStockMark extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_id' => false,
        'number' => true,
        'symbolic' => true,
        'created' => false,
        'modified' => false,
        'event' => false,
    ];
}
