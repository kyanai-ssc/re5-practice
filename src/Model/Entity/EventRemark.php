<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventRemark Entity
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property int|null $form_item_id
 * @property int $detail_display_flg
 * @property string $remark
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event $event
 * @property \App\Model\Entity\FormItem $form_item
 */
class EventRemark extends AppEntity
{
    /**
     * 詳細表示フラグ：オン
     */
    public const DETAIL_DISPLAY_FLG_ON = 1;

    /**
     * 詳細表示フラグ：オフ
     */
    public const DETAIL_DISPLAY_FLG_OFF = 0;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_id' => false,
        'name' => true,
        'form_item_id' => true,
        'detail_display_flg' => true,
        'remark' => true,
        'created' => false,
        'modified' => false,
        'event' => false,
        'form_item' => false,
    ];
}
