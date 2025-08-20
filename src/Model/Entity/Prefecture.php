<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * Prefecture Entity
 *
 * @property int $id
 * @property int $code
 * @property string $name
 * @property string $default_name
 * @property int $sort_no
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class Prefecture extends AppEntity
{
    public const NAME_MAX = 100;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'code' => false,
        'name' => true,
        'default_name' => false,
        'sort_no' => false,
        'created' => false,
        'modified' => false,
    ];
}
