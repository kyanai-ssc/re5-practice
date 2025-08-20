<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * FormGroup Entity
 *
 * @property int $id
 * @property int $form_type
 * @property string|null $name
 * @property int $name_display_flg
 * @property int $sort_no
 * @property int|null $smart_lock_type
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\FormItem[] $form_items
 */
class FormGroup extends AppEntity
{
    /**
     * フォームタイプ 会員
     *
     * @var int
     */
    public const FORM_TYPE_USER = 1;

    /**
     * フォームタイプ 予約
     *
     * @var int
     */
    public const FORM_TYPE_RESERVATION = 2;

    /**
     * グループ名表示 非表示
     *
     * @var int
     */
    public const NAME_DISPLAY_FLG_OFF = 0;

    /**
     * グループ名表示 表示
     *
     * @var int
     */
    public const NAME_DISPLAY_FLG_ON = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'form_type' => false,
        'name' => true,
        'name_display_flg' => true,
        'sort_no' => false,
        'smart_lock_type' => false,
        'created' => false,
        'modified' => false,
        'form_items' => true,
    ];
}
