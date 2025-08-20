<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventStockSettingExcludeDate Entity
 *
 * @property int $id
 * @property int $event_stock_setting_id
 * @property \Cake\I18n\FrozenDate $date
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\EventStockSetting $event_stock_setting
 */
class EventStockSettingExcludeDate extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_stock_setting_id' => false,
        'date' => true,
        'created' => false,
        'modified' => false,
        'event_stock_setting' => false,
    ];
}
