<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * Holiday Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenDate $date
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class Holiday extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'date' => true,
        'created' => false,
        'modified' => false,
    ];
}
