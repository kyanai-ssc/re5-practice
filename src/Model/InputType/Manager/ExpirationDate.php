<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\InputType\AbstractInputTypeManager;

/**
 * ExpirationDate class.
 */
class ExpirationDate extends AbstractInputTypeManager
{
    /**
     * @var bool
     */
    protected $canReservationDisplay = true;
}
