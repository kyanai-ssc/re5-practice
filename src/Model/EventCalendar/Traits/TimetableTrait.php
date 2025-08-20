<?php
declare(strict_types=1);

namespace App\Model\EventCalendar\Traits;

use App\Model\Entity\ColorChip;
use App\Model\EventCalendar\EventUnit;
use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * Timetable trait.
 */
trait TimetableTrait
{
    /**
     * @var bool
     */
    protected $adminFlg = null;

    /**
     * @var int|null
     */
    protected $userId = null;

    /**
     * @var bool
     */
    protected $limitDisplayTime = null;

    /**
     * @var int|null
     */
    protected $excludeReservationId = null;

    /**
     * @var array|null
     */
    protected $continuousData = null;

    /**
     * 管理者側フラグを取得
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->adminFlg;
    }

    /**
     * 会員IDを設定
     *
     * @param int|null $userId 会員ID
     * @return void
     */
    public function setUserId(?int $userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * 表示時間の制限を設定
     *
     * @param bool $limitDisplayTime 制限有無
     * @return void
     */
    public function setLimitDisplayTime(bool $limitDisplayTime)
    {
        $this->limitDisplayTime = $limitDisplayTime;
    }

    /**
     * 在庫計算から除外する予約を設定
     *
     * @param int $excludeReservationId 予約ID
     * @return void
     */
    public function setExcludeReservationId(?int $excludeReservationId = null)
    {
        $this->excludeReservationId = $excludeReservationId;
    }

    /**
     * 連続予約を設定
     *
     * @param array|null $continuousData 連続予約
     * @return void
     */
    public function setContinuousData($continuousData = null)
    {
        $this->continuousData = $continuousData;
    }

    /**
     * カラーチップを取得
     *
     * @param bool $page 次ページ取得用
     * @return array
     */
    public function getColorChips($page = false)
    {
        /** @var \App\Model\Table\ColorChipsTable $colorChipsTable */
        $colorChipsTable = $this->getTableLocator()->get('ColorChips');
        $colorChipsData = $colorChipsTable->getData();
        $defaultColor = Hash::combine(
            $colorChipsData,
            '{*}[default_flg=' . ColorChip::DEFAULT_FLG_ON . '].id',
            '{*}[default_flg=' . ColorChip::DEFAULT_FLG_ON . '].id'
        );

        $colorChips = array_intersect_key(
            $colorChipsData,
            array_fill_keys($this->getColorChipIds() + $defaultColor, true)
        );

        foreach ($colorChips as $index => $colorChip) {
            $colorChips[$index] = [
                'id' => $index,
                'sort_no' => $colorChip['sort_no'],
                'name' => $colorChip['name'],
                'color_code' => $colorChip['color_code'],
            ];

            if (((string)$colorChip['front_display_flg']) !== ((string)ColorChip::FRONT_DISPLAY_FLG_ON)) {
                unset($colorChips[$index]);
            }
        }

        return $colorChips;
    }

    /**
     * 予約のURLを取得
     *
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @return array
     */
    public function getReservationUrl(EventUnit $eventUnit)
    {
        $prefix = 'User';
        if ($this->isAdmin()) {
            $prefix = 'Admin';
        }
        $url = [
            'prefix' => $prefix,
            'controller' => 'Reservations',
            'action' => 'add',
            '?' => [
                'event_id' => $eventUnit->getEvent()->get('id'),
                'usage_timestamp_from' => $eventUnit->getDateTimeFrom()->format('Y/m/d H:i'),
            ],
        ];

        return $url;
    }

    /**
     * 枠の選択データを取得
     *
     * @param \App\Model\EventCalendar\EventUnit $eventUnit 枠
     * @return array
     */
    public function getSelectCalendarData(EventUnit $eventUnit)
    {
        $calendarData = [
            'event_id' => $eventUnit->getEvent()->get('id'),
            'usage_timestamp_from' => $eventUnit->getDateTimeFrom()->format('Y/m/d H:i'),
        ];

        return $calendarData;
    }

    /**
     * 祝日を取得
     *
     * @param \DateTimeInterface|null $dateFrom 開始日
     * @param \DateTimeInterface|null $dateTo 終了日
     * @return array
     */
    protected function getPublicHolidays($dateFrom = null, $dateTo = null)
    {
        $holidaysTable = $this->getTableLocator()->get('Holidays');

        $holidays = $holidaysTable->find('calendar', [
            'inputs' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ])->toArray();

        return $holidays;
    }

    /**
     * タイムテーブルへ予約を反映
     *
     * @param array $timetable タイムテーブル
     * @param \DateTimeInterface|null $dateTimeFrom 開始日時
     * @param \DateTimeInterface|null $dateTimeTo 終了日時
     * @return void
     */
    protected function applyReservations($timetable, $dateTimeFrom = null, $dateTimeTo = null)
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

        // 在庫計算
        $stockReservations = $reservationsTable->find('calculateStock', [
            'inputs' => [
                'event_id' => array_keys($timetableByEvent),
                'usage_timestamp_from' => $dateTimeFrom,
                'usage_timestamp_to' => $dateTimeTo,
                'exclude_id' => $this->excludeReservationId,
            ],
        ]);
        foreach ($stockReservations as $reservation) {
            foreach ($timetableByEvent[$reservation['event_id']] as $eventTimetable) {
                $eventTimetable->applyReservation(
                    $reservation['usage_timestamp_from'],
                    $reservation['usage_timestamp_to'],
                    (int)$reservation['number']
                );
            }
        }

        // 会員の予約を反映
        if (((string)$this->userId) !== '') {
            $userReservations = $reservationsTable->find('userCalendar', [
                'inputs' => [
                    'user_id' => $this->userId,
                    'event_id' => array_keys($timetableByEvent),
                    'usage_timestamp_from' => $dateTimeFrom,
                    'usage_timestamp_to' => $dateTimeTo,
                ],
            ]);
            foreach ($userReservations as $reservation) {
                foreach ($timetableByEvent[$reservation['event_id']] as $eventTimetable) {
                    $eventTimetable->setUserReservation(
                        $reservation['usage_timestamp_from'],
                        $reservation['usage_timestamp_to']
                    );
                }
            }
        }

        // 連続予約の反映
        foreach ((array)$this->continuousData as $continuousData) {
            $reservation = $continuousData['data']['reservations'];
            if (isset($timetableByEvent[$reservation['event_id']])) {
                foreach ($timetableByEvent[$reservation['event_id']] as $eventTimetable) {
                    $eventTimetable->applyReservation(
                        $reservation['usage_timestamp_from'],
                        $reservation['usage_timestamp_to'],
                        (int)$reservation['number']
                    );
                    $eventTimetable->setContinuousData(
                        $reservation['usage_timestamp_from'],
                        $reservation['usage_timestamp_to']
                    );
                }
            }
        }

        // インターバルの在庫を反映
        $continuousByEvent = [];
        foreach ((array)$this->continuousData as $continuousData) {
            $reservation = $continuousData['data']['reservations'];
            $continuousByEvent[$reservation['event_id']][] = $reservation;
        }
        foreach ($timetable as $eventTimetable) {
            $eventTimetable->applyIntervalStock(
                $this->excludeReservationId,
                Hash::get($continuousByEvent, $eventTimetable->getEvent()->get('id'))
            );
        }
    }

    /**
     * 枠の共通クラスを取得
     *
     * @param \App\Model\EventCalendar\EventUnit|null $eventUnit 枠
     * @param bool $selectCalendar カレンダー選択
     * @return array
     */
    protected function getCommonUnitHtmlClass($eventUnit, bool $selectCalendar = false)
    {
        $class = [];

        if (!$eventUnit instanceof EventUnit) {
            return $class;
        }

        if (!$eventUnit->isAdmin() && $eventUnit->isWaitingCancellation()) {
            $class[] = 'icon_cancel';
        } elseif ($eventUnit->usesEventStockMark()) {
            $eventStockMark = $eventUnit->getEventStockMark();
            if (isset($eventStockMark)) {
                $class[] = Configure::readOrFail('Master.event.symbolicDispClass.' . $eventStockMark);
            }
        }

        if ($eventUnit->isHoliday()) {
            $class[] = 'is_holiday';
        }

        if (!$eventUnit->isAdmin()) {
            if (!$eventUnit->canReserveUnitTime()) {
                $class[] = 'js_cannot_reserve_unit';
            } elseif ($eventUnit->isWaitingCancellation()) {
                $class[] = 'js_waiting_cancellation';
            } elseif ($eventUnit->canReserve()) {
                $class[] = 'js_can_reserve';
            }
        }
        if ($selectCalendar && $eventUnit->canReserve()) {
            $class[] = 'js_select_calendar';
        }

        return $class;
    }

    /**
     * カラーチップIDを取得
     *
     * @return array
     */
    abstract protected function getColorChipIds(): array;
}
