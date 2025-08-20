<?php
declare(strict_types=1);

namespace App\Model;

use Cake\I18n\FrozenDate;

/**
 * DateTimeFormat trait.
 */
trait DateTimeFormatTrait
{
    /**
     * 時間をHH:mmフォーマットに変更
     *
     * @param string|\Cake\I18n\FrozenTime|null $time datetimeオブジェクト
     * @param bool $to TOフラグ
     * @return mixed 00:00～24:00の文字列
     */
    protected function formatTime24($time, bool $to = false)
    {
        if (!is_object($time)) {
            return $time;
        }

        if ($to === true && $time->i18nFormat('HH:mm') === '00:00') {
            $formatTime = '24:00';
        } else {
            $formatTime = $time->i18nFormat('HH:mm');
        }

        return $formatTime;
    }

    /**
     * 時間をYYYY-mm-dd H:iフォーマットに変更
     *
     * @param string|\Cake\I18n\FrozenTime|null $datetime datetimeオブジェクト
     * @return mixed YYYY-mm-dd H:iの文字列
     */
    protected function formatDateTimeHyphen($datetime)
    {
        if (!is_object($datetime)) {
            return $datetime;
        }

        return $datetime->i18nFormat('yyyy-MM-dd HH:mm');
    }

    /**
     * 日にちをYYYY-mm-dd フォーマットに変更
     *
     * @param string|\Cake\I18n\FrozenDate|null $date datetimeオブジェクト
     * @return mixed yyyy-MM-ddの文字列
     */
    protected function formatDateHyphen($date)
    {
        if (!is_object($date)) {
            return $date;
        }

        return $this->formatDateToString($date, '-');
    }

    /**
     * 日にちを引数区切りのstringにフォーマット
     *
     * @param string|\Cake\I18n\FrozenDate|null $date datetimeオブジェクト
     * @param string $separate 区切り文字 デフォルトスラッシュ
     * @return mixed フォーマットの文字列
     */
    protected function formatDateToString($date, $separate = '/')
    {
        if ($date instanceof FrozenDate) {
            return $date->format('Y' . $separate . 'm' . $separate . 'd');
        }

        return $date;
    }
}
