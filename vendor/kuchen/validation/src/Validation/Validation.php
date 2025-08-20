<?php

declare(strict_types=1);

namespace Kuchen\Validation\Validation;

use Cake\Validation\Validation as CakeValidation;
use DateTimeInterface;

/**
 * Validation 拡張
 */
class Validation extends CakeValidation
{
    /**
     * 半角英数字のみ
     *
     * {@inheritdoc}
     */
    public static function alphaNumeric($check): bool
    {
        if ((empty($check) && $check !== '0') || !is_scalar($check)) {
            return false;
        }

        return self::_check($check, '/^[a-zA-Z0-9]+$/');
    }

    /**
     * Validation::email() の全角文字無効化
     *
     * {@inheritdoc}
     */
    public static function email($check, $deep = false, ?string $regex = null): bool
    {
        return CakeValidation::email($check, $deep, $regex) &&
            self::_check($check, '/^[[:graph:][:space:]]+$/');
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $check
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
        $leapYear = '(?:(?:(?:(?!0000)[012]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))';
        // 4 digit leap year sub-pattern
        $fourDigitLeapYear = '(?:(?:(?:(?!0000)[012]\\d)(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))';

        $regex['dmy'] = '%^(?:(?:31(\\/|-|\\.|\\x20)(?:0?[13578]|1[02]))\\1|(?:(?:29|30)' .
            $separator . '(?:0?[13-9]|1[0-2])\\2))' . $year . '$|^(?:29' .
            $separator . '0?2\\3' . $leapYear . ')$|^(?:0?[1-9]|1\\d|2[0-8])' .
            $separator . '(?:(?:0?[1-9])|(?:1[0-2]))\\4' . $year . '$%';

        $regex['mdy'] = '%^(?:(?:(?:0?[13578]|1[02])(\\/|-|\\.|\\x20)31)\\1|(?:(?:0?[13-9]|1[0-2])' .
            $separator . '(?:29|30)\\2))' . $year . '$|^(?:0?2' . $separator . '29\\3' . $leapYear . ')$|^(?:(?:0?[1-9])|(?:1[0-2]))' .
            $separator . '(?:0?[1-9]|1\\d|2[0-8])\\4' . $year . '$%';

        $regex['ymd'] = '%^(?:(?:' . $leapYear .
            $separator . '(?:0?2\\1(?:29)))|(?:' . $year .
            $separator . '(?:(?:(?:0?[13578]|1[02])\\2(?:31))|(?:(?:0?[13-9]|1[0-2])\\2(29|30))|(?:(?:0?[1-9])|(?:1[0-2]))\\2(?:0?[1-9]|1\\d|2[0-8]))))$%';

        $regex['dMy'] = '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ ' . $fourDigitLeapYear . '))|(0?[1-9])|1\\d|2[0-8])\\ (Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\ ' . $fourDigitYear . '$/';

        $regex['Mdy'] = '/^(?:(((Jan(uary)?|Ma(r(ch)?|y)|Jul(y)?|Aug(ust)?|Oct(ober)?|Dec(ember)?)\\ 31)|((Jan(uary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep)(tember)?|(Nov|Dec)(ember)?)\\ (0?[1-9]|([12]\\d)|30))|(Feb(ruary)?\\ (0?[1-9]|1\\d|2[0-8]|(29(?=,?\\ ' . $fourDigitLeapYear . ')))))\\,?\\ ' . $fourDigitYear . ')$/';

        $regex['My'] = '%^(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)' .
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
}
