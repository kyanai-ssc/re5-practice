<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * ReservationOption Entity
 *
 * @property int $id
 * @property int $reservation_id
 * @property int $option_id
 * @property int $form_item_id
 * @property int $number
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Reservation $reservation
 * @property \App\Model\Entity\Option $option
 * @property \App\Model\Entity\FormItem $form_item
 */
class ReservationOption extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'reservation_id' => true,
        'option_id' => true,
        'form_item_id' => true,
        'number' => true,
        'created' => false,
        'modified' => false,
        'reservation' => false,
        'option' => false,
        'form_item' => false,
    ];
}
