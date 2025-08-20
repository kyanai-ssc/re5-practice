<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use App\Validation\CustomValidation;

/**
 * Mail class.
 */
class Mail extends AbstractInputCheck
{
    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        if (!CustomValidation::email($value)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return __(Message::ERROR_MAIL_ADDRESS);
    }
}
