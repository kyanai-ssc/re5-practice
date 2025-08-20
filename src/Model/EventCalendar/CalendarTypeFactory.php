<?php
declare(strict_types=1);

namespace App\Model\EventCalendar;

use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;

/**
 * CalendarTypeFactory class.
 */
class CalendarTypeFactory
{
    /**
     * カレンダータイプのインスタンスを取得
     *
     * @param int $calendarType カレンダータイプ
     * @param bool $adminFlg 管理者側フラグ
     * @return \App\Model\EventCalendar\AbstractCalendarType
     */
    public static function getInstance(int $calendarType, bool $adminFlg = false)
    {
        $className = Configure::readOrFail('Master.event.calendarTypeClass.' . $calendarType);
        $class = '\\App\\Model\\EventCalendar\\CalendarType\\' . $className;
        $instance = new $class($calendarType, $adminFlg);
        if (!($instance instanceof AbstractCalendarType)) {
            throw new CakeException();
        }

        return $instance;
    }
}
