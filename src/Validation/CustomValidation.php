<?php
declare(strict_types=1);

namespace App\Validation;

use App\Utility\CommonData\CommonDataFactory;
use App\Utility\DateTimeUtility;
use App\Utility\StringUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use DateTimeInterface;
use Kuchen\Validation\Validation\Validation;

/**
 * Class CustomValidation
 *
 * @package App\Validation
 */
class CustomValidation extends Validation
{
    /**
     * bigint の最大値
     */
    public const BIGINT_MAX = '9223372036854775807';

    /**
     * integer の最大値
     */
    public const INTEGER_MAX = '2147483647';

    /**
     * smallint の最大値
     */
    public const SMALLINT_MAX = '32767';

    /**
     * INTEGER の範囲内か
     *
     * @param mixed $value val
     * @param string $max 最大値
     * @return bool
     */
    public static function integer($value, $max = self::INTEGER_MAX)
    {
        $value = (string)$value;
        if (!(ctype_digit($value) && ctype_digit($max))) {
            return false;
        }

        return bccomp($max, $value) !== -1;
    }

    /**
     * 0～24時
     *
     * @param mixed $value 値
     * @param array $context context
     * @return bool
     */
    public static function time24h($value, $context)
    {
        $valid = static::time($value);

        if ($valid === false && $value !== '24:00') {
            return false;
        }

        return true;
    }

    /**
     * 倍数判定（フィールド同士）
     *
     * @param mixed $value 値
     * @param string $field フィールド名
     * @param array $context context
     * @return bool
     */
    public static function multipleNum($value, $field, $context)
    {
        if (!static::integer($value) || !static::integer($context['data'][$field])) {
            return false;
        }

        return static::multipleNumStatic($value, $context['data'][$field], $context);
    }

    /**
     * 倍数判定(特定の数字)
     *
     * @param mixed $value 値
     * @param int|string $number 数値
     * @param array $context context
     * @return bool
     */
    public static function multipleNumStatic($value, $number, $context)
    {
        if (!self::integer($value)) {
            return false;
        }

        if (!Validation::naturalNumber((string)$number)) {
            return false;
        }

        if ((int)$value % (int)$number === 0) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public static function date($check, $format = 'ymd', ?string $regex = null): bool
    {
        if ($check instanceof DateTimeInterface) {
            return true;
        }
        if (is_object($check)) {
            return false;
        }
        if (is_array($check)) {
            $check = static::_getDateString($check);
            $format = 'ymd';
        }

        if ($regex !== null) {
            return static::_check($check, $regex);
        }
        $month = '(0[123456789]|10|11|12)';
        $separator = '([-/])'; // 変更点: 区切り文字を限定する
        // Don't allow 0000, but 0001-2999 are ok.
        $fourDigitYear = '(?:(?!0000)[012]\d{3})';
        $twoDigitYear = '(?:\d{2})';
        $year = '(?:' . $fourDigitYear . '|' . $twoDigitYear . ')';

        // phpcs:disable Generic.Files.LineLength
        // 2 or 4 digit leap year sub-pattern
        $leapYear = '(?:(?:(?:(?!0000)[012]\\d)?(?:0[48]|[2468][048]|[13579][26])' .
            '|(?:(?:16|[2468][048]|[3579][26])00)))';
        // 4 digit leap year sub-pattern
        $fourDigitLeapYear = '(?:(?:(?:(?!0000)[012]\\d)(?:0[48]|[2468][048]|[13579][26])' .
            '|(?:(?:16|[2468][048]|[3579][26])00)))';

        $regex['dmy'] = '%^(?:(?:31(\\/|-|\\.|\\x20)(?:0?[13578]|1[02]))\\1|(?:(?:29|30)' .
            $separator . '(?:0?[13-9]|1[0-2])\\2))' . $year . '$|^(?:29' .
            $separator . '0?2\\3' . $leapYear . ')$|^(?:0?[1-9]|1\\d|2[0-8])' .
            $separator . '(?:(?:0?[1-9])|(?:1[0-2]))\\4' . $year . '$%';

        $regex['mdy'] = '%^(?:(?:(?:0?[13578]|1[02])(\\/|-|\\.|\\x20)31)\\1|(?:(?:0?[13-9]|1[0-2])' .
            $separator . '(?:29|30)\\2))' . $year . '$|^(?:0?2' . $separator . '29\\3' . $leapYear . ')$' .
            '|^(?:(?:0?[1-9])|(?:1[0-2]))' .
            $separator . '(?:0?[1-9]|1\\d|2[0-8])\\4' . $year . '$%';

        $regex['ymd'] = '%^(?:(?:' . $leapYear .
            $separator . '(?:02\\1(?:29)))|(?:' . $year .
            $separator . '(?:(?:(?:0[13578]|1[02])\\2(?:31))|(?:(?:0[13-9]|1[0-2])\\2(29|30))' .
            '|(?:(?:0[1-9])|(?:1[0-2]))\\2(?:0[1-9]|1\\d|2[0-8]))))$%';

        $regex['dMy'] = '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))' .
            '|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ ' . $fourDigitLeapYear . '))' .
            '|(0?[1-9])|1\\d|2[0-8])\\ (Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))' .
            '|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\ ' . $fourDigitYear . '$/';

        $regex['Mdy'] = '/^(?:(((Jan(uary)?|Ma(r(ch)?|y)|Jul(y)?|Aug(ust)?|Oct(ober)?|Dec(ember)?)\\ 31)' .
            '|((Jan(uary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep)(tember)?' .
            '|(Nov|Dec)(ember)?)\\ (0?[1-9]|([12]\\d)|30))|(Feb(ruary)?\\ (0?[1-9]|1\\d|2[0-8]|(29(?=,?\\ ' .
            $fourDigitLeapYear . ')))))\\,?\\ ' . $fourDigitYear . ')$/';

        $regex['My'] = '%^(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?' .
            '|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)' .
            $separator . $fourDigitYear . '$%';
        // phpcs:enable Generic.Files.LineLength

        $regex['my'] = '%^(' . $month . $separator . $year . ')$%';
        $regex['ym'] = '%^(' . $year . $separator . $month . ')$%';
        $regex['y'] = '%^(' . $fourDigitYear . ')$%';

        $format = is_array($format) ? array_values($format) : [$format];
        foreach ($format as $key) {
            if (static::_check($check, $regex[$key]) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public static function time($check): bool
    {
        if ($check instanceof DateTimeInterface) {
            return true;
        }
        if (is_array($check)) {
            $check = static::_getDateString($check);
        }

        if (!is_scalar($check)) {
            return false;
        }

        $standardClockRegex = '^(0?\d|1\d|2[0-3])((:[0-5]\d){1,2})$';

        return static::_check($check, '%' . $standardClockRegex . '%');
    }

    /**
     * 日時の比較
     *
     * @param mixed $check1 The left value to compare.
     * @param string $operator Comparison operator.
     * @param mixed $check2 The right value to compare.
     * @return bool
     */
    public static function compareDateTime($check1, string $operator, $check2): bool
    {
        $check1 = DateTimeUtility::convertToDateTimeObject($check1);
        $check2 = DateTimeUtility::convertToDateTimeObject($check2);
        if (!isset($check1) || !isset($check2)) {
            return true;
        }

        switch ($operator) {
            case static::COMPARE_GREATER:
                if ($check1 > $check2) {
                    return true;
                }
                break;
            case static::COMPARE_LESS:
                if ($check1 < $check2) {
                    return true;
                }
                break;
            case static::COMPARE_GREATER_OR_EQUAL:
                if ($check1 >= $check2) {
                    return true;
                }
                break;
            case static::COMPARE_LESS_OR_EQUAL:
                if ($check1 <= $check2) {
                    return true;
                }
                break;
            case static::COMPARE_EQUAL:
                if ($check1 == $check2) {
                    return true;
                }
                break;
            case static::COMPARE_NOT_EQUAL:
                if ($check1 != $check2) {
                    return true;
                }
                break;
            case static::COMPARE_SAME:
                if ($check1 === $check2) {
                    return true;
                }
                break;
            case static::COMPARE_NOT_SAME:
                if ($check1 !== $check2) {
                    return true;
                }
                break;
            default:
                break;
        }

        return false;
    }

    /**
     * 日時フィールドの比較
     *
     * @param mixed $check The value to find in $field.
     * @param string $field The field to check $check against. This field must be present in $context.
     * @param string $operator Comparison operator.
     * @param array $context The validation context.
     * @return bool
     */
    public static function compareDateTimeFields($check, string $field, string $operator, array $context): bool
    {
        return static::compareDateTime($check, $operator, Hash::get($context['data'], $field));
    }

    /**
     * 半角英数記号
     *
     * @param mixed $check 値
     * @return bool
     */
    public static function alnumSym($check)
    {
        if (empty($check) && $check !== '0') {
            return false;
        }

        $len = mb_strlen($check);
        for ($i = 0; $i < $len; $i++) {
            $cut = mb_substr($check, $i, 1);
            if (!preg_match('/^[\x21-\x7e]{1}$/', $cut)) {
                // 半角英数記号でない場合
                return false;
            }
        }

        return true;
    }

    /**
     * 半角英数字
     *
     * @param mixed $check 値
     * @return bool
     */
    public static function alnum($check)
    {
        if (!is_string($check) && !is_int($check) && !is_float($check)) {
            return false;
        }

        if (!preg_match('/^[a-z\d]*$/i', (string)$check)) {
            return false;
        }

        return true;
    }

    /**
     * ディレクトリ名
     *
     * @param mixed $check 値
     * @return bool
     */
    public static function isDirectory($check)
    {
        if (empty($check) && $check !== '0') {
            return false;
        }

        if (!preg_match('/^[A-Za-z\d_-]+$/', $check)) {
            return false;
        }

        return true;
    }

    /**
     * ファイル名
     *
     * @param mixed $check 値
     * @return bool
     */
    public static function isFile($check)
    {
        if (empty($check) && $check !== '0') {
            return false;
        }

        if (!preg_match('/^[A-Za-z\d_-]+$/', $check)) {
            return false;
        }

        return true;
    }

    /**
     * ディレクトリ存在チェック
     *
     * @param mixed $check 値
     * @param string $path ディレクトリ
     * @return bool
     */
    public static function dirExists($check, $path)
    {
        if (empty($check) && $check !== '0') {
            return false;
        }

        if (empty($path) && $path !== '0') {
            return false;
        }

        if (file_exists($path . $check)) {
            return false;
        }

        return true;
    }

    /**
     * 電話番号（ハイフンあり）チェック
     *
     * @param string $value 値
     * @return bool
     */
    public static function phoneNumberWithHyphen($value)
    {
        $length = [
            '0' => [
                'min' => 2,
                'max' => 5,
            ],
            '1' => [
                'min' => 1,
                'max' => 4,
            ],
            '2' => [
                'min' => 4,
                'max' => 4,
            ],
        ];

        $data = preg_split('/-/', $value);
        if (!is_array($data) || count($data) !== 3) {
            return false;
        }

        foreach ($data as $index => $number) {
            if (!Validation::custom($number, '/^\\d+$/')) {
                return false;
            }
            if (!Validation::lengthBetween($number, $length[$index]['min'], $length[$index]['max'])) {
                return false;
            }
        }
        if (!static::phoneNumberFirstTwoDigits($data[0] . '-' . $data[1])) {
            return false;
        }

        return true;
    }

    /**
     * 電話番号（ハイフンなし）チェック
     *
     * @param string $value 値
     * @return bool
     */
    public static function phoneNumberWithoutHyphen($value)
    {
        if (!Validation::custom($value, '/^\\d{10,11}$/')) {
            return false;
        }

        return true;
    }

    /**
     * 電話番号上2桁チェック
     *
     * @param string $value 値
     * @return bool
     */
    public static function phoneNumberFirstTwoDigits($value)
    {
        $length = [
            'min' => 6,
            'max' => 7,
        ];

        $data = preg_split('/-/', $value);
        if (!is_array($data) || count($data) < 2) {
            return false;
        }

        if (!Validation::lengthBetween($data[0] . $data[1], $length['min'], $length['max'])) {
            return false;
        }

        return true;
    }

    /**
     * 各種入力チェック
     *
     * @param mixed $value 値
     * @param int $inputCheck 入力チェック
     * @return bool
     */
    public static function inputCheck($value, int $inputCheck)
    {
        $className = Configure::readOrFail('Master.form.textInputCheckClass.' . $inputCheck);
        $classPath = '\\App\\Validation\\InputCheck\\' . $className;
        $validation = new $classPath();
        if (!($validation instanceof AbstractInputCheck)) {
            throw new CakeException();
        }

        return $validation->validate($value);
    }

    /**
     * 指定の文字が含まれるか判定
     *
     * @param mixed $value チェック対象
     * @param array $list 判定リスト
     * @param array|null $sensitive 大文字/小文字、全角/半角、平仮名/片仮名の区別
     * @return bool
     */
    public static function includeString($value, array $list, ?array $sensitive = null)
    {
        if (!is_scalar($value)) {
            return false;
        }
        $sensitive = (array)$sensitive + [
            'case' => true,
            'width' => true,
            'kana' => true,
        ];

        $filters = [
            'case' => function ($string) {
                return strtolower(mb_convert_kana($string, 'r'));
            },
            'width' => function ($string) {
                return mb_convert_kana($string, 'ASKV');
            },
            'kana' => function ($string) {
                return mb_convert_kana($string, 'KCV');
            },
        ];

        foreach ($filters as $name => $filter) {
            if (!$sensitive[$name]) {
                $value = call_user_func($filter, $value);
                foreach ($list as $index => $data) {
                    $list[$index] = call_user_func($filter, $data);
                }
            }
        }

        foreach ($list as $data) {
            $pattern = '/' . implode('\\s*', array_map('preg_quote', StringUtility::splitString($data))) . '/u';
            /** @var string $value */
            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * リダイレクト先のチェック
     *
     * @param mixed $redirectUrl リダイレクト先
     * @return bool
     */
    public static function redirectUrl($redirectUrl)
    {
        if (
            !is_string($redirectUrl) || preg_match('/^\\/(?:[^\\/%].*)?$/', $redirectUrl) !== 1
            || !Router::routeExists($redirectUrl)
        ) {
            return false;
        }

        return true;
    }

    /**
     * DKIM設定のドメインと一致するか判定
     *
     * @param mixed $email メールアドレス
     * @return bool
     */
    public static function isDkimDomain($email)
    {
        if (!CommonDataFactory::getInstance()->existsDefaultFromAddressOnEnv()) {
            return true;
        }

        return is_string($email)
            && preg_match(
                sprintf('/^.+%s$/', preg_quote(CommonDataFactory::getInstance()->getDefaultFromAddress(true))),
                $email
            ) === 1;
    }

    /**
     * RFC5322準拠か判定
     *
     * @param mixed $email メールアドレス
     * @return bool
     */
    public static function isRfc5322($email): bool
    {
        $regex = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)' .
            '|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)' .
            '|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)' .
            '|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]' .
            '|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)' .
            '|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]' .
            '|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+' .
            '(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)' .
            '|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)' .
            '|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})' .
            '|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::' .
            '(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))' .
            '|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)' .
            '|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::' .
            '(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])' .
            '|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])' .
            '|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';

        return static::_check($email, $regex);
    }
}
