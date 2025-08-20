<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\InputType\AbstractInputTypeManager;

/**
 * UserAuthority class.
 */
class UserAuthority extends AbstractInputTypeManager
{
    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var bool
     */
    protected $canReservationDisplay = true;
}
