<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Model\InputType\AbstractInputTypeManager;

/**
 * MailConfirm class.
 */
class MailConfirm extends AbstractInputTypeManager
{
    /**
     * @var bool
     */
    protected $canSelectRequired = true;
}
