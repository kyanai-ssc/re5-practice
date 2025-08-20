<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * FileGroup Entity
 *
 * @property int $id
 * @property string $name
 * @property string $directory
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\File[] $files
 */
class FileGroup extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'name' => true,
        'directory' => true,
        'created' => false,
        'modified' => false,
        'files' => true,
    ];
}
