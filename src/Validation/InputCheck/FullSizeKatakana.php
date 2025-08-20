<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use Cake\Validation\Validation;

/**
 * FullSizeKatakana class.
 */
class FullSizeKatakana extends AbstractInputCheck
{
    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        $validCode = [
            '\\x{3001}-\\x{3002}', // 、-。
            '\\x{300c}-\\x{300d}', // 「-」
            '\\x{309b}-\\x{309c}', // ゛-゜
            '\\x{30a1}-\\x{30f4}', // ァ-ヴ
            '\\x{30fb}-\\x{30fc}', // ・-ー
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
        return __(Message::ERROR_FULL_SIZE_KATAKANA);
    }
}
