<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use Cake\Validation\Validation;

/**
 * ZipCode class.
 */
class ZipCode extends AbstractInputCheck
{
    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        if (!Validation::custom($value, '/^\\d{7}$/')) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return __(Message::ERROR_ZIP_CODE);
    }
}
