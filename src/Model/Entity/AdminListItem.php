<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\ArrayUtility;

/**
 * AdminListItem Entity
 *
 * @property int $id
 * @property int $admin_id
 * @property int $type
 * @property string|null $items
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Admin $admin
 */
class AdminListItem extends AppEntity
{
    public const TYPE_USER_LIST = 1;
    public const TYPE_RESERVATION_LIST = 2;
    public const TYPE_MAIL_DELIVERIES = 3;

    public const ITEM_USER_ID = -1;
    public const ITEM_GUEST_FLG = -2;
    public const ITEM_WITHDRAWAL_FLG = -3;
    public const ITEM_USER_INS_TIMESTAMP = -4;
    public const ITEM_RESERVATION_ID = -5;
    public const ITEM_RESERVATION_STATUS_ID = -6;
    public const ITEM_USAGE_TIMESTAMP = -7;
    public const ITEM_EVENT_PLANS = -8;
    public const ITEM_CHARGE = -9;
    public const ITEM_PAYMENT_METHOD = -10;
    public const ITEM_PAYMENT_STATUS = -11;
    public const ITEM_RESERVATION_INS_TIMESTAMP = -12;
    public const ITEM_RECEPTION_STATUS_ID = -13;
    public const ITEM_RESERVATION_PAYMENT_STATUS = -14;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'admin_id' => false,
        'type' => false,
        'items' => true,
        'created' => false,
        'modified' => false,
        'admin' => false,
    ];

    /**
     * 項目のミューテーター
     *
     * @param array|null $items 項目
     * @return array|null
     */
    protected function _setItems($items)
    {
        if (!is_array($items)) {
            return $items;
        }

        return ArrayUtility::arrayMapRecursive(function ($value) {
            return (int)$value;
        }, $items);
    }
}
