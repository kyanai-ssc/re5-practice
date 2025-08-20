<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * WaitingCancellationConfToken Entity
 *
 * @property int $id
 * @property string $mail
 * @property string $token
 * @property \Cake\I18n\FrozenTime $expiration_timestamp
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class WaitingCancellationConfToken extends AppEntity
{
    /**
     * 有効期限；追加時間
     */
    public const EXPIRATION_ADD_HOUR = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'mail' => true,
        'token' => false,
        'expiration_timestamp' => false,
        'created' => false,
        'modified' => false,
    ];
}
