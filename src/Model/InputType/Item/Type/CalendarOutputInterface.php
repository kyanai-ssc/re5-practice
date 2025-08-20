<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

use App\Model\Entity\Reservation;

/**
 * CalendarOutput interface.
 */
interface CalendarOutputInterface
{
    /**
     * カレンダーの出力内容を取得
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return string|null 出力内容
     */
    public function getCalendarOutputValue(Reservation $reservation);
}
