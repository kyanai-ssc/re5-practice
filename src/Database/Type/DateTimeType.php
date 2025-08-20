<?php
declare(strict_types=1);

namespace App\Database\Type;

use Cake\Database\Type\DateTimeType as CakeDateTimeType;

/**
 * DateTimeType class.
 */
class DateTimeType extends CakeDateTimeType
{
    /**
     * @inheritDoc
     */
    protected $_format = 'Y-m-d H:i';

    /**
     * @inheritDoc
     */
    protected $_marshalFormats = [
        'Y-m-d H:i',
        'Y-m-d H:i:s',
        'Y-m-d\TH:i',
        'Y-m-d\TH:i:s',
        'Y-m-d\TH:i:sP',
        'Y/m/d H:i',
        'Y/m/d H:i:s',
        'Y/m/d\TH:i',
        'Y/m/d\TH:i:s',
        'Y/m/d\TH:i:sP',
    ];
}
