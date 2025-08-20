<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\Traits;

use App\Model\EventCalendar\EventTimetable;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;

/**
 * TimetablePopup trait.
 */
trait TimetablePopupTrait
{
    /**
     * タイムテーブルポップアップの検索データを取得
     *
     * @param string|\DateTimeInterface $date 日付
     * @param \App\Model\EventCalendar\EventTimetable $eventTimetable タイムテーブル
     * @return array
     */
    public function getTimetablePopupSearchData($date, EventTimetable $eventTimetable)
    {
        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        $searchData = [
            'id' => $eventTimetable->getEvent()->get('id'),
            'date' => $date->format('Y/m/d'),
        ];
        if ($this->isAdmin() && !$this->limitDisplayTime) {
            $searchData['display_all_time'] = Configure::readOrFail('Master.common.flg.on');
        }
        if ($this->isAdmin() && isset($this->displayItem)) {
            $searchData['display_item'] = $this->displayItem;
        }

        return $searchData;
    }
}
