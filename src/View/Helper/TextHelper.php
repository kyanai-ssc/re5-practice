<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper\TextHelper as CakeTextHelper;

/**
 * TextHelper class. TextHelper
 */
class TextHelper extends CakeTextHelper
{
    /**
     * @inheritDoc
     */
    public function truncate(?string $text, int $length = 100, array $options = []): string
    {
        if (is_null($text)) {
            $text = '';
        }

        $result = parent::truncate($text, $length, $options);

        if (Hash::get($options, 'escape', false)) {
            $result = h($result);
        }

        if (Hash::get($options, 'tooltip', false)) {
            $result = '<span class="tooltip" title="' . h($text) . '">' . $result . '</span>';
        }

        return $result;
    }

    /**
     * 改行を特定の区切り文字で区切る
     *
     * @param string|null $text text
     * @param string $delimiter 区切り文字
     * @return string|string[]|null
     */
    public function nl2format($text, $delimiter = ' / ')
    {
        if (is_null($text)) {
            return '';
        }

        return preg_replace('/(?:\n|\r|\r\n)/', $delimiter, $text);
    }
}
