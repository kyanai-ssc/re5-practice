<?php
declare(strict_types=1);

namespace App\Utility\Filter;

/**
 * Filter abstract class.
 */
abstract class AbstractFilter
{
    /**
     * 値のフィルタリング
     *
     * @param mixed $value 値
     * @return mixed
     */
    abstract public function filter($value);
}
