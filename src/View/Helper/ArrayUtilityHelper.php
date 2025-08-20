<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Utility\ArrayUtility;
use Cake\View\Helper;

/**
 * ArrayUtilityHelper class.
 */
class ArrayUtilityHelper extends Helper
{
    /**
     * 数値は文字列へ変換し厳密な型比較を行って配列中の値をチェックする
     *
     * @param mixed $needle 検索値
     * @param array $haystack 配列
     * @return bool 存在有無
     */
    public function inArray($needle, array $haystack)
    {
        return ArrayUtility::inArray($needle, $haystack);
    }

    /**
     * 数値は文字列へ変換し厳密な型比較を行って配列中の値を検索する
     *
     * @param mixed $needle 検索値
     * @param array $haystack 配列
     * @return bool キー
     */
    public function arraySearch($needle, array $haystack)
    {
        return ArrayUtility::arraySearch($needle, $haystack);
    }
}
