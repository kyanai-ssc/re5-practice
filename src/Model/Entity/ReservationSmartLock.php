<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * ReservationSmartLock Entity
 *
 * @property int $id
 * @property int $reservation_id
 * @property string|null $smart_lock_user_id
 * @property string|null $smart_lock_grant_id
 * @property string|null $smart_lock_pin
 * @property string|null $smart_lock_key_url
 * @property string|null $smart_lock_key_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Reservation $reservation
 */
class ReservationSmartLock extends AppEntity
{
    /**
     * 検索項目：未連携
     */
    public const DISPLAY_STATUS_UNLINKED = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'reservation_id' => true,
        'smart_lock_user_id' => true,
        'smart_lock_grant_id' => true,
        'smart_lock_pin' => true,
        'smart_lock_key_url' => true,
        'smart_lock_key_id' => true,
        'created' => false,
        'modified' => false,
        'reservation' => false,
    ];
}
