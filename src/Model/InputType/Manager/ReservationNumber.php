<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\InputType\AbstractInputTypeManager;

/**
 * ReservationNumber class.
 */
class ReservationNumber extends AbstractInputTypeManager
{
    /**
     * @var bool
     */
    protected $required = true;
}
