<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * BounceMail Entity
 *
 * @property int $id
 * @property string $mail
 * @property int $total_number
 * @property int $remaining_number
 * @property int $send_exclude_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\BounceMailHistory[] $bounce_mail_histories
 */
class BounceMail extends AppEntity
{
    /**
     * 配信不可フラグ：オン
     */
    public const SEND_EXCLUDE_FLG_ON = 1;

    /**
     * 配信不可フラグ：オフ
     */
    public const SEND_EXCLUDE_FLG_OFF = 0;

    public const REMAIN_NUMBER = 3;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'mail' => true,
        'total_number' => true,
        'remaining_number' => true,
        'send_exclude_flg' => true,
        'created' => false,
        'modified' => false,
        'bounce_mail_histories' => true,
    ];
}
