<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * Inquiry Entity
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $phone_number
 * @property string|null $mail
 * @property string $contents
 * @property int $mail_send_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 */
class Inquiry extends AppEntity
{
    /**
     * メール送信フラグ：未送信
     */
    public const MAIL_SEND_FLG_OFF = 0;

    /**
     * メール送信フラグ：送信済み
     */
    public const MAIL_SEND_FLG_ON = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'user_id' => false,
        'name' => true,
        'phone_number' => true,
        'mail' => true,
        'contents' => true,
        'mail_send_flg' => false,
        'created' => false,
        'modified' => false,
        'user' => false,
        'mail_confirm' => true,
    ];

    protected $_virtual = [
        'mail_confirm',
    ];
}
