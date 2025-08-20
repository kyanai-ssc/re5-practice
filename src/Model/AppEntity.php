<?php
declare(strict_types=1);

namespace App\Model;

use App\Utility\ArrayUtility;
use App\Utility\CommonData\CommonDataTrait;
use Cake\ORM\Entity;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * An entity represents a single result row from a repository.
 */
class AppEntity extends Entity
{
    use CommonDataTrait;
    use DateTimeFormatTrait;
    use LocatorAwareTrait;

    /**
     * チェックボックス形式のフィルタリング
     *
     * @param array $data 値
     * @return array
     */
    protected function filterForMultiCheckbox(array $data)
    {
        $data = array_values(array_filter($data, function ($value) {
            return $value || is_numeric($value);
        }));

        return ArrayUtility::arrayMapRecursive(function ($value) {
            return (int)$value;
        }, $data);
    }
}
