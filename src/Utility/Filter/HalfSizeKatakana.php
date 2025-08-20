<?php
declare(strict_types=1);

namespace App\Utility\Filter;

/**
 * HalfSizeKatakana class.
 */
class HalfSizeKatakana extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function filter($value)
    {
        return mb_convert_kana($value, 'kh');
    }
}
