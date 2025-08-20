<?php
declare(strict_types=1);

namespace App\Utility\Filter;

/**
 * FullSizeKatakana class.
 */
class FullSizeKatakana extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function filter($value)
    {
        return mb_convert_kana($value, 'KVC');
    }
}
