<?php
declare(strict_types=1);

namespace App\Model\EventCalendar;

use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;

/**
 * CalendarTypeFactory class.
 */
class CalendarPopupFactory
{
    /**
     * カレンダーポップアップのインスタンスを取得
     *
     * @param int $calendarPopupType ポップアップタイプ
     * @param bool $adminFlg 管理者側フラグ
     * @return \App\Model\EventCalendar\AbstractCalendarPopup
     */
    public static function getInstance(int $calendarPopupType, bool $adminFlg = false)
    {
        $className = Configure::readOrFail('Master.reservation.calendarPopupTypeClass.' . $calendarPopupType);
        $class = '\\App\\Model\\EventCalendar\\CalendarPopup\\' . $className;
        $instance = new $class($calendarPopupType, $adminFlg);
        if (!($instance instanceof AbstractCalendarPopup)) {
            throw new CakeException();
        }

        return $instance;
    }
}
