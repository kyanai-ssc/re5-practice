<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * EventStockSettingWeek Entity
 *
 * @property int $id
 * @property int $event_stock_setting_id
 * @property int $week
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\EventStockSetting $event_stock_setting
 */
class EventStockSettingWeek extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'event_stock_setting_id' => false,
        'week' => true,
        'created' => false,
        'modified' => false,
        'event_stock_setting' => false,
    ];
}
