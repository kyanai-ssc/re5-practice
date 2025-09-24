<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * FormItemChoice Entity
 *
 * @property int $id
 * @property int $form_item_id
 * @property string $name
 * @property int $sort_no
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\FormItem $form_item
 */
class FormItemChoice extends AppEntity
{
    /**
     * 繰り返し予約フラグ：可
     */
    public const REPEAT_RESERVATION_FLG_ON = '1';

    /**
     * 繰り返し予約フラグ：不可
     */
    public const REPEAT_RESERVATION_FLG_OFF = '2';

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'form_item_id' => false,
        'name' => true,
        'sort_no' => false,
        'created' => false,
        'modified' => false,
        'form_item' => false,
    ];
}
