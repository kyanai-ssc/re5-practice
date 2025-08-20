<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use Cake\Validation\Validation;

/**
 * DateFormat class.
 */
class DateFormat extends AbstractInputCheck
{
    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        if (!Validation::date($value, 'ymd')) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return __(Message::ERROR_DATE);
    }
}
