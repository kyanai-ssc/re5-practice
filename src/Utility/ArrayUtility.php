<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Core\Exception\CakeException;

/**
 * ArrayUtility Class.
 */
class ArrayUtility
{
    /**
     * 数値は文字列へ変換し厳密な型比較を行って配列中の値をチェックする
     *
     * @param mixed $needle 検索値
     * @param array $haystack 配列
     * @return bool 存在有無
     */
    public static function inArray($needle, array $haystack)
    {
        $convert = function ($value) {
            if (is_integer($value)) {
                $value = (string)$value;
            }

            return $value;
        };

        $needle = call_user_func($convert, $needle);
        foreach ($haystack as $key => $value) {
            $haystack[$key] = call_user_func($convert, $value);
        }

        return in_array($needle, $haystack, true);
    }

    /**
     * 数値は文字列へ変換し厳密な型比較を行って配列中の値を検索する
     *
     * @param mixed $needle 検索値
     * @param array $haystack 配列
     * @return mixed キー
     */
    public static function arraySearch($needle, array $haystack)
    {
        $convert = function ($value) {
            if (is_integer($value)) {
                $value = (string)$value;
            }

            return $value;
        };

        $needle = call_user_func($convert, $needle);
        foreach ($haystack as $key => $value) {
            $haystack[$key] = call_user_func($convert, $value);
        }

        return array_search($needle, $haystack, true);
    }

    /**
     * 連想配列のユニーク化
     *
     * @param array $haystack 配列
     * @param string $key ユニークにしたいキー
     * @return array キー
     */
    public static function arrayUnique(array $haystack, string $key)
    {
        $tmp = [];
        $uniqueArray = [];
        foreach ($haystack as $value) {
            if (!in_array($value[$key], $tmp)) {
                $tmp[] = $value[$key];
                $uniqueArray[] = $value;
            }
        }

        return $uniqueArray;
    }

    /**
     * 再帰的にコールバック関数を適用する
     *
     * @param callable $callback コールバック関数
     * @param array $array 配列
     * @return array 適用結果
     */
    public static function arrayMapRecursive(callable $callback, array $array)
    {
        $map = function ($data, $index = null, $path = null) use (&$map, $callback) {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $path = $key;
                    if (isset($index)) {
                        $path = sprintf('%s.%s', $index, $key);
                    }
                    $data[$key] = call_user_func($map, $value, $key, $path);
                }
            } else {
                $data = call_user_func($callback, $data, $index, $path);
            }

            return $data;
        };

        /** @var array $result */
        $result = call_user_func($map, $array);

        return $result;
    }

    /**
     * 配列の要素を指定に型にキャストする
     *
     * @param array $arr キャストしたい配列
     * @param string $cast_type 【int, str, float, bool】を指定することが可能
     * @return array キャストした配列を返却
     */
    public static function arrayCast(array $arr, string $cast_type): array
    {
        $callBack = $cast_type . 'val';

        if (!is_callable($callBack)) {
            throw new CakeException();
        }

        return array_map($callBack, $arr);
    }

    /**
     * 配列内の最大値を取得する
     *
     * 配列専用のmax()。空配列を渡すと例外を投げる
     *
     * @param array $array 配列
     * @return mixed
     */
    public static function arrayMax(array $array)
    {
        if (empty($array)) {
            throw new CakeException();
        }

        return max($array);
    }
}
