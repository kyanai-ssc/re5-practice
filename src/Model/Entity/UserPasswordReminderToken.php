<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * UserPasswordReminderToken Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property \Cake\I18n\FrozenTime $expiration_timestamp
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 */
class UserPasswordReminderToken extends AppEntity
{
    public const EXPIRATION_ADD_HOUR = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'user_id' => false,
        'token' => false,
        'expiration_timestamp' => false,
        'created' => false,
        'modified' => false,
        'user' => false,
        'mail' => true,
    ];

    protected $_virtual = ['mail'];
}
