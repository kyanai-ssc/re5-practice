<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Model\Entity\Traits\AdditionTrait;

/**
 * ReservationAddition Entity
 *
 * @property int $id
 * @property int $reservation_id
 * @property int $form_item_id
 * @property string|null $value
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Reservation $reservation
 * @property \App\Model\Entity\FormItem $form_item
 */
class ReservationAddition extends AppEntity
{
    use AdditionTrait;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'reservation_id' => true,
        'form_item_id' => true,
        'value' => true,
        'created' => false,
        'modified' => false,
        'reservation' => false,
        'form_item' => false,
        'data' => true,
    ];

    protected $_virtual = ['data'];

    /**
     * @var \App\Model\Entity\Reservation
     */
    protected $reservationEntity;

    /**
     * 予約のエンティティーを設定
     *
     * @param \App\Model\Entity\Reservation $reservationEntity エンティティー
     * @return void
     */
    public function setReservationEntity(Reservation $reservationEntity)
    {
        $this->reservationEntity = $reservationEntity;
    }

    /**
     * 予約のエンティティーを取得
     *
     * @return \App\Model\Entity\Reservation|null エンティティー
     */
    public function getReservationEntity()
    {
        return $this->reservationEntity;
    }
}
