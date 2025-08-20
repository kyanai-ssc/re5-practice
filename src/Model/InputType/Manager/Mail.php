<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\InputType\AbstractInputTypeManager;

/**
 * Mail class.
 */
class Mail extends AbstractInputTypeManager
{
    /**
     * @var bool
     */
    protected $canSelectRequired = true;

    /**
     * @var bool
     */
    protected $canReservationDisplay = true;
}
