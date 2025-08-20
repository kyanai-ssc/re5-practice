<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Core\Exception\CakeException;
use Cake\Utility\Security;

/**
 * StringUtility Class.
 */
class StringUtility
{
    public const RANDOM_STRING = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public const ENCRYPT_SALT_LENGTH = 32;

    /**
     * 改行コードを指定文字へ置換
     *
     * @param string $data 置換対象
     * @param string $replace 置換文字
     * @return string 置換後の文字列
     */
    public static function replaceLinefeed(string $data, string $replace)
    {
        $result = $data;
        $result = preg_replace('/\\r\\n/', "\n", $result);
        if (!is_string($result)) {
            throw new CakeException();
        }
        $result = preg_replace('/\\r|\\n/', $replace, $result);
        if (!is_string($result)) {
            throw new CakeException();
        }

        return $result;
    }

    /**
     * ランダム文字列を生成
     *
     * @param int $length 文字数
     * @param string|null $list 文字リスト
     * @return string
     */
    public static function randomString(int $length = 1, ?string $list = null)
    {
        if (!isset($list)) {
            $list = static::RANDOM_STRING;
        }
        $list = static::splitString($list);
        if (!is_array($list) || empty($list)) {
            throw new CakeException();
        }
        $listCount = count($list);

        $random = [];
        for ($i = 0; $i < $length; ++$i) {
            $random[] = $list[random_int(0, $listCount - 1)];
        }

        return implode('', $random);
    }

    /**
     * Base64エンコードし76文字で改行
     *
     * @param string $data 文字列
     * @return string
     */
    public static function mimeBase64Encode($data)
    {
        $result = implode("\n", str_split(base64_encode($data), 76));

        return $result;
    }

    /**
     * HTMLを除去してBRタグを改行に変換
     *
     * @param string $string 文字列
     * @return string
     */
    public static function nl2brStripTags($string)
    {
        // 大文字・小文字を区別しない
        $br2nlStr = preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "\n", $string);
        if (is_string($br2nlStr)) {
            return strip_tags($br2nlStr);
        }

        return $string;
    }

    /**
     * 文字列を1文字ごとに分割
     *
     * @param string $string 文字列
     * @return array
     */
    public static function splitString(string $string)
    {
        $split = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($split)) {
            throw new CakeException();
        }

        return $split;
    }

    /**
     * 正規表現の後方参照の文字をエスケープ
     *
     * @param string|null $string 文字列
     * @return string
     */
    public static function pregReplaceQuote($string)
    {
        $result = preg_replace('/(\\\\|\\$)/', '\\\\${1}', (string)$string);
        if (is_null($result)) {
            throw new CakeException();
        }

        return $result;
    }

    /**
     * 文字列を暗号化
     *
     * @param string $value 文字列
     * @param string $key キー
     * @param string|null $salt ソルト
     * @return string
     */
    public static function encrypt(string $value, string $key, ?string $salt = null)
    {
        if (!isset($salt)) {
            $salt = Security::getSalt();
        }
        $keySalt = Security::randomString(static::ENCRYPT_SALT_LENGTH);

        return $keySalt . base64_encode(Security::encrypt($value, static::generateCryptKey($key, $keySalt), $salt));
    }

    /**
     * 文字列を復号化
     *
     * @param string $crypt 暗号化文字列
     * @param string $key キー
     * @param string|null $salt ソルト
     * @return string|null
     */
    public static function decrypt(string $crypt, string $key, ?string $salt = null)
    {
        if (!isset($salt)) {
            $salt = Security::getSalt();
        }
        $keySalt = substr($crypt, 0, static::ENCRYPT_SALT_LENGTH);

        $crypt = base64_decode(substr($crypt, static::ENCRYPT_SALT_LENGTH));
        if (!is_string($crypt)) {
            return null;
        }
        $value = Security::decrypt($crypt, static::generateCryptKey($key, $keySalt), $salt);
        if (!is_string($value)) {
            return null;
        }

        return $value;
    }

    /**
     * 暗号化キーを生成
     *
     * @param string $key キー
     * @param string $salt ソルト
     * @return string
     */
    protected static function generateCryptKey($key, $salt)
    {
        return substr(Security::hash($key, 'sha1', $salt), 0, static::ENCRYPT_SALT_LENGTH);
    }

    /**
     * 国コードと電話番号をフォーマット
     *
     * @param mixed $value 文字列
     * @return array
     */
    public static function formatCountryCodeAndPhoneNumber($value)
    {
        $result = [
            'countryCode' => '',
            'number' => '',
        ];

        if (!is_string($value) || strpos($value, ' ') === false || strpos($value, '+') === false) {
            return $result;
        }

        $splitValue = preg_split('/\s/', $value, 2);
        if (!is_array($splitValue)) {
            return $result;
        }

        if (
            !isset($splitValue[0]) || !is_string($splitValue[0])
            || !isset($splitValue[1]) || !is_string($splitValue[1])
        ) {
            return $result;
        }

        // 国コード
        $result['countryCode'] = preg_replace('/[^0-9]/', '', $splitValue[0]);
        // 電話番号
        $result['number'] = preg_replace('/[^0-9]/', '', $splitValue[1]);

        return $result;
    }
}
