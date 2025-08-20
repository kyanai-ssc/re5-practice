<?php
declare(strict_types=1);

namespace App\Utility\Filter;

/**
 * HalfSizeNumber class.
 */
class HalfSizeNumber extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function filter($value)
    {
        return mb_convert_kana($value, 'n');
    }
}
