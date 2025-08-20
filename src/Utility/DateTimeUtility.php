<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use DateTimeInterface;

/**
 * DateTimeUtility Class.
 */
class DateTimeUtility
{
    /**
     * 日付をオブジェクトへ変換
     *
     * @param mixed $date 日付
     * @return \Cake\I18n\FrozenDate|null オブジェクト
     */
    public static function convertToDateObject($date): ?FrozenDate
    {
        if ($date instanceof FrozenDate) {
            return $date;
        } elseif ($date instanceof DateTimeInterface) {
            return FrozenDate::instance($date);
        } elseif (is_scalar($date) && (string)$date !== '') {
            return new FrozenDate((string)$date);
        }

        return null;
    }

    /**
     * 時間をオブジェクトへ変換
     *
     * @param mixed $time 時間
     * @param string|\DateTimeInterface|null $date 日付
     * @return \Cake\I18n\FrozenTime|null オブジェクト
     */
    public static function convertToTimeObject($time, $date = null)
    {
        $time = static::convertToDateTimeObject($time);
        $date = static::convertToDateObject($date);
        if (isset($date) && isset($time)) {
            $time = $time->setDate((int)$date->format('Y'), (int)$date->format('n'), (int)$date->format('j'));
        }

        return $time;
    }

    /**
     * 日時をオブジェクトへ変換
     *
     * @param mixed $dateTime 日付
     * @return \Cake\I18n\FrozenTime|null オブジェクト
     */
    public static function convertToDateTimeObject($dateTime): ?FrozenTime
    {
        if ($dateTime instanceof FrozenTime) {
            return $dateTime;
        } elseif ($dateTime instanceof DateTimeInterface) {
            return FrozenTime::instance($dateTime);
        } elseif (is_scalar($dateTime) && (string)$dateTime !== '') {
            return new FrozenTime((string)$dateTime);
        }

        return null;
    }

    /**
     * 日付が範囲に内包されているかチェック
     *
     * @param string|\DateTimeInterface|null $targetFrom 内包される対象(From)
     * @param string|\DateTimeInterface|null $targetTo 内包される対象(To)
     * @param string|\DateTimeInterface|null $dateFrom 範囲(From)
     * @param string|\DateTimeInterface|null $dateTo 範囲(To)
     * @return bool 判定結果
     */
    public static function isWithinDate($targetFrom, $targetTo, $dateFrom, $dateTo)
    {
        $targetFrom = static::convertToDateObject($targetFrom);
        $targetTo = static::convertToDateObject($targetTo);
        $dateFrom = static::convertToDateObject($dateFrom);
        $dateTo = static::convertToDateObject($dateTo);

        if (isset($dateFrom)) {
            if (!isset($targetFrom) || $targetFrom < $dateFrom) {
                return false;
            }
        }
        if (isset($dateTo)) {
            if (!isset($targetTo) || $targetTo > $dateTo) {
                return false;
            }
        }

        return true;
    }

    /**
     * 時間が範囲に内包されているかチェック
     *
     * @param string|\DateTimeInterface|null $targetFrom 内包される対象(From)
     * @param string|\DateTimeInterface|null $targetTo 内包される対象(To)
     * @param string|\DateTimeInterface|null $timeFrom 範囲(From)
     * @param string|\DateTimeInterface|null $timeTo 範囲(To)
     * @return bool 判定結果
     */
    public static function isWithinTime($targetFrom, $targetTo, $timeFrom, $timeTo)
    {
        $now = FrozenTime::now();
        $targetFrom = static::convertToTimeObject($targetFrom, $now);
        $targetTo = static::convertToTimeObject($targetTo, $now);
        $timeFrom = static::convertToTimeObject($timeFrom, $now);
        $timeTo = static::convertToTimeObject($timeTo, $now);

        if (!isset($timeFrom) || !isset($timeTo)) {
            return true;
        }
        if ($timeFrom->format('H:i:s') === $timeTo->format('H:i:s')) {
            return true;
        }

        if (!isset($targetFrom) || !isset($targetTo)) {
            return false;
        }
        if ($targetFrom->format('H:i:s') === $targetTo->format('H:i:s')) {
            return false;
        }

        if ($timeFrom < $timeTo) {
            if ($targetFrom < $targetTo) {
                if ($targetFrom < $timeFrom || $targetTo > $timeTo) {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            if ($targetFrom < $targetTo) {
                if ($targetFrom < $timeFrom && $targetTo > $timeTo) {
                    return false;
                }
            } else {
                if ($targetFrom < $timeFrom || $targetTo > $timeTo) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 日時が範囲に内包されているかチェック
     *
     * @param string|\DateTimeInterface|null $targetFrom 内包される対象(From)
     * @param string|\DateTimeInterface|null $targetTo 内包される対象(To)
     * @param string|\DateTimeInterface|null $dateTimeFrom 範囲(From)
     * @param string|\DateTimeInterface|null $dateTimeTo 範囲(To)
     * @return bool 判定結果
     */
    public static function isWithinDateTime($targetFrom, $targetTo, $dateTimeFrom, $dateTimeTo)
    {
        $targetFrom = static::convertToDateTimeObject($targetFrom);
        $targetTo = static::convertToDateTimeObject($targetTo);
        $dateTimeFrom = static::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = static::convertToDateTimeObject($dateTimeTo);

        if (isset($dateTimeFrom)) {
            if (!isset($targetFrom) || $targetFrom < $dateTimeFrom) {
                return false;
            }
        }
        if (isset($dateTimeTo)) {
            if (!isset($targetTo) || $targetTo > $dateTimeTo) {
                return false;
            }
        }

        return true;
    }

    /**
     * 日付が重複しているかチェック
     *
     * @param string|\DateTimeInterface|null $dateFrom1 日付1(From)
     * @param string|\DateTimeInterface|null $dateTo1 日付1(To)
     * @param string|\DateTimeInterface|null $dateFrom2 日付2(From)
     * @param string|\DateTimeInterface|null $dateTo2 日付2(To)
     * @return bool 判定結果
     */
    public static function isOverlapDate($dateFrom1, $dateTo1, $dateFrom2, $dateTo2)
    {
        $dateFrom1 = static::convertToDateObject($dateFrom1);
        $dateTo1 = static::convertToDateObject($dateTo1);
        $dateFrom2 = static::convertToDateObject($dateFrom2);
        $dateTo2 = static::convertToDateObject($dateTo2);

        if (isset($dateFrom1) && isset($dateTo2)) {
            if ($dateFrom1 > $dateTo2) {
                return false;
            }
        }
        if (isset($dateTo1) && isset($dateFrom2)) {
            if ($dateTo1 < $dateFrom2) {
                return false;
            }
        }

        return true;
    }

    /**
     * 時間が重複しているかチェック
     *
     * @param string|\DateTimeInterface|null $timeFrom1 時間1(From)
     * @param string|\DateTimeInterface|null $timeTo1 時間1(To)
     * @param string|\DateTimeInterface|null $timeFrom2 時間2(From)
     * @param string|\DateTimeInterface|null $timeTo2 時間2(To)
     * @return bool 判定結果
     */
    public static function isOverlapTime($timeFrom1, $timeTo1, $timeFrom2, $timeTo2)
    {
        $now = FrozenTime::now();
        $timeFrom1 = static::convertToTimeObject($timeFrom1, $now);
        $timeTo1 = static::convertToTimeObject($timeTo1, $now);
        $timeFrom2 = static::convertToTimeObject($timeFrom2, $now);
        $timeTo2 = static::convertToTimeObject($timeTo2, $now);

        if (!isset($timeFrom1) || !isset($timeTo1) || !isset($timeFrom2) || !isset($timeTo2)) {
            return true;
        }
        if (
            $timeFrom1->format('H:i:s') === $timeTo1->format('H:i:s')
            || $timeFrom2->format('H:i:s') === $timeTo2->format('H:i:s')
        ) {
            return true;
        }

        if ($timeFrom1 < $timeTo1) {
            if ($timeFrom2 < $timeTo2) {
                if ($timeFrom2 >= $timeTo1 || $timeTo2 <= $timeFrom1) {
                    return false;
                }
            } else {
                if ($timeFrom2 >= $timeTo1 && $timeTo2 <= $timeFrom1) {
                    return false;
                }
            }
        } else {
            if ($timeFrom2 < $timeTo2) {
                if ($timeFrom2 >= $timeTo1 && $timeTo2 <= $timeFrom1) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 日時が重複しているかチェック
     *
     * @param string|\DateTimeInterface|null $dateTimeFrom1 日時1(From)
     * @param string|\DateTimeInterface|null $dateTimeTo1 日時1(To)
     * @param string|\DateTimeInterface|null $dateTimeFrom2 日時2(From)
     * @param string|\DateTimeInterface|null $dateTimeTo2 日付日時(To)
     * @return bool 判定結果
     */
    public static function isOverlapDateTime($dateTimeFrom1, $dateTimeTo1, $dateTimeFrom2, $dateTimeTo2)
    {
        $dateTimeFrom1 = static::convertToDateTimeObject($dateTimeFrom1);
        $dateTimeTo1 = static::convertToDateTimeObject($dateTimeTo1);
        $dateTimeFrom2 = static::convertToDateTimeObject($dateTimeFrom2);
        $dateTimeTo2 = static::convertToDateTimeObject($dateTimeTo2);

        if (isset($dateTimeFrom1) && isset($dateTimeTo2)) {
            if ($dateTimeFrom1 >= $dateTimeTo2) {
                return false;
            }
        }
        if (isset($dateTimeTo1) && isset($dateTimeFrom2)) {
            if ($dateTimeTo1 <= $dateTimeFrom2) {
                return false;
            }
        }

        return true;
    }

    /**
     * 曜日、祝日の指定を満たしているか判定
     *
     * @param string|\DateTimeInterface $targetDate 判定対象の日付
     * @param array|null $weeks 曜日
     * @param bool $checkHolidays 祝日チェック
     * @param array|null $holidays 祝日
     * @return bool
     */
    public static function isWithinWeekHoliday(
        $targetDate,
        ?array $weeks = null,
        bool $checkHolidays = false,
        ?array $holidays = null
    ) {
        $targetDate = static::convertToDateObject($targetDate);
        if (is_null($targetDate)) {
            throw new CakeException();
        }

        if (!empty($weeks) || $checkHolidays) {
            if (isset($holidays[$targetDate->format('Y-m-d')])) {
                if (!$checkHolidays) {
                    return false;
                }
            } else {
                if (!ArrayUtility::inArray($targetDate->format('N'), (array)$weeks)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 日付、曜日、祝日、除外日の指定を満たしているか判定
     *
     * @param string|\DateTimeInterface $targetDate 判定対象の日付
     * @param string|\DateTimeInterface|null $dateFrom 日付(From)
     * @param string|\DateTimeInterface|null $dateTo 日付(To)
     * @param array|null $weeks 曜日
     * @param bool $checkHolidays 祝日チェック
     * @param array|null $excludeDates 除外日
     * @param array|null $holidays 祝日
     * @return bool
     */
    public static function isWithinDateWeekHoliday(
        $targetDate,
        $dateFrom,
        $dateTo,
        ?array $weeks = null,
        bool $checkHolidays = false,
        ?array $excludeDates = null,
        ?array $holidays = null
    ) {
        $targetDate = static::convertToDateTimeObject($targetDate);
        if (is_null($targetDate)) {
            throw new CakeException();
        }

        if (!static::isWithinDate($targetDate, $targetDate, $dateFrom, $dateTo)) {
            return false;
        }
        if (!static::isWithinWeekHoliday($targetDate, $weeks, $checkHolidays, $holidays)) {
            return false;
        }

        foreach ((array)$excludeDates as $excludeDate) {
            $excludeDate = static::convertToDateObject($excludeDate);
            if (!isset($excludeDate)) {
                throw new CakeException();
            }
            if ($targetDate->format('Y-m-d') === $excludeDate->format('Y-m-d')) {
                return false;
            }
        }

        return true;
    }

    /**
     * 分単位を切り捨て
     *
     * @param string|\DateTimeInterface $dateTime 日時
     * @param int $time 切り捨てる単位
     * @return \Cake\I18n\FrozenTime
     */
    public static function truncateMinute($dateTime, $time)
    {
        $dateTime = static::convertToDateTimeObject($dateTime);
        if (!isset($dateTime)) {
            throw new CakeException();
        }

        $result = new FrozenTime($dateTime->format('Y-m-d H:i:00'));
        $diff = (new FrozenTime($dateTime->format('Y-m-d H:00:00')))->diffInSeconds($result);
        $result = $result->subSeconds($diff % (MINUTE * $time));

        return $result;
    }

    /**
     * 週の開始日を計算
     *
     * @param string|\DateTimeInterface $date 日付
     * @param int|null $firstWeek 最初の曜日
     * @return \Cake\I18n\FrozenDate 開始日
     */
    public static function getWeekFirstDay($date, ?int $firstWeek = null)
    {
        if (!isset($firstWeek)) {
            $firstWeek = FrozenDate::SUNDAY;
        }

        $date = static::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        $date = $date->subDays(((int)$date->format('N') - $firstWeek + 7) % 7);

        return $date;
    }

    /**
     * カレンダーを生成
     *
     * @param string|\DateTimeInterface $date 日付
     * @param int|null $firstWeek 最初の曜日
     * @return array カレンダー
     */
    public static function createCalendar($date, ?int $firstWeek = null)
    {
        $date = static::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }
        if (!isset($firstWeek)) {
            $firstWeek = FrozenDate::SUNDAY;
        }

        $weeks = [];
        for ($i = 0; $i < 7; ++$i) {
            $weeks[$i] = (($firstWeek + $i - 1) % 7) + 1;
        }

        $dates = [];
        $day = static::getWeekFirstDay($date->format('Y-m-01'), $firstWeek);
        while (true) {
            $row = [];
            for ($i = 0; $i < 7; ++$i) {
                $row[$i] = $day;
                $day = $day->addDays(1);
            }
            $dates[] = $row;

            if ($day->format('n') !== $date->format('n')) {
                break;
            }
        }

        $current = new FrozenDate($date->format('Y-m-01'));
        $previous = clone $current;
        $previous = $previous->subMonths(1);
        $next = clone $current;
        $next = $next->addMonths(1);

        return [
            'weeks' => $weeks,
            'dates' => $dates,
            'current' => $current,
            'previous' => $previous,
            'next' => $next,
        ];
    }

    /**
     * 指定した月の日付をリストで返す
     *
     * @param string|null $month 取得したい月(デフォルトは当月)
     * @return array 対象となる月の日付のリスト(Chronosオブジェクトのリスト)
     */
    public static function getDaysOfMonth($month = null)
    {
        $daysOfMonth = [];

        if (is_null($month)) {
            $time = FrozenTime::now()->startOfMonth();
        } else {
            $time = FrozenTime::createFromFormat('Y/m/d', $month)->startOfMonth();
        }

        $start = $time->startOfMonth()->day;
        $end = $time->endOfMonth()->day;

        for ($i = $start; $i <= $end; $i++) {
            $daysOfMonth[] = $time;
            $time = $time->addDays(1);
        }

        return $daysOfMonth;
    }

    /**
     * min-maxで$interval単位のキーがhh:iiの形式の時間を返却する
     *
     * @param int $min 最小
     * @param int $max 最大
     * @param int $interval 間隔
     * @param string|null $suffix 接尾辞
     * @param string $mode 種別
     * @return mixed
     */
    public static function createTimeList(int $min, int $max, int $interval, ?string $suffix = null, $mode = 'time')
    {
        $list = [];
        if ($mode === 'time') {
            for ($i = $min; $i <= $max; $i += $interval) {
                $time = sprintf('%02d', $i) . ':00';
                $list[$time] = $i . $suffix;
            }
        } else {
            for ($i = $min; $i <= $max; $i += $interval) {
                $time = $i;
                $list[$time] = $i . $suffix;
            }
        }

        return $list;
    }

    /**
     * 日付の0埋め
     *
     * @param string $val 日付
     * @return string
     */
    public static function zeroPaddingDate($val)
    {
        if (!preg_match("/^[0-9]+[\/\-][0-9]+[\/\-][0-9]+$/", $val)) {
            return $val;
        }

        $date = preg_split("/[\/\-]+/", $val);
        if (!is_array($date)) {
            return $val;
        }

        return vsprintf('%04d/%02d/%02d', $date);
    }

    /**
     * 時間の0埋め
     *
     * @param string $val 時間
     * @return string
     */
    public static function zeroPaddingTime($val)
    {
        if (!preg_match("/^[0-9]+[\:][0-9]+$/", $val)) {
            return $val;
        }

        $time = preg_split("/[\:]+/", $val);

        if (!is_array($time)) {
            return $val;
        }

        return vsprintf('%02s:%02s', $time);
    }

    /**
     * 日時の0埋め
     *
     * @param string $val 日時
     * @return string
     */
    public static function zeroPaddingDateTime($val)
    {
        if (!preg_match("/^[0-9]+[\/\-][0-9]+[\/\-][0-9]+[\s][0-9]+[\:][0-9]+$/", $val)) {
            return $val;
        }

        $dateTime = preg_split("/[\/\-\s\:]+/", $val);

        if (!is_array($dateTime)) {
            return $val;
        }

        return vsprintf('%04d/%02d/%02d %02d:%02d', $dateTime);
    }
}
