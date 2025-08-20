<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * TagGroup Entity
 *
 * @property int $id
 * @property string $name
 * @property int|null $sort_no
 * @property int $public_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Tag[] $tags
 */
class TagGroup extends AppEntity
{
    /**
     * 公開フラグ：オン
     */
    public const PUBLIC_FLG_ON = 1;

    /**
     * 公開フラグ：オフ
     */
    public const PUBLIC_FLG_OFF = 0;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'name' => true,
        'sort_no' => true,
        'public_flg' => true,
        'created' => false,
        'modified' => false,
        'tags' => true,
    ];
}
