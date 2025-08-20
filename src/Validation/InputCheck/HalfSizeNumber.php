<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use Cake\Validation\Validation;

/**
 * HalfSizeNumber class.
 */
class HalfSizeNumber extends AbstractInputCheck
{
    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        if (!Validation::custom($value, '/^[0-9]+$/')) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return __(Message::ERROR_HALF_SIZE_NUMBER);
    }
}
