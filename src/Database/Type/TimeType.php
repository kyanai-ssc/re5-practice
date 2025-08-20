<?php
declare(strict_types=1);

namespace App\Database\Type;

use Cake\Database\Type\TimeType as CakeTimeType;

/**
 * TimeType class.
 */
class TimeType extends CakeTimeType
{
    /**
     * @inheritDoc
     */
    protected $_format = 'H:i';
}
