<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\InputType\AbstractInputTypeManager;

/**
 * AkerunUserId class.
 */
class AkerunUserId extends AbstractInputTypeManager
{
    /**
     * @var bool
     */
    protected $canReservationDisplay = true;
}
