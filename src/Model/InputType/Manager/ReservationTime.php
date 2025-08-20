<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\InputType\AbstractInputTypeManager;

/**
 * ReservationTime class.
 */
class ReservationTime extends AbstractInputTypeManager
{
    /**
     * @var bool
     */
    protected $required = true;
}
