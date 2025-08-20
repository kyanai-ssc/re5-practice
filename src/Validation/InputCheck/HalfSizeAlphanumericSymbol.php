<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use Cake\Validation\Validation;

/**
 * HalfSizeAlphanumericSymbol class.
 */
class HalfSizeAlphanumericSymbol extends AbstractInputCheck
{
    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        if (!Validation::custom($value, '/^[\\x21-\\x7e]+$/')) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return __(Message::ERROR_ALNUM_SYM);
    }
}
