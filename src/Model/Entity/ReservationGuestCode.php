<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * ReservationGuestCode Entity
 *
 * @property int $id
 * @property int $reservation_id
 * @property string $code
 * @property \Cake\I18n\FrozenTime $expiration_timestamp
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Reservation $reservation
 */
class ReservationGuestCode extends AppEntity
{
    /**
     * コード文字数
     */
    public const CODE_MAXLENGTH = 8;

    /**
     * 有効期限（h）
     */
    public const EXPIRATION_ADD_HOUR = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'reservation_id' => true,
        'code' => false,
        'expiration_timestamp' => false,
        'created' => false,
        'modified' => false,
        'reservation' => false,
    ];
}
