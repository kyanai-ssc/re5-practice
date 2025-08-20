<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * WaitingCancellation Entity
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $event_id
 * @property \Cake\I18n\FrozenTime $usage_timestamp
 * @property string|null $mail
 * @property int $notify_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Event $event
 * @property \App\Model\Entity\AutoReplyMailHistory[] $auto_reply_mail_histories
 */
class WaitingCancellation extends AppEntity
{
    /**
     * 通知フラグ：OFF
     */
    public const NOTIFY_FLG_OFF = 0;

    /**
     * 通知フラグ：ON
     */
    public const NOTIFY_FLG_ON = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'user_id' => true,
        'event_id' => true,
        'usage_timestamp' => true,
        'mail' => true,
        'notify_flg' => false,
        'created' => false,
        'modified' => false,
        'user' => false,
        'event' => false,
        'auto_reply_mail_histories' => false,
    ];
}
