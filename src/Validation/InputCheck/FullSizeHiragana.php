<?php
declare(strict_types=1);

namespace App\Validation\InputCheck;

use App\Locale\Message;
use App\Validation\AbstractInputCheck;
use Cake\Validation\Validation;

/**
 * FullSizeHiragana class.
 */
class FullSizeHiragana extends AbstractInputCheck
{
    /**
     * @inheritDoc
     */
    public function validate($value)
    {
        $validCode = [
            '\\x{3001}-\\x{3002}', // 、-。
            '\\x{300c}-\\x{300d}', // 「-」
            '\\x{3041}-\\x{3093}', // ぁ-ん
            '\\x{309b}-\\x{309c}', // ゛-゜
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
        return __(Message::ERROR_FULL_SIZE_HIRAGANA);
    }
}
