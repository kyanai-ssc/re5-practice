<?php
declare(strict_types=1);

namespace App\Utility\VideoMeeting;

use App\Utility\DateTimeUtility;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;
use Cake\Utility\Security;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Google_Service_Exception;

class MeetApi extends AbstractVideoMeeting
{
    public const REQUEST_ID_LENGTH = 16;

    public const ERROR_INVALID_SECRET_JSON = 'ERROR_INVALID_SECRET_JSON';

    public const API_CONFERENCE_DATA_VERSION_ON = 1;

    public const API_CONFERENCE_SOLUTION_KEY_MEET = 'hangoutsMeet';

    /**
     * @var array
     */
    protected $_defaultConfig = [
        'errorLog' => null,
        'timeZoneName' => null,
        'timeZoneOffset' => null,
        'applicationName' => null,
        'secretJson' => null,
    ];

    /**
     * @var string|null
     */
    protected $calendarId = null;

    /**
     * @var array|null
     */
    protected $apiHistory = null;

    /**
     * @inheritDoc
     */
    protected function initialize()
    {
        $this->apiHistory = [];
    }

    /**
     * @inheritDoc
     */
    public function createMeeting($dateTimeFrom, $dateTimeTo, ?array $options = null)
    {
        $options = (array)$options + [
            'summary' => null,
        ];

        $event = new Google_Service_Calendar_Event([
            'conferenceData' => [
                'createRequest' => [
                    'conferenceSolutionKey' => [
                        'type' => static::API_CONFERENCE_SOLUTION_KEY_MEET,
                    ],
                    'requestId' => Security::randomString(static::REQUEST_ID_LENGTH),
                ],
            ],
        ]);
        $event->setStart($this->createApiDateTime($dateTimeFrom));
        $event->setEnd($this->createApiDateTime($dateTimeTo));
        $event->setSummary($options['summary']);

        $result = $this->sendCreateMeetingApi($event);
        if (!isset($result)) {
            return null;
        }

        $this->apiHistory[] = [
            'type' => 'create',
            'id' => Hash::get($result, 'id'),
        ];

        return ['event' => $result];
    }

    /**
     * @inheritDoc
     */
    public function updateMeeting($id, $dateTimeFrom, $dateTimeTo, ?array $options = null)
    {
        $options = (array)$options + [
            'summary' => null,
        ];

        $data = $this->sendGetMeetingApi($id);
        if (!isset($data)) {
            return null;
        }

        $event = clone $data;
        $event->setStart($this->createApiDateTime($dateTimeFrom));
        $event->setEnd($this->createApiDateTime($dateTimeTo));
        $event->setSummary($options['summary']);

        $result = $this->sendUpdateMeetingApi($id, $event);
        if (!isset($result)) {
            return null;
        }

        $this->apiHistory[] = [
            'type' => 'update',
            'id' => $id,
            'data' => $data,
        ];

        return ['event' => $result];
    }

    /**
     * @inheritDoc
     */
    public function deleteMeeting($id, ?array $options = null)
    {
        $data = $this->sendGetMeetingApi($id);
        if (!isset($data)) {
            return null;
        }

        $result = $this->sendDeleteMeetingApi($id);
        if (!isset($result)) {
            return null;
        }

        $this->apiHistory[] = [
            'type' => 'delete',
            'id' => $id,
            'data' => $data,
        ];

        return ['event' => $result];
    }

    /**
     * @inheritDoc
     */
    public function rollbackApi()
    {
        $apiHistory = array_reverse((array)$this->apiHistory);
        $this->apiHistory = null;

        $result = null;
        foreach ($apiHistory as $history) {
            $apiResult = null;
            if ($history['type'] === 'create') {
                $apiResult = $this->sendDeleteMeetingApi($history['id']);
            } elseif ($history['type'] === 'update') {
                $event = $this->sendGetMeetingApi($history['id']);
                if (!isset($event)) {
                    return null;
                }
                $event->setStart($history['data']->getStart());
                $event->setEnd($history['data']->getEnd());
                $event->setSummary($history['data']->getSummary());

                $apiResult = $this->sendUpdateMeetingApi($history['id'], $event);
            } elseif ($history['type'] === 'delete') {
                $event = new Google_Service_Calendar_Event([
                    'conferenceData' => [
                        'createRequest' => [
                            'conferenceSolutionKey' => [
                                'type' => static::API_CONFERENCE_SOLUTION_KEY_MEET,
                            ],
                            'requestId' => Security::randomString(static::REQUEST_ID_LENGTH),
                        ],
                    ],
                ]);
                $event->setStart($history['data']->getStart());
                $event->setEnd($history['data']->getEnd());
                $event->setSummary($history['data']->getSummary());

                $apiResult = $this->sendCreateMeetingApi($event);
            }
            if (!isset($apiResult)) {
                return null;
            }

            $history['result'] = ['event' => $apiResult];
            $result[] = $history;
        }

        return $result;
    }

    /**
     * カレンダーIDを取得
     *
     * @return string|null
     */
    public function getCalendarId()
    {
        return $this->calendarId;
    }

    /**
     * カレンダーIDを設定
     *
     * @param string $calendarId ID
     * @return void
     */
    public function setCalendarId(string $calendarId)
    {
        $this->calendarId = $calendarId;
    }

    /**
     * ビデオ会議取得のAPI送信
     *
     * @param string $id ID
     * @return \Google_Service_Calendar_Event|null
     */
    protected function sendGetMeetingApi($id)
    {
        $service = $this->createApiService();
        if (!isset($service)) {
            return null;
        }

        try {
            $result = $service->events->get($this->getCalendarId(), $id);
        } catch (Google_Service_Exception $e) {
            $this->writeExceptionLog($e);

            return null;
        }

        return $result;
    }

    /**
     * ビデオ会議作成のAPI送信
     *
     * @param \Google_Service_Calendar_Event $event イベント
     * @return \Google_Service_Calendar_Event|null
     */
    protected function sendCreateMeetingApi($event)
    {
        $service = $this->createApiService();
        if (!isset($service)) {
            return null;
        }

        try {
            $result = $service->events->insert($this->getCalendarId(), $event, [
                'conferenceDataVersion' => static::API_CONFERENCE_DATA_VERSION_ON,
            ]);
        } catch (Google_Service_Exception $e) {
            $this->writeExceptionLog($e);

            return null;
        }

        return $result;
    }

    /**
     * ビデオ会議更新のAPI送信
     *
     * @param string $id ID
     * @param \Google_Service_Calendar_Event $event イベント
     * @return \Google_Service_Calendar_Event|null
     */
    protected function sendUpdateMeetingApi($id, $event)
    {
        $service = $this->createApiService();
        if (!isset($service)) {
            return null;
        }

        try {
            $result = $service->events->update($this->getCalendarId(), $id, $event);
        } catch (Google_Service_Exception $e) {
            $this->writeExceptionLog($e);

            return null;
        }

        return $result;
    }

    /**
     * ビデオ会議削除のAPI送信
     *
     * @param string $id ID
     * @return \Google_Service_Calendar_Event|null
     */
    protected function sendDeleteMeetingApi($id)
    {
        $service = $this->createApiService();
        if (!isset($service)) {
            return null;
        }

        try {
            $result = $service->events->delete($this->getCalendarId(), $id);
        } catch (Google_Service_Exception $e) {
            $this->writeExceptionLog($e);

            return null;
        }

        return $result;
    }

    /**
     * APIのインスタンスを生成
     *
     * @return \Google_Service_Calendar|null
     */
    protected function createApiService()
    {
        if (!static::isValidSecretJson($this->getConfig('secretJson'))) {
            $this->writeLog(static::ERROR_INVALID_SECRET_JSON);

            return null;
        }

        $client = new Google_Client();
        $client->setApplicationName($this->getConfig('applicationName'));
        $client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
        $client->setAuthConfig($this->getConfig('secretJson'));

        $service = new Google_Service_Calendar($client);

        return $service;
    }

    /**
     * API用の日時を生成
     *
     * @param \DateTimeInterface|string $dateTime 日時
     * @return \Google_Service_Calendar_EventDateTime
     */
    protected function createApiDateTime($dateTime)
    {
        $dateTime = DateTimeUtility::convertToDateTimeObject($dateTime);
        if (!isset($dateTime)) {
            throw new CakeException();
        }

        $calendarDateTime = new Google_Service_Calendar_EventDateTime([
            'dateTime' => $dateTime->format('Y-m-d\\TH:i:s' . $this->getConfig('timeZoneOffset')),
            'timeZone' => $this->getConfig('timeZoneName'),
        ]);

        return $calendarDateTime;
    }

    /**
     * 例外をログへ記録
     *
     * @param \Throwable $exception 例外
     * @return void
     */
    protected function writeExceptionLog($exception)
    {
        $this->writeLog($exception->__toString());
    }

    /**
     * SecretJson の値を検証
     *
     * @param array $secretJson SecretJson
     * @return bool
     */
    public static function isValidSecretJson(array $secretJson)
    {
        if (!isset($secretJson['client_id']) || !isset($secretJson['client_email'])) {
            return false;
        }

        $key = 'web';
        if (isset($secretJson['installed'])) {
            $key = 'installed';
        }

        if (isset($secretJson['type']) && $secretJson['type'] === 'service_account') {
            if (!isset($secretJson['client_email']) || !isset($secretJson['private_key'])) {
                return false;
            }
        } elseif (isset($secretJson[$key])) {
            if (
                !isset($secretJson[$key]['client_secret'])
                || isset($secretJson[$key]['redirect_uris']) && !isset($secretJson[$key]['redirect_uris'][0])
            ) {
                return false;
            }
        } else {
            if (
                !isset($secretJson['client_secret'])
                || isset($secretJson['redirect_uris']) && !isset($secretJson['redirect_uris'][0])
            ) {
                return false;
            }
        }

        return true;
    }
}
