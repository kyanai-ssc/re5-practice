<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use Cake\Core\Configure;

/**
 * ColorChip Entity
 *
 * @property int $id
 * @property int $type
 * @property string $name
 * @property string $color_code
 * @property int $front_display_flg
 * @property int $sort_no
 * @property int $default_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event[] $events
 */
class ColorChip extends AppEntity
{
    /**
     * 公開非公開設定：ON
     */
    public const FRONT_DISPLAY_FLG_ON = 1;

    /**
     * 公開非公開設定：OFF
     */
    public const FRONT_DISPLAY_FLG_OFF = 0;

    /**
     * デフォルトフラグ:ON
     */
    public const DEFAULT_FLG_ON = 1;

    /**
     * デフォルトフラグ:OFF
     */
    public const DEFAULT_FLG_OFF = 0;

    /**
     * カラータイプ：期間外
     */
    public const TYPE_TERM_END = 1;

    /**
     * カラータイプ：あきなし
     */
    public const TYPE_FULL = 2;

    /**
     * カラータイプ：空きあり
     */
    public const TYPE_EMPTY = 3;

    /**
     * カラータイプ：予約済み
     */
    public const TYPE_RESERVED = 4;

    /**
     * カラータイプ：予約中
     */
    public const TYPE_CONTINUOUS = 5;

    /**
     * カラータイプ：追加
     */
    public const TYPE_ADD = 6;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'type' => true,
        'name' => true,
        'color_code' => true,
        'front_display_flg' => true,
        'sort_no' => true,
        'default_flg' => false,
        'created' => false,
        'modified' => false,
        'events' => false,
    ];

    /**
     * @return bool
     */
    public function canDelete()
    {
        if (!empty($this->get('events'))) {
            return false;
        }

        if ($this->get('default_flg') === Configure::read('Master.common.flg.on')) {
            return false;
        }

        return true;
    }
}
