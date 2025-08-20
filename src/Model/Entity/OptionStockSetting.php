<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * OptionStockSetting Entity
 *
 * @property int $id
 * @property int $option_id
 * @property \Cake\I18n\FrozenTime $usage_timestamp_from
 * @property \Cake\I18n\FrozenTime $usage_timestamp_to
 * @property int $stock
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Option $option
 */
class OptionStockSetting extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'option_id' => false,
        'usage_timestamp_from' => true,
        'usage_timestamp_to' => true,
        'stock' => true,
        'created' => false,
        'modified' => false,
        'option' => false,
    ];
}
