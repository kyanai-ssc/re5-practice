<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\StringUtility;
use App\Utility\VideoMeeting\VideoMeetingFactory;
use App\Utility\VideoMeeting\ZoomApi;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;

/**
 * Organizer Entity
 *
 * @property int $id
 * @property string $name
 * @property int $video_meeting_type
 * @property string|null $zoom_api_key
 * @property string|null $zoom_api_secret
 * @property int $zoom_connect_type
 * @property string|null $zoom_host_email
 * @property string|null $meet_api_key
 * @property string|null $meet_calendar_id
 * @property string|null $sort_key
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\ReservationVideoMeeting[] $reservation_video_meetings
 */
class Organizer extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'name' => true,
        'video_meeting_type' => true,
        'zoom_api_key' => true,
        'zoom_api_secret' => true,
        'zoom_connect_type' => true,
        'zoom_host_email' => true,
        'meet_api_key' => true,
        'meet_calendar_id' => true,
        'sort_key' => true,
        'created' => false,
        'modified' => false,
        'events' => false,
        'reservation_video_meetings' => false,
    ];

    /** Zoom */
    public const VIDEO_MEETING_TYPE_ZOOM = 1;
    /** Meet */
    public const VIDEO_MEETING_TYPE_MEET = 2;

    /** Zoom 認証タイプ: JWT */
    public const ZOOM_CONNECT_TYPE_JWT = 1;
    /** Zoom 認証タイプ: Oauth */
    public const ZOOM_CONNECT_TYPE_OAUTH = 2;

    /**
     * 削除可否
     *
     * @return bool
     */
    public function canDelete()
    {
        $conditions = [
            'organizer_id' => $this->get('id'),
        ];
        if (
            $this->getTableLocator()->get('Events')->exists($conditions)
            || $this->getTableLocator()->get('ReservationVideoMeetings')->exists($conditions)
        ) {
            return false;
        }

        return true;
    }

    /**
     * 主催者の表示名を生成
     *
     * @return string
     */
    public function createDisplayName()
    {
        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->getTableLocator()->get('Organizers');

        return $organizersTable->createDisplayName($this->get('video_meeting_type'), $this->get('name'));
    }

    /**
     * ビデオ会議のモジュールを作成
     *
     * @return \App\Utility\VideoMeeting\AbstractVideoMeeting
     */
    public function createVideoMeetingModule()
    {
        if ((string)$this->get('video_meeting_type') === (string)static::VIDEO_MEETING_TYPE_ZOOM) {
            // Zoom
            if ((string)$this->get('zoom_connect_type') === (string)static::ZOOM_CONNECT_TYPE_JWT) {
                // JWT
                $videoMeeting = VideoMeetingFactory::createZoomApiModule(
                    ZoomApi::AUTHORIZATION_TYPE_JWT,
                    $this->decryptApiInfo($this->get('zoom_api_key')),
                    $this->decryptApiInfo($this->get('zoom_api_secret'))
                );
                $videoMeeting->setZoomUserId($this->get('zoom_host_email'));
            } elseif ((string)$this->get('zoom_connect_type') === (string)static::ZOOM_CONNECT_TYPE_OAUTH) {
                // OAuth
                $videoMeeting = VideoMeetingFactory::createZoomApiModule(ZoomApi::AUTHORIZATION_TYPE_OAUTH);
                $videoMeeting->setZoomUserId($this->get('zoom_host_email'));
            } else {
                throw new CakeException();
            }
        } elseif ((string)$this->get('video_meeting_type') === (string)static::VIDEO_MEETING_TYPE_MEET) {
            // Meet
            $secretJson = (array)json_decode($this->decryptApiInfo($this->get('meet_api_key')), true);
            $videoMeeting = VideoMeetingFactory::createMeetApiModule($secretJson);
            $videoMeeting->setCalendarId($this->get('meet_calendar_id'));
        } else {
            throw new CakeException();
        }

        return $videoMeeting;
    }

    /**
     * API情報を暗号化
     *
     * @param string $apiInfo API情報
     * @return string
     */
    public function encryptApiInfo(string $apiInfo)
    {
        return StringUtility::encrypt(
            $apiInfo,
            (string)$this->get('video_meeting_type'),
            Configure::readOrFail('Env.videoMeeting.salt')
        );
    }

    /**
     * API情報を復号化
     *
     * @param string $crypt 暗号化文字列
     * @return string
     */
    public function decryptApiInfo(string $crypt)
    {
        $value = StringUtility::decrypt(
            $crypt,
            (string)$this->get('video_meeting_type'),
            Configure::readOrFail('Env.videoMeeting.salt')
        );
        if (!isset($value)) {
            throw new CakeException();
        }

        return $value;
    }

    /**
     * Zoom API Keyのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setZoomApiKey($data)
    {
        if (!is_string($data) || $data === '') {
            return null;
        }

        return $this->encryptApiInfo($data);
    }

    /**
     * Zoom API Secretのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setZoomApiSecret($data)
    {
        if (!is_string($data) || $data === '') {
            return null;
        }

        return $this->encryptApiInfo($data);
    }

    /**
     * Meet Secret JSONのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setMeetApiKey($data)
    {
        if (!is_string($data) || $data === '') {
            return null;
        }

        return $this->encryptApiInfo($data);
    }
}
