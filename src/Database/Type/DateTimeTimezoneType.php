<?php
declare(strict_types=1);

namespace App\Database\Type;

use Cake\Database\Type\DateTimeTimezoneType as CakeDateTimeTimezoneType;

/**
 * DateTimeTimezoneType class.
 */
class DateTimeTimezoneType extends CakeDateTimeTimezoneType
{
    /**
     * @inheritDoc
     */
    protected $_marshalFormats = [
        'Y-m-d H:i',
        'Y-m-d H:i:s',
        'Y-m-d H:i:sP',
        'Y-m-d H:i:s.u',
        'Y-m-d H:i:s.uP',
        'Y-m-d\TH:i',
        'Y-m-d\TH:i:s',
        'Y-m-d\TH:i:sP',
        'Y-m-d\TH:i:s.u',
        'Y-m-d\TH:i:s.uP',
        'Y/m/d H:i',
        'Y/m/d H:i:s',
        'Y/m/d H:i:sP',
        'Y/m/d H:i:s.u',
        'Y/m/d H:i:s.uP',
        'Y/m/d\TH:i',
        'Y/m/d\TH:i:s',
        'Y/m/d\TH:i:sP',
        'Y/m/d\TH:i:s.u',
        'Y/m/d\TH:i:s.uP',
    ];
}
