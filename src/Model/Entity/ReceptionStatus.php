<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * ReceptionStatus Entity
 *
 * @property int $id
 * @property string $name
 * @property string $default_name
 * @property int $sort_no
 * @property int $status_type
 * @property int $search_display_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class ReceptionStatus extends AppEntity
{
    /**
     * 受付ステータスタイプ：入場中
     */
    public const STATUS_TYPE_ADMISSION = 1;

    /**
     * 受付ステータスタイプ：退場
     */
    public const STATUS_TYPE_EXIT = 2;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'name' => true,
        'default_name' => false,
        'sort_no' => true,
        'status_type' => false,
        'search_display_flg' => true,
        'created' => false,
        'modified' => false,
    ];
}
