<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * AutoReplyMailStatus Entity
 *
 * @property int $id
 * @property int $auto_reply_mail_id
 * @property int|null $reservation_status_from_id
 * @property int|null $reservation_status_to_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\AutoReplyMail $auto_reply_mail
 */
class AutoReplyMailStatus extends AppEntity
{
    public const STATUS_EMPTY = 0;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'auto_reply_mail_id' => true,
        'reservation_status_from_id' => true,
        'reservation_status_to_id' => true,
        'created' => false,
        'modified' => false,
        'auto_reply_mail' => false,
        'type' => true,
    ];

    protected $_virtual = ['type'];
}
