<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\Entity\FormGroup;
use App\Model\InputType\AbstractInputTypeManager;

/**
 * EventRemark class.
 */
class EventRemark extends AbstractInputTypeManager
{
    /**
     * @var bool
     */
    protected $canSelectRequired = false;

    /**
     * @var array
     */
    protected $canCreate = [
        FormGroup::FORM_TYPE_USER => false,
        FormGroup::FORM_TYPE_RESERVATION => true,
    ];
}
