<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use Cake\Validation\Validation;

/**
 * HalfSizeAlphameric class.
 */
class HalfSizeAlphameric extends AbstractInputCheck
{
    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        if (!Validation::custom($value, '/^[0-9A-Za-z]+$/')) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return __(Message::ERROR_ALPHA_NUMERIC);
    }
}
