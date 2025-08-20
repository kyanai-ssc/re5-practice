<?php
declare(strict_types=1);

namespace App\Utility\VideoMeeting;

use App\Utility\DateTimeUtility;
use App\Utility\OAuth\Token;
use Cake\Core\Exception\CakeException;
use Cake\Event\Event;
use Cake\Http\Client;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;

class ZoomApi extends AbstractVideoMeeting
{
    /**
     * 認証タイプ：OAuth
     */
    public const AUTHORIZATION_TYPE_OAUTH = 1;
    /**
     * 認証タイプ：Basic
     */
    public const AUTHORIZATION_TYPE_BASIC = 2;
    /**
     * 認証タイプ：JWT
     */
    public const AUTHORIZATION_TYPE_JWT = 3;

    public const JWT_TOKEN_EXPIRES = 3600;

    public const ERROR_TOKEN_IS_EMPTY = 'ERROR_TOKEN_IS_EMPTY';
    public const ERROR_DIFFERENT_ZOOM_USER = 'ERROR_DIFFERENT_ZOOM_USER: %s %s';

    public const API_URL_GET_ACCESS_TOKEN = '/oauth/token';
    public const API_URL_GET_USER = '/users/%s';
    public const API_URL_GET_MEETING = '/meetings/%s';
    public const API_URL_CREATE_MEETING = '/users/%s/meetings';
    public const API_URL_UPDATE_MEETING = '/meetings/%s';
    public const API_URL_DELETE_MEETING = '/meetings/%s';

    public const API_GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    public const API_GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

    public const API_USER_ID_OAUTH = 'me';

    public const API_MEETING_TYPE_SCHEDULED = 2;

    /**
     * @var array
     */
    protected $_defaultConfig = [
        'errorLog' => null,
        'timeZone' => null,
        'scheme' => null,
        'host' => null,
        'hostOAuth' => null,
        'baseUrl' => null,
        'sslVerify' => true,
        'apiKey' => null,
        'apiSecret' => null,
        'clientId' => null,
        'clientSecret' => null,
        'redirectUri' => null,
    ];

    /**
     * @var string|null
     */
    protected $zoomUserId = null;

    /**
     * @var int|null
     */
    protected $authorizationType = null;

    /**
     * @var \App\Utility\OAuth\Token|null
     */
    protected $token = null;

    /**
     * @var array|null
     */
    protected $apiHistory = null;

    /**
     * @var bool
     */
    protected $canRetry = true;

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
            'topic' => null,
        ];

        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);
        if (is_null($dateTimeFrom) || is_null($dateTimeTo)) {
            throw new CakeException();
        }

        $result = $this->sendCreateMeetingApi([
            'topic' => $options['topic'],
            'start_time' => $this->createApiDateTime($dateTimeFrom),
            'duration' => $dateTimeFrom->diffInMinutes($dateTimeTo),
        ]);
        if (!isset($result)) {
            return null;
        }

        $this->apiHistory[] = [
            'type' => 'create',
            'id' => Hash::get($result, 'id'),
        ];

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function updateMeeting($id, $dateTimeFrom, $dateTimeTo, ?array $options = null)
    {
        $options = (array)$options + [
            'id' => null,
            'topic' => null,
        ];

        $dateTimeFrom = DateTimeUtility::convertToDateTimeObject($dateTimeFrom);
        $dateTimeTo = DateTimeUtility::convertToDateTimeObject($dateTimeTo);
        if (is_null($dateTimeFrom) || is_null($dateTimeTo)) {
            throw new CakeException();
        }

        $data = $this->sendGetMeetingApi($id);
        if (!isset($data)) {
            return null;
        }

        $result = $this->sendUpdateMeetingApi($id, [
            'topic' => $options['topic'],
            'start_time' => $this->createApiDateTime($dateTimeFrom),
            'duration' => $dateTimeFrom->diffInMinutes($dateTimeTo),
        ]);
        if (!isset($result)) {
            return null;
        }

        $this->apiHistory[] = [
            'type' => 'update',
            'id' => $id,
            'data' => [
                'topic' => Hash::get($data, 'topic'),
                'timezone' => Hash::get($data, 'timezone'),
                'start_time' => Hash::get($data, 'start_time'),
                'duration' => Hash::get($data, 'duration'),
            ],
        ];

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function deleteMeeting($id, ?array $options = null)
    {
        $options = (array)$options + [
            'id' => null,
        ];

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
            'data' => [
                'topic' => Hash::get($data, 'topic'),
                'timezone' => Hash::get($data, 'timezone'),
                'start_time' => Hash::get($data, 'start_time'),
                'duration' => Hash::get($data, 'duration'),
            ],
        ];

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function rollbackApi()
    {
        $apiHistory = array_reverse((array)$this->apiHistory);
        $this->apiHistory = null;

        $result = [];
        foreach ($apiHistory as $history) {
            $apiResult = null;
            if ($history['type'] === 'create') {
                $apiResult = $this->sendDeleteMeetingApi($history['id']);
            } elseif ($history['type'] === 'update') {
                $apiResult = $this->sendUpdateMeetingApi($history['id'], $history['data']);
            } elseif ($history['type'] === 'delete') {
                $apiResult = $this->sendCreateMeetingApi($history['data']);
            }
            if (!isset($apiResult)) {
                return null;
            }

            $history['result'] = $apiResult;
            $result[] = $history;
        }

        return $result;
    }

    /**
     * 認証タイプを取得
     *
     * @return int|null
     */
    public function getAuthorizationType()
    {
        return $this->authorizationType;
    }

    /**
     * 認証タイプを設定
     *
     * @param int $authorizationType 認証タイプ
     * @return void
     */
    public function setAuthorizationType(int $authorizationType)
    {
        $this->authorizationType = $authorizationType;
    }

    /**
     * トークンを取得
     *
     * @return \App\Utility\OAuth\Token|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * トークンを設定
     *
     * @param \App\Utility\OAuth\Token $token トークン
     * @return void
     */
    public function setToken(Token $token)
    {
        $this->token = $token;
    }

    /**
     * ZoomユーザーIDを取得
     *
     * @return string|null
     */
    public function getZoomUserId()
    {
        return $this->zoomUserId;
    }

    /**
     * ZoomユーザーIDを設定
     *
     * @param string $zoomUserId ID
     * @return void
     */
    public function setZoomUserId(string $zoomUserId)
    {
        $this->zoomUserId = $zoomUserId;
    }

    /**
     * コードからアクセストークンを取得
     *
     * @param string $code コード
     * @return \App\Utility\OAuth\Token|null
     */
    public function getAccessTokenByCode(string $code)
    {
        $client = $this->createApiClient(static::AUTHORIZATION_TYPE_BASIC);
        if (!isset($client)) {
            throw new CakeException();
        }

        $response = $client->post(static::API_URL_GET_ACCESS_TOKEN, [
            'code' => $code,
            'grant_type' => static::API_GRANT_TYPE_AUTHORIZATION_CODE,
            'redirect_uri' => $this->getConfig('redirectUri'),
        ]);

        if (!$response->isOk()) {
            $this->writeReponseLog($response);

            return null;
        }

        $result = $response->getJson();
        if (
            !is_array($result) || !isset($result['access_token']) || !isset($result['expires_in'])
            || !isset($result['refresh_token'])
        ) {
            $this->writeReponseLog($response);

            return null;
        }

        return new Token(
            $result['access_token'],
            (new FrozenTime())->addSeconds($result['expires_in']),
            $result['refresh_token']
        );
    }

    /**
     * ユーザー情報を取得
     *
     * @return array|null
     */
    public function getZoomUser()
    {
        $client = $this->createApiClient();
        if (!isset($client)) {
            return null;
        }

        $response = $client->get(
            $this->getConfig('baseUrl') . sprintf(static::API_URL_GET_USER, static::API_USER_ID_OAUTH)
        );

        if (!$response->isOk()) {
            $this->writeReponseLog($response);

            // トークン失効の可能性がある場合に再実行
            if ($this->shouldRetry()) {
                $this->canRetry = false;

                return $this->getZoomUser();
            }

            return null;
        }

        $result = $response->getJson();
        if (!is_array($result)) {
            $this->writeReponseLog($response);

            return null;
        }

        return $result;
    }

    /**
     * ビデオ会議取得のAPI送信
     *
     * @param string $id ID
     * @return array|null
     */
    protected function sendGetMeetingApi($id)
    {
        $client = $this->createApiClient();
        if (!isset($client)) {
            return null;
        }

        $client->setConfig('type', 'json');
        $response = $client->get($this->getConfig('baseUrl') . sprintf(static::API_URL_GET_MEETING, $id));

        if (!$response->isOk()) {
            $this->writeReponseLog($response);

            // トークン失効の可能性がある場合に再実行
            if ($this->shouldRetry()) {
                $this->canRetry = false;

                return $this->sendGetMeetingApi($id);
            }

            return null;
        }

        $result = $response->getJson();
        if (!is_array($result)) {
            $this->writeReponseLog($response);

            return null;
        }

        if (Hash::get($result, 'host_email') !== $this->getZoomUserId()) {
            $this->writeLog(sprintf(
                static::ERROR_DIFFERENT_ZOOM_USER,
                $this->getZoomUserId(),
                Hash::get($result, 'host_email')
            ));

            return null;
        }

        return $result;
    }

    /**
     * ビデオ会議作成のAPI送信
     *
     * @param array $data データ
     * @return array|null
     */
    protected function sendCreateMeetingApi($data)
    {
        $client = $this->createApiClient();
        if (!isset($client)) {
            return null;
        }

        $client->setConfig('type', 'json');
        $response = $client->post(
            $this->getConfig('baseUrl') . sprintf(static::API_URL_CREATE_MEETING, $this->getZoomUserId()),
            json_encode($data + [
                'type' => static::API_MEETING_TYPE_SCHEDULED,
                'timezone' => $this->getConfig('timeZone'),
                'settings' => [
                    'host_video' => true,
                    'participant_video' => true,
                ],
            ])
        );

        if (!$response->isOk()) {
            $this->writeReponseLog($response);

            // トークン失効の可能性がある場合に再実行
            if ($this->shouldRetry()) {
                $this->canRetry = false;

                return $this->sendCreateMeetingApi($data);
            }

            return null;
        }

        $result = $response->getJson();
        if (!is_array($result)) {
            $this->writeReponseLog($response);

            return null;
        }

        return $result;
    }

    /**
     * ビデオ会議更新のAPI送信
     *
     * @param string $id ID
     * @param array $data データ
     * @return array|null
     */
    protected function sendUpdateMeetingApi($id, $data)
    {
        $client = $this->createApiClient();
        if (!isset($client)) {
            return null;
        }

        $client->setConfig('type', 'json');
        $response = $client->patch(
            $this->getConfig('baseUrl') . sprintf(static::API_URL_UPDATE_MEETING, $id),
            json_encode($data + [
                'timezone' => $this->getConfig('timeZone'),
            ])
        );

        if (!$response->isOk()) {
            $this->writeReponseLog($response);

            // トークン失効の可能性がある場合に再実行
            if ($this->shouldRetry()) {
                $this->canRetry = false;

                return $this->sendUpdateMeetingApi($id, $data);
            }

            return null;
        }

        return [];
    }

    /**
     * ビデオ会議削除のAPI送信
     *
     * @param string $id ID
     * @return array|null
     */
    protected function sendDeleteMeetingApi($id)
    {
        $client = $this->createApiClient();
        if (!isset($client)) {
            return null;
        }

        $client->setConfig('type', 'json');
        $response = $client->delete($this->getConfig('baseUrl') . sprintf(static::API_URL_UPDATE_MEETING, $id));

        if (!$response->isOk()) {
            $this->writeReponseLog($response);

            // トークン失効の可能性がある場合に再実行
            if ($this->shouldRetry()) {
                $this->canRetry = false;

                return $this->sendDeleteMeetingApi($id);
            }

            return null;
        }

        return [];
    }

    /**
     * API送信用のクライアントを生成
     *
     * @param int|null $authorizationType 認証タイプ
     * @return \Cake\Http\Client|null
     */
    protected function createApiClient($authorizationType = null)
    {
        $authorizationHeader = $this->getAuthorizationHeader($authorizationType);
        if (!isset($authorizationHeader)) {
            return null;
        }

        if ((string)$authorizationType === (string)static::AUTHORIZATION_TYPE_BASIC) {
            $host = $this->getConfig('hostOAuth');
        } else {
            $host = $this->getConfig('host');
        }

        return new Client([
            'adapter' => 'Cake\\Http\\Client\\Adapter\\Curl',
            'host' => $host,
            'scheme' => $this->getConfig('scheme'),
            'ssl_verify_peer' => $this->getConfig('sslVerify'),
            'ssl_verify_peer_name' => $this->getConfig('sslVerify'),
            'headers' => [
                'Authorization' => $authorizationHeader,
            ],
        ]);
    }

    /**
     * 認証ヘッダを取得
     *
     * @param int|null $authorizationType 認証タイプ
     * @return string|null
     */
    protected function getAuthorizationHeader($authorizationType)
    {
        if (!isset($authorizationType)) {
            $authorizationType = $this->getAuthorizationType();
        }

        if ((string)$authorizationType === (string)static::AUTHORIZATION_TYPE_OAUTH) {
            $accessToken = $this->getAccessToken();
            if (!isset($accessToken)) {
                return null;
            }

            return 'Bearer ' . $accessToken;
        } elseif ((string)$authorizationType === (string)static::AUTHORIZATION_TYPE_BASIC) {
            return 'Basic ' . base64_encode(
                sprintf('%s:%s', $this->getConfig('clientId'), $this->getConfig('clientSecret'))
            );
        } elseif ((string)$authorizationType === (string)static::AUTHORIZATION_TYPE_JWT) {
            return 'Bearer ' . $this->createJwtToken();
        } else {
            throw new CakeException();
        }
    }

    /**
     * アクセストークンを取得
     *
     * @return string|null
     */
    protected function getAccessToken()
    {
        $token = $this->getToken();
        if (!isset($token)) {
            $this->writeLog(static::ERROR_TOKEN_IS_EMPTY);

            return null;
        }

        if ($token->hasExpired()) {
            $token = $this->getAccessTokenByRefreshToken();
            if (!isset($token)) {
                return null;
            }
        }

        return $token->getAccessToken();
    }

    /**
     * リフレッシュトークンからアクセストークンを取得
     *
     * @return \App\Utility\OAuth\Token|null
     */
    protected function getAccessTokenByRefreshToken()
    {
        $client = $this->createApiClient(static::AUTHORIZATION_TYPE_BASIC);
        if (!isset($client)) {
            throw new CakeException();
        }

        $token = $this->getToken();
        if (!isset($token)) {
            throw new CakeException();
        }

        $response = $client->post(static::API_URL_GET_ACCESS_TOKEN, [
            'grant_type' => static::API_GRANT_TYPE_REFRESH_TOKEN,
            'refresh_token' => $token->getRefreshToken(),
        ]);

        if (!$response->isOk()) {
            $this->writeReponseLog($response);

            return null;
        }

        $result = $response->getJson();
        if (
            !is_array($result) || !isset($result['access_token']) || !isset($result['expires_in'])
            || !isset($result['refresh_token'])
        ) {
            $this->writeReponseLog($response);

            return null;
        }

        $oldToken = $token;
        $token = new Token(
            $result['access_token'],
            (new FrozenTime())->addSeconds($result['expires_in']),
            $result['refresh_token']
        );
        $this->setToken($token);

        // リフレッシュトークン利用時のイベント
        $event = new Event('Model.ZoomApi.afterUseRefreshToken', $this, [
            'token' => $token,
            'oldToken' => $oldToken,
        ]);
        $this->getEventManager()->dispatch($event);

        return $token;
    }

    /**
     * トークン失効のため再試行するか判定
     *
     * @return bool
     */
    protected function shouldRetry()
    {
        $token = $this->getToken();
        if ((string)$this->getAuthorizationType() !== (string)static::AUTHORIZATION_TYPE_OAUTH || !isset($token)) {
            return false;
        }

        return $this->canRetry && $token->hasExpired();
    }

    /**
     * JWTを生成する
     *
     * @return string
     */
    protected function createJwtToken()
    {
        $expires = new FrozenTime();
        $expires = $expires->addSeconds(static::JWT_TOKEN_EXPIRES);

        $builder = new Builder(new JoseEncoder(), ChainedFormatter::default());
        $builder->expiresAt($expires);
        $builder->issuedBy($this->getConfig('apiKey'));

        $token = $builder->getToken(new Sha256(), InMemory::plainText($this->getConfig('apiSecret')));

        return $token->toString();
    }

    /**
     * API用の日時を生成
     *
     * @param \DateTimeInterface|string $dateTime 日時
     * @return string
     */
    protected function createApiDateTime($dateTime)
    {
        $dateTime = DateTimeUtility::convertToDateTimeObject($dateTime);
        if (!isset($dateTime)) {
            throw new CakeException();
        }

        return $dateTime->format('Y-m-d\\TH:i:s');
    }

    /**
     * レスポンスをログへ記録
     *
     * @param \Cake\Http\Client\Response $response レスポンス
     * @return void
     */
    protected function writeReponseLog($response)
    {
        $lines = [];
        foreach ($response->getHeaders() as $key => $values) {
            $lines[] = $key . ': ' . implode(',', $values);
        }
        $lines[] = $response->getStringBody();

        $this->writeLog(implode("\n", $lines));
    }
}
