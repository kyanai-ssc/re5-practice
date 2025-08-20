<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

use App\Model\Entity\Reservation;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;

/**
 * VideoMeeting trait.
 */
trait VideoMeetingTrait
{
    /**
     * ビデオ会議情報の利用可否を判定
     *
     * @return bool
     */
    protected function canUseVideoMeeting()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if (!$systemSettingsTable->getData()->canCoordinateVideoMeeting()) {
            return false;
        }

        return true;
    }

    /**
     * ビデオ会議情報の有無を判定
     *
     * @return bool
     */
    protected function hasVideoMeeting()
    {
        if (!$this->canUseVideoMeeting()) {
            return false;
        }

        $reservation = $this->getConfig('reservation');
        if (!($reservation instanceof Reservation)) {
            return false;
        }

        return !empty($reservation->get('reservation_video_meetings'))
            && (
                $this->commonData()->existsAdminLoginData()
                || !$reservation->hasReservationPayment()
                || $reservation->getReservationPayment()->isPaid()
            );
    }

    /**
     * ビデオ会議情報を取得
     *
     * @param array|\App\Model\Entity\Reservation|null $reservation 予約
     * @return array|null
     */
    protected function getReservationVideoMeeting($reservation)
    {
        if (!isset($reservation)) {
            return null;
        }

        $reservationVideoMeeting = null;
        if (is_array($reservation)) {
            $reservationVideoMeetings = Hash::get($reservation, 'reservation_video_meetings');
            if (!empty($reservationVideoMeetings)) {
                $reservationVideoMeeting = reset($reservationVideoMeetings);
            }
        } elseif ($reservation instanceof Reservation) {
            $reservationVideoMeeting = $reservation->getReservedVideoMeeting();
            if (isset($reservationVideoMeeting)) {
                $reservationVideoMeeting = $reservationVideoMeeting->toArray();
            }
        } else {
            throw new CakeException();
        }

        return $reservationVideoMeeting;
    }
}
