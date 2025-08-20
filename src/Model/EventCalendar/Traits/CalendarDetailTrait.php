<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\Traits;

use App\Model\EventCalendar\EventUnit;
use App\Model\InputType\Item\Type\CalendarOutputInterface;
use Cake\Core\Exception\CakeException;

/**
 * CalendarDetail trait.
 */
trait CalendarDetailTrait
{
    /**
     * @var mixed
     */
    protected $displayItem = null;

    /**
     * カレンダーの表示項目を取得
     *
     * @return \App\Model\Entity\FormItem|null
     */
    public function getCalendarDisplayFormItem()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        if (((string)$this->displayItem) === '') {
            return null;
        }
        $formItem = $formItemsTable->getFormItem($this->displayItem);
        if (!isset($formItem)) {
            throw new CakeException();
        }

        return $formItem;
    }

    /**
     * カレンダーの表示項目IDを取得
     *
     * @return int|null
     */
    public function getCalendarDisplayFormItemId()
    {
        $formItem = $this->getCalendarDisplayFormItem();
        if (!isset($formItem)) {
            return null;
        }

        return $formItem->get('id');
    }

    /**
     * カレンダーの表示項目を設定
     *
     * @param int|null $displayItem フォーム項目
     * @return void
     */
    public function setCalendarDisplayItem(?int $displayItem = null)
    {
        $this->displayItem = $displayItem;
    }

    /**
     * カレンダーの予約データ表示可否を判定
     *
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @return bool
     */
    public function isReserveDataDisplay(EventUnit $eventUnit)
    {
        $data = $eventUnit->getReservationData();
        if (((string)$data === '') || $eventUnit->getAllStock() !== 1 || $eventUnit->getReservedStock() !== 1) {
            return false;
        }

        return true;
    }

    /**
     * 詳細の検索データを取得
     *
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @return array
     */
    public function getDetailSearchData(EventUnit $eventUnit)
    {
        $searchData = [
            'event_id' => $eventUnit->getEvent()->get('id'),
            'usage_timestamp_from' => $eventUnit->getDateTimeFrom()->format('Y/m/d H:i'),
            'usage_timestamp_to' => $eventUnit->getDateTimeTo()->format('Y/m/d H:i'),
            'display_item' => $this->displayItem,
        ];

        return $searchData;
    }

    /**
     * カレンダーの表示項目を適用
     *
     * @param array $timetable タイムテーブル
     * @param \DateTimeInterface|null $dateTimeFrom 開始日時
     * @param \DateTimeInterface|null $dateTimeTo 終了日時
     * @return void
     */
    protected function applyAdminCalendarData($timetable, $dateTimeFrom = null, $dateTimeTo = null)
    {
        if (!$this->isAdmin()) {
            return;
        }

        $this->applyCalendarReservationCount($timetable, $dateTimeFrom, $dateTimeTo);
        $this->applyCalendarDisplayItem($timetable, $dateTimeFrom, $dateTimeTo);
    }

    /**
     * カレンダーの表示件数を適用
     *
     * @param array $timetable タイムテーブル
     * @param \DateTimeInterface|null $dateTimeFrom 開始日時
     * @param \DateTimeInterface|null $dateTimeTo 終了日時
     * @return void
     */
    protected function applyCalendarReservationCount($timetable, $dateTimeFrom = null, $dateTimeTo = null)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $timetableByEvent = [];
        foreach ($timetable as $eventTimetable) {
            $timetableByEvent[$eventTimetable->getEvent()->get('id')][] = $eventTimetable;
        }
        if (empty($timetableByEvent)) {
            return;
        }

        $reservations = $reservationsTable->find('calendarReservationCount', [
            'inputs' => [
                'event_id' => array_keys($timetableByEvent),
                'usage_timestamp_from' => $dateTimeFrom,
                'usage_timestamp_to' => $dateTimeTo,
            ],
        ]);

        foreach ($reservations as $reservation) {
            foreach ($timetableByEvent[$reservation['event_id']] as $eventTimetable) {
                $eventTimetable->addReservationCount(
                    $reservation['usage_timestamp_from'],
                    $reservation['usage_timestamp_to'],
                    $reservation['reservation_status_id'],
                    $reservation['count']
                );
            }
        }
    }

    /**
     * カレンダーの表示項目を適用
     *
     * @param array $timetable タイムテーブル
     * @param \DateTimeInterface|null $dateTimeFrom 開始日時
     * @param \DateTimeInterface|null $dateTimeTo 終了日時
     * @return void
     */
    protected function applyCalendarDisplayItem($timetable, $dateTimeFrom = null, $dateTimeTo = null)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $formItem = $this->getCalendarDisplayFormItem();
        if (!isset($formItem)) {
            return;
        }
        $inputTypeItem = $formItem->getInputTypeItem();
        if (!($inputTypeItem instanceof CalendarOutputInterface)) {
            throw new CakeException();
        }

        $timetableByEvent = [];
        foreach ($timetable as $eventTimetable) {
            $timetableByEvent[$eventTimetable->getEvent()->get('id')][] = $eventTimetable;
        }
        if (empty($timetableByEvent)) {
            return;
        }

        $reservations = $reservationsTable->find('calendarDisplay', [
            'inputs' => [
                'event_id' => array_keys($timetableByEvent),
                'usage_timestamp_from' => $dateTimeFrom,
                'usage_timestamp_to' => $dateTimeTo,
                'display_item' => $this->displayItem,
            ],
        ]);

        foreach ($reservations as $reservation) {
            $reservationData = $inputTypeItem->getCalendarOutputValue($reservation);

            foreach ($timetableByEvent[$reservation['event_id']] as $eventTimetable) {
                $eventTimetable->setReservationData(
                    $reservation['usage_timestamp_from'],
                    $reservation['usage_timestamp_to'],
                    $reservationData
                );
            }
        }
    }
}
