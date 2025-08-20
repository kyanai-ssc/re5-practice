<?php
declare(strict_types=1);

namespace App\Database\Type;

use Cake\Database\Type\DateType as CakeDateType;

/**
 * DateType class.
 */
class DateType extends CakeDateType
{
    /**
     * @inheritDoc
     */
    protected $_marshalFormats = [
        'Y-m-d',
        'Y/m/d',
    ];
}
