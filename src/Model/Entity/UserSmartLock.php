<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * UserSmartLock Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $smart_lock_user_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class UserSmartLock extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'user_id' => true,
        'smart_lock_user_id' => true,
    ];
}
