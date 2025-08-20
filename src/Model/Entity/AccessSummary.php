<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * AccessSummary Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenDate $access_date
 * @property int $users
 * @property int $reservations
 * @property int $calendar
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class AccessSummary extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'access_date' => true,
        'users' => true,
        'reservations' => true,
        'calendar' => true,
        'created' => false,
        'modified' => false,
    ];
}
