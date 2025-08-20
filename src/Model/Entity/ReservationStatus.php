<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * ReservationStatus Entity
 *
 * @property int $id
 * @property string $name
 * @property string $default_name
 * @property int $sort_no
 * @property int $status_type
 * @property int $keep_stock_flg
 * @property int $search_display_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\Reservation[] $reservations
 */
class ReservationStatus extends AppEntity
{
    /**
     * 予約ステータスタイプ：確定
     */
    public const STATUS_TYPE_FIXED = 1;

    /**
     * 予約ステータスタイプ：仮予約
     */
    public const STATUS_TYPE_TENTATIVE = 2;

    /**
     * 予約ステータスタイプ：来訪済み
     */
    public const STATUS_TYPE_VISIT = 3;

    /**
     * 予約ステータスタイプ：キャンセル
     */
    public const STATUS_TYPE_CANCEL = 4;

    /**
     * 予約ステータスタイプ：欠席
     */
    public const STATUS_TYPE_ABSENCE = 5;

    public const KEEP_STOCK_FLG_OFF = 0;
    public const KEEP_STOCK_FLG_ON = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'name' => true,
        'default_name' => false,
        'sort_no' => true,
        'status_type' => false,
        'keep_stock_flg' => false,
        'search_display_flg' => true,
        'created' => false,
        'modified' => false,
        'events' => false,
        'reservations' => false,
    ];
}
