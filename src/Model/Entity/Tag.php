<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * Tag Entity
 *
 * @property int $id
 * @property int $tag_group_id
 * @property string $name
 * @property int|null $sort_no
 * @property int $public_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\TagGroup $tag_group
 * @property \App\Model\Entity\EventTag[] $event_tags
 */
class Tag extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'tag_group_id' => true,
        'name' => true,
        'sort_no' => true,
        'public_flg' => true,
        'created' => false,
        'modified' => false,
        'tag_group' => false,
        'event_tags' => false,
    ];

    /**
     * 公開フラグ：オン
     */
    public const PUBLIC_FLG_ON = 1;

    /**
     * 公開フラグ：オフ
     */
    public const PUBLIC_FLG_OFF = 0;
}
