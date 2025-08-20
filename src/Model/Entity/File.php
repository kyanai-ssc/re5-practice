<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * File Entity
 *
 * @property int $id
 * @property int $file_group_id
 * @property string $file_name
 * @property string $file_type
 * @property string|null $description
 * @property int $size
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\FileGroup $file_group
 */
class File extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'file_group_id' => false,
        'file_name' => true,
        'file_type' => true,
        'description' => true,
        'size' => true,
        'created' => false,
        'modified' => false,
        'file_group' => false,
        'index' => true,
    ];

    protected $_virtual = ['index', 'full_file_name'];

    /**
     * full_file_name
     *
     * @return string
     */
    protected function _getFullFileName()
    {
        if (!is_null($this->get('file_type'))) {
            return $this->get('file_name') . '.' . $this->get('file_type');
        }

        return '';
    }
}
