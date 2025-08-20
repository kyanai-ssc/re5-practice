<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * PaymentStatus Entity
 *
 * @property int $id
 * @property int $type
 * @property string $name
 * @property int $sort_no
 * @property int $default_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Reservation[] $reservations
 */
class PaymentStatus extends AppEntity
{
    /**
     * タイプ：未決済
     */
    public const TYPE_PAYMENT_YET = 1;

    /**
     * タイプ：仮決済
     */
    public const TYPE_PAYMENT_TEMP = 2;

    /**
     * タイプ：決済済み
     */
    public const TYPE_PAYMENT_COMPLETE = 3;

    /**
     * タイプ：決済キャンセル
     */
    public const TYPE_PAYMENT_CANCEL = 4;

    /**
     * タイプ：未入金
     */
    public const TYPE_RECEIVE_YET = 5;

    /**
     * タイプ：入金確認中
     */
    public const TYPE_RECEIVE_CONFIRM = 6;

    /**
     * タイプ：入金済み
     */
    public const TYPE_RECEIVE_COMPLETE = 7;

    /**
     * タイプ：返金処理中
     */
    public const TYPE_REFUND_PROCESS = 8;

    /**
     * タイプ：返金済み
     */
    public const TYPE_REFUND_COMPLETE = 9;

    /**
     * タイプ：決済済み（変更あり）
     */
    public const TYPE_PAYMENT_COMPLETE_CHANGED = 10;

    public const DEFAULT_FLG_ON = 1;
    public const DEFAULT_FLG_OFF = 0;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'type' => false,
        'name' => true,
        'sort_no' => false,
        'default_flg' => false,
        'created' => false,
        'modified' => false,
        'reservations' => false,
    ];
}
