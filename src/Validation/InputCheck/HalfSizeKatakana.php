<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use Cake\Validation\Validation;

/**
 * HalfSizeKatakana class.
 */
class HalfSizeKatakana extends AbstractInputCheck
{
    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        $validCode = [
            '\\x{ff61}-\\x{ff9f}', // ｡-ﾟ
        ];
        if (!Validation::custom($value, '/^[' . implode('', $validCode) . ']+$/u')) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return __(Message::ERROR_HALF_SIZE_KATAKANA);
    }
}
