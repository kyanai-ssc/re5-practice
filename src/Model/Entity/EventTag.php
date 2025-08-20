<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventTag Entity
 *
 * @property int $id
 * @property int $event_id
 * @property int $tag_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event $event
 * @property \App\Model\Entity\Tag $tag
 */
class EventTag extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_id' => false,
        'tag_id' => true,
        'created' => false,
        'modified' => false,
        'event' => false,
        'tag' => false,
    ];
}
