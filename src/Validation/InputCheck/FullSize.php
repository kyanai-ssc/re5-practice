<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use Cake\Validation\Validation;

/**
 * FullSize class.
 */
class FullSize extends AbstractInputCheck
{
    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        $invalidCode = [
            '\\x{0000}-\\x{007f}', // NUL-DEL
            '\\x{ff61}-\\x{ff9f}', // ｡-ﾟ
        ];
        if (!Validation::custom($value, '/^[^' . implode('', $invalidCode) . ']+$/u')) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return __(Message::ERROR_FULL_SIZE);
    }
}
