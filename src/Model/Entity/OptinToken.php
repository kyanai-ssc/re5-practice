<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * OptinToken Entity
 *
 * @property int $id
 * @property string $mail
 * @property string $token
 * @property \Cake\I18n\FrozenTime $expiration_timestamp
 * @property int|null $user_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class OptinToken extends AppEntity
{
    public const EXPIRATION_ADD_HOUR = 1;

    public const TYPE_USER = 1;
    public const TYPE_RESERVATION = 2;

    /**
     * タイプ：メールアドレス変更
     */
    public const TYPE_MAIL_EDIT = 3;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'mail' => true,
        'token' => false,
        'expiration_timestamp' => false,
        'user_id' => false,
        'created' => false,
        'modified' => false,
        'type' => false,
    ];
    protected $_virtual = [
        'type',
    ];
}
