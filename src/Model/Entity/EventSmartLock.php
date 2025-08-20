<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventSmartLock Entity
 *
 * @property int $id
 * @property int|null $event_id
 * @property string|null $smart_lock_device_key
 * @property int|null $smart_lock_key_url_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event $event
 */
class EventSmartLock extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_id' => true,
        'smart_lock_device_key' => true,
        'smart_lock_key_url_flg' => true,
        'created' => false,
        'modified' => false,
        'event' => false,
    ];
}
