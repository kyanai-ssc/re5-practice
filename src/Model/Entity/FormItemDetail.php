<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * FormItemDetail Entity
 *
 * @property int $id
 * @property int $form_item_id
 * @property string|null $front_word
 * @property string|null $back_word
 * @property int|null $text_lower_limit
 * @property int|null $text_upper_limit
 * @property int|null $text_input_translate
 * @property int|null $text_input_check
 * @property \Cake\I18n\FrozenDate|null $date_lower_limit
 * @property int|null $date_upper_limit_type
 * @property \Cake\I18n\FrozenDate|null $date_upper_limit_absolute
 * @property int|null $date_upper_limit_relative
 * @property \Cake\I18n\FrozenDate|null $date_default
 * @property int|null $sort_no
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\FormItem $form_item
 */
class FormItemDetail extends AppEntity
{
    public const TEXT_INPUT_TRANSLATE_HALF_SIZE_KATAKANA = 1;
    public const TEXT_INPUT_TRANSLATE_HALF_SIZE_NUMBER = 2;
    public const TEXT_INPUT_TRANSLATE_FULL_SIZE_KATAKANA = 3;
    public const TEXT_INPUT_TRANSLATE_FULL_SIZE_NUMBER = 4;

    public const TEXT_INPUT_CHECK_MAIL = 1;
    public const TEXT_INPUT_CHECK_ZIP_CODE = 2;
    public const TEXT_INPUT_CHECK_PHONE_NUMBER_WITH_HYPHEN = 3;
    public const TEXT_INPUT_CHECK_PHONE_NUMBER_WITHOUT_HYPHEN = 4;
    public const TEXT_INPUT_CHECK_DATE_FORMAT = 5;
    public const TEXT_INPUT_CHECK_HALF_SIZE = 6;
    public const TEXT_INPUT_CHECK_HALF_SIZE_NUMBER = 7;
    public const TEXT_INPUT_CHECK_HALF_SIZE_ALPHAMERIC = 8;
    public const TEXT_INPUT_CHECK_HALF_SIZE_ALPHANUMERIC_SYMBOL = 9;
    public const TEXT_INPUT_CHECK_HALF_SIZE_KATAKANA = 10;
    public const TEXT_INPUT_CHECK_FULL_SIZE = 11;
    public const TEXT_INPUT_CHECK_FULL_SIZE_HIRAGANA = 12;
    public const TEXT_INPUT_CHECK_FULL_SIZE_KATAKANA = 13;

    public const DATE_UPPER_LIMIT_TYPE_ABSOLUTE = 1;
    public const DATE_UPPER_LIMIT_TYPE_RELATIVE = 2;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'form_item_id' => false,
        'front_word' => true,
        'back_word' => true,
        'text_lower_limit' => true,
        'text_upper_limit' => true,
        'text_input_translate' => true,
        'text_input_check' => true,
        'date_lower_limit' => true,
        'date_upper_limit_type' => true,
        'date_upper_limit_absolute' => true,
        'date_upper_limit_relative' => true,
        'date_default' => true,
        'sort_no' => false,
        'created' => false,
        'modified' => false,
        'form_item' => false,
    ];

    /**
     * date_lower_limitのアクセサ
     *
     * @param mixed $value 値
     * @return string
     */
    protected function _getDateLowerLimit($value)
    {
        return $this->formatDateToString($value, '/');
    }

    /**
     * date_upper_limit_absoluteのアクセサ
     *
     * @param mixed $value 値
     * @return string
     */
    protected function _getDateUpperLimitAbsolute($value)
    {
        return $this->formatDateToString($value, '/');
    }

    /**
     * date_defaultのアクセサ
     *
     * @param mixed $value 値
     * @return string
     */
    protected function _getDateDefault($value)
    {
        return $this->formatDateToString($value, '/');
    }
}
