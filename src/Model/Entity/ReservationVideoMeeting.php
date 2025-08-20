<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * ReservationVideoMeeting Entity
 *
 * @property int $id
 * @property int $reservation_id
 * @property int $organizer_id
 * @property string $video_meeting_url
 * @property string $video_meeting_id
 * @property string|null $video_meeting_password
 * @property int $video_meeting_type
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Organizer $organizer
 * @property \App\Model\Entity\Reservation $reservation
 */
class ReservationVideoMeeting extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'reservation_id' => true,
        'organizer_id' => true,
        'video_meeting_url' => true,
        'video_meeting_id' => true,
        'video_meeting_password' => true,
        'video_meeting_type' => true,
        'created' => false,
        'modified' => false,
        'organizer' => false,
        'reservation' => false,
    ];
}
