<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventImage Entity
 *
 * @property int $id
 * @property int $event_id
 * @property string $url
 * @property int $sort_no
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event $event
 */
class EventImage extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_id' => false,
        'url' => true,
        'sort_no' => false,
        'created' => false,
        'modified' => false,
        'event' => true,
    ];
}
