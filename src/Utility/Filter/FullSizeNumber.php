<?php
declare(strict_types=1);

namespace App\Utility\Filter;

/**
 * FullSizeNumber class.
 */
class FullSizeNumber extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function filter($value)
    {
        return mb_convert_kana($value, 'N');
    }
}
