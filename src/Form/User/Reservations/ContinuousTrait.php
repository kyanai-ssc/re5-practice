<?php
declare(strict_types=1);

namespace App\Form\User\Reservations;

/**
 * Continuous trait.
 */
trait ContinuousTrait
{
    /**
     * 予約フォームを生成
     *
     * @return \App\Form\User\Reservations\ReservationForm
     */
    protected function createReservationForm()
    {
        $reservationForm = new ReservationForm();

        return $reservationForm;
    }
}
