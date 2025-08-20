<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * FormPatternOption Entity
 *
 * @property int $id
 * @property int $form_pattern_id
 * @property int $form_item_option_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\FormPattern $form_pattern
 * @property \App\Model\Entity\FormItemOption $form_item_option
 */
class FormPatternOption extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'form_pattern_id' => false,
        'form_item_option_id' => true,
        'created' => false,
        'modified' => false,
        'form_pattern' => false,
        'form_item_option' => false,
    ];
}
