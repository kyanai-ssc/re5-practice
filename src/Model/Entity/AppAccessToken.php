<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * AppAccessToken Entity
 *
 * @property int $id
 * @property int|null $admin_id
 * @property string $token
 * @property \Cake\I18n\FrozenTime $expiration_timestamp
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class AppAccessToken extends AppEntity
{
    public const STATUS_TYPE_SUCCESS = 'STATUS_TYPE_SUCCESS';
    public const STATUS_TYPE_REQUIRED = 'STATUS_TYPE_REQUIRED';
    public const STATUS_TYPE_NOT_AUTHORIZED = 'STATUS_TYPE_NOT_AUTHORIZED';
    public const STATUS_TYPE_ACCESS_TOKEN_NOT_EXIST = 'STATUS_TYPE_ACCESS_TOKEN_NOT_EXIST';
    public const STATUS_TYPE_EXPRIOD_TOKEN = 'STATUS_TYPE_EXPRIOD_TOKEN';
    public const STATUS_TYPE_NOT_ACCEPTED = 'STATUS_TYPE_NOT_ACCEPTED';
    public const STATUS_TYPE_ABSENCE = 'STATUS_TYPE_ABSENCE';
    public const STATUS_TYPE_ACCOUNT_NOT_EXIST = 'STATUS_TYPE_ACCOUNT_NOT_EXIST';
    public const STATUS_TYPE_DATA_NOT_EXIST = 'STATUS_TYPE_DATA_NOT_EXIST';

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'admin_id' => true,
        'token' => true,
        'expiration_timestamp' => true,
    ];
}
