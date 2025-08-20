<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * FormItemOption Entity
 *
 * @property int $id
 * @property int $form_item_option_group_id
 * @property int $option_id
 * @property int $stock_range_from
 * @property int $stock_range_to
 * @property int $sort_no
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\FormItemOptionGroup $form_item_option_group
 * @property \App\Model\Entity\Option $option
 * @property \App\Model\Entity\FormPatternOption[] $form_pattern_options
 */
class FormItemOption extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'form_item_option_group_id' => false,
        'option_id' => true,
        'stock_range_from' => true,
        'stock_range_to' => true,
        'sort_no' => false,
        'created' => false,
        'modified' => false,
        'form_item_option_group' => false,
        'option' => false,
        'form_pattern_options' => false,
    ];
}
