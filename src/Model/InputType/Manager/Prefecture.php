<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\Entity\FormGroup;
use App\Model\InputType\AbstractInputTypeManager;

/**
 * Prefecture class.
 */
class Prefecture extends AbstractInputTypeManager
{
    /**
     * @var array
     */
    protected $canCreate = [
        FormGroup::FORM_TYPE_USER => true,
        FormGroup::FORM_TYPE_RESERVATION => true,
    ];

    /**
     * @var bool
     */
    protected $canSelectRequired = true;

    /**
     * @var bool
     */
    protected $canReservationDisplay = true;
}
