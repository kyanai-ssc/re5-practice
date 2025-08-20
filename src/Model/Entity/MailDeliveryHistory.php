<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * MailDeliveryHistory Entity
 *
 * @property int $id
 * @property int $mail_delivery_id
 * @property int $user_id
 * @property string $mail
 * @property int $send_status
 * @property string|null $bounce_mail_token
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\MailDelivery $mail_delivery
 * @property \App\Model\Entity\User $user
 */
class MailDeliveryHistory extends AppEntity
{
    /**
     * 配信ステータス：未配信
     */
    public const SEND_STATUS_NOT_SEND = 1;

    /**
     * 配信ステータス：成功
     */
    public const SEND_STATUS_SUCCESS = 2;

    /**
     * 配信ステータス：失敗
     */
    public const SEND_STATUS_FAILED = 3;

    /**
     * 配信ステータス：不達による除外
     */
    public const SEND_STATUS_EXCLUDE = 4;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'mail_delivery_id' => false,
        'user_id' => false,
        'mail' => false,
        'send_status' => false,
        'bounce_mail_token' => false,
        'created' => false,
        'modified' => false,
        'mail_delivery' => false,
        'user' => false,
    ];
}
