<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * AdminPassResetToken Entity
 *
 * @property int $id
 * @property int $admin_id
 * @property string $token
 * @property \Cake\I18n\FrozenTime $expiration_timestamp
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Admin $admin
 */
class AdminPassResetToken extends AppEntity
{
    /**
     * 有効期限；追加時間
     */
    public const EXPIRATION_ADD_HOUR = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'admin_id' => false,
        'token' => false,
        'expiration_timestamp' => false,
        'created' => false,
        'modified' => false,
        'admin' => false,
        'mail' => true,
    ];

    protected $_virtual = [
        'mail',
    ];
}
