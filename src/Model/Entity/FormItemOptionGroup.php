<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * FormItemOptionGroup Entity
 *
 * @property int $id
 * @property int $form_item_id
 * @property int $select_type
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\FormItem $form_item
 * @property \App\Model\Entity\FormItemOption[] $form_item_options
 */
class FormItemOptionGroup extends AppEntity
{
    public const SELECT_TYPE_SINGLE = 1;
    public const SELECT_TYPE_MULTIPLE = 2;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'form_item_id' => false,
        'select_type' => true,
        'created' => false,
        'modified' => false,
        'form_item' => false,
        'form_item_options' => true,
    ];

    /**
     * 複数選択可能なタイプの判定
     *
     * @return bool
     */
    public function isMultipleType()
    {
        if ((string)$this->get('select_type') === ((string)static::SELECT_TYPE_MULTIPLE)) {
            return true;
        }

        return false;
    }
}
