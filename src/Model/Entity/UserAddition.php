<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Model\Entity\Traits\AdditionTrait;

/**
 * UserAddition Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $form_item_id
 * @property string|null $value
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\FormItem $form_item
 */
class UserAddition extends AppEntity
{
    use AdditionTrait;

    /**
     * 繰り返し予約フラグ：不可
     */
    public const REPEAT_RESERVATION_FLG_OFF = '1';

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'user_id' => true,
        'form_item_id' => true,
        'value' => true,
        'created' => false,
        'modified' => false,
        'user' => false,
        'form_item' => false,
        'data' => true,
    ];

    protected $_virtual = ['data'];
}
