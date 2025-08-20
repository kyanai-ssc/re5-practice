<?php
declare(strict_types=1);

namespace App\Utility\SmartLock;

use App\Exception\SmartLockException;
use App\Model\Entity\Reservation;
use App\Utility\CommonData\CommonDataTrait;
use Cake\Console\Arguments;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\Client;
use Cake\Http\Client\Request;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Hash;
use Psr\Log\LogLevel;

/**
 * スマートロック連携の基底クラス
 */
abstract class AbstractSmartLock
{
    use CommonDataTrait;
    use InstanceConfigTrait;
    use LocatorAwareTrait;
    use MailerAwareTrait;

    /**
     * コンテンツタイプ: urlencoded
     *
     * @var string
     */
    public const CONTENT_TYPE_URLENCODED = 'application/x-www-form-urlencoded';

    /**
     * コンテンツタイプ: json
     *
     * @var string
     */
    public const CONTENT_TYPE_JSON = 'application/json';

    /**
     * コンテンツタイプ: text
     *
     * @var string
     */
    public const CONTENT_TYPE_TEXT = 'text/html';

    /**
     * Accept
     *
     * @var string|null
     */
    public const API_ACCEPT = '';

    /**
     * grant_type
     *
     * @var string
     */
    public const API_GRANT_TYPE = '';

    /**
     * API URL: CODE取得
     *
     * @var string
     */
    public const API_URL_OAUTH_CODE = '/oauth/authorize/';

    /**
     * API URL: トークン取得
     *
     * @var string
     */
    public const API_URL_OAUTH_TOKEN = '/oauth/token';

    /**
     * 再試行する HTTP ステータスコード (アクセストークン不正)
     *
     * @var int[]
     * @see https://developers.akerun.com/
     */
    public const RETRY_STATUS_CODE = [];

    /**
     * config
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * エラーメール送信の有無
     *
     * @var bool
     */
    protected $errorMailSendFlg = true;

    /**
     * 連携済か
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return bool
     */
    abstract public function isLinked(Reservation $reservation): bool;

    /**
     * スケジュール登録
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return bool
     */
    abstract public function addSchedule(Reservation $reservation): bool;

    /**
     * スケジュール変更
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @param \App\Model\Entity\Reservation $oldReservation 変更前予約情報
     * @return bool
     */
    abstract public function changeSchedule(Reservation $reservation, Reservation $oldReservation): bool;

    /**
     * スケジュール削除
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return bool
     */
    abstract public function deleteSchedule(Reservation $reservation): bool;

    /**
     * コマンドより実行する初期設定処理
     *
     * @param \Cake\Console\Arguments $args execute() で引数となている $args
     * @param string $secretKey コマンドの対話上で入力される秘密鍵
     * @return void
     * @throws \Exception
     */
    abstract public function initialSetting(Arguments $args, string $secretKey): void;

    /**
     * エラーメール送信
     *
     * @param \App\Model\Entity\Reservation $reservation 変更前 or 変更後の予約情報
     * @param int $errorMailType エラータイプ
     * @param \App\Model\Entity\Reservation|null $anotherReservation $reservation とは逆の予約情報 ($reservationが変更前なら変更後の予約情報)
     * @return void
     */
    abstract protected function sendErrorMail(
        Reservation $reservation,
        int $errorMailType,
        ?Reservation $anotherReservation = null
    ): void;

    /**
     * Constructor.
     *
     * @param array $options オプション
     */
    public function __construct(array $options = [])
    {
        $options = $this->initialize($options);
        $this->setConfig($options);
    }

    /**
     * 初期化処理
     *
     * @param array $options オプション
     * @return array $options
     */
    protected function initialize(array $options): array
    {
        return $options;
    }

    /**
     * ロック取得
     *
     * 別プロセスのトークン取得が被らないようにロックを取得する
     *
     * @return void
     */
    public function getLock(): void
    {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');
        /** @var \App\Model\Table\SmartLocksTable $smartLocksTable */
        $smartLocksTable = $this->getTableLocator()->get('SmartLocks');
        $smartLocksTable->getLockForSmartLock($smartLock->id);
    }

    /**
     * ロック開放
     *
     * @return void
     */
    public function releaseLock(): void
    {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');
        /** @var \App\Model\Table\SmartLocksTable $smartLocksTable */
        $smartLocksTable = $this->getTableLocator()->get('SmartLocks');
        $smartLocksTable->releaseLockForSmartLock($smartLock->id);
    }

    /**
     * 連携するかどうか
     *
     * 予約枠.デバイスキーに値が設定されていれば true
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return bool
     */
    public function canLinkage(Reservation $reservation): bool
    {
        if (!$reservation->get('event') || !$reservation->event->get('event_smart_lock')) {
            $reservation->setSmartLockInfo();
        }

        return $reservation->get('event')
            && $reservation->event->get('event_smart_lock')
            && $reservation->event->event_smart_lock->get('smart_lock_device_key');
    }

    /**
     * トークン取得
     *
     * @return string トークン
     */
    public function getToken(): string
    {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');
        // commonData は過去の場合があるのでトークンの有効期限は現在時刻での判定とする
        if ($smartLock->get('expires') && FrozenTime::now() < $smartLock->get('expires')) {
            // 有効期限内ならそのまま利用する
            return $smartLock->get('token');
        }

        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');
        $data = [
            'client_id' => $smartLock->get('client_id'),
            'client_secret' => $smartLock->get('client_secret'),
            'grant_type' => static::API_GRANT_TYPE,
        ];
        if ($smartLock->get('refresh_token')) {
            $data['refresh_token'] = $smartLock->get('refresh_token');
        }
        $result = $this->sendTokenApi(
            $this->getConfig('env.authUrl') . static::API_URL_OAUTH_TOKEN,
            Request::METHOD_POST,
            static::CONTENT_TYPE_URLENCODED,
            $data
        );

        $token = (string)Hash::get($result, 'access_token', '');
        if (!$token) {
            throw new CakeException();
        }

        // 取得したトークンでスマートロック設定を更新する
        $updateData = [
            'token' => $token,
            'expires' => FrozenTime::createFromTimestamp(
                (int)Hash::get($result, 'created_at', 0) + (int)Hash::get($result, 'expires_in', 0),
                Configure::read('App.defaultTimezone')
            ),
            'refresh_token' => Hash::get($result, 'refresh_token'),
        ];
        /** @var \App\Model\Table\SmartLocksTable $smartLocksTable */
        $smartLocksTable = $this->getTableLocator()->get('SmartLocks');
        $smartLock = $smartLocksTable->patchEntity($smartLock, $updateData);
        $smartLocksTable->save($smartLock);

        // 新しい設定内容で config を更新する
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $smartLocksTable->getData();
        $this->setConfig('smartLock', $smartLock);

        return $token;
    }

    /**
     * トークン取得 API の送信
     *
     * @param string $url URL
     * @param string $method HTTPメソッド
     * @param string $contentType コンテンツタイプ
     * @param array|null $data データ
     * @return array
     */
    protected function sendTokenApi(
        string $url,
        string $method,
        string $contentType,
        ?array $data
    ): array {
        $response = $this->sendApi($url, $method, $contentType, $data);

        $body = $response->getStringBody();
        if (!$response->isOk()) {
            // エラーログ
            $this->writeLog(sprintf('http status = %s, response_body = %s', $response->getStatusCode(), $body));

            throw new CakeException();
        }

        if ((string)$body === '') {
            return [];
        }

        return json_decode($body, true);
    }

    /**
     * 連携 API の送信
     *
     * @param string $url URL
     * @param string $method HTTPメソッド
     * @param string $contentType コンテンツタイプ
     * @param array|null $data データ
     * @param int $sendMailType エラー時: メールタイプ
     * @param \App\Model\Entity\Reservation $reservation エラー時: 予約情報
     * @param \App\Model\Entity\Reservation|null $anotherReservation $reservation とは逆の予約情報 ($reservationが変更前なら変更後の予約情報)
     * @param array $permissionStatusCodes 許可したいステータス
     * @return array
     */
    protected function sendProcessApi(
        string $url,
        string $method,
        string $contentType,
        ?array $data,
        int $sendMailType,
        Reservation $reservation,
        ?Reservation $anotherReservation = null,
        array $permissionStatusCodes = []
    ): array {
        $tokenRetryCount = (int)$this->getConfig('env.tokenRetryCount');
        $count = 1;
        $response = null;
        while ($count <= $tokenRetryCount) {
            $token = $this->getToken();

            $response = $this->sendApi(
                $url,
                $method,
                $contentType,
                $data,
                $token
            );

            if (!in_array($response->getStatusCode(), static::RETRY_STATUS_CODE)) {
                // 再試行以外のステータスならループを抜ける
                break;
            }

            $count++;
            // 繰り返しごとにディレイを入れる
            sleep((int)$this->getConfig('env.delaySecond'));
        }

        if (!$response) {
            return [];
        }

        $body = $response->getStringBody();
        $statusCheck = in_array($response->getStatusCode(), $permissionStatusCodes);
        if (!($response->isOk() || $statusCheck)) {
            // メール送信
            $this->sendErrorMail($reservation, $sendMailType, $anotherReservation);
            // エラーログ
            $this->writeLog(sprintf('http status = %s, response_body = %s', $response->getStatusCode(), $body));

            throw new SmartLockException();
        }

        if ((string)$body === '') {
            return [];
        }

        $decodeBody = json_decode($body, true);

        if ($statusCheck) {
            // isOk()以外で許可されたステータスの場合、返り値にステータスを含める。
            $decodeBody['statusCode'] = $response->getStatusCode();
        }

        return $decodeBody;
    }

    /**
     * API の送信
     *
     * @param string $url URL
     * @param string $method HTTPメソッド
     * @param string $contentType コンテンツタイプ
     * @param array|null $data データ
     * @param string|null $accessToken アクセストークン
     * @return \Cake\Http\Client\Response レスポンス
     */
    protected function sendApi(
        string $url,
        string $method,
        string $contentType,
        ?array $data = [],
        ?string $accessToken = ''
    ): Client\Response {
        $client = new Client();

        $options = [];
        $headers = [];
        if ($contentType) {
            $headers['Content-Type'] = $contentType;
        }

        if (static::API_ACCEPT) {
            $headers['Accept'] = static::API_ACCEPT;
        } else {
            if ($contentType) {
                $options['type'] = $contentType;
            }
        }

        // アクセストークンがあるときはヘッダーに追記
        if (isset($accessToken)) {
            $headers['Authorization'] = 'Bearer ' . $accessToken;
        }
        if ($headers) {
            $options['headers'] = $headers;
        }

        // コンテンツタイプにjson指定の場合
        if ($contentType === static::CONTENT_TYPE_JSON) {
            $data = json_encode($data);
        }

        switch ($method) {
            case Request::METHOD_DELETE:
                $response = $client->delete($url, $data, $options);
                break;
            case Request::METHOD_PUT:
                $response = $client->put($url, $data, $options);
                break;
            case Request::METHOD_GET:
                if (!is_array($data)) {
                    $data = [];
                }
                $response = $client->get($url, $data, $options);
                break;
            default:
                $response = $client->post($url, $data, $options);
                break;
        }

        return $response;
    }

    /**
     * ログへメッセージを記録
     *
     * @param string $message メッセージ
     * @param int|string|null $level レベル
     * @return void
     */
    protected function writeLog(string $message, $level = null): void
    {
        if (!isset($level)) {
            $level = LogLevel::ERROR;
        }
        Log::write($level, $message, ['scope' => 'smart_lock']);
    }

    /**
     * バッファを引いた予約開始時間を返却
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return \Cake\I18n\FrozenTime バッファを引いた開始時間
     */
    protected function getDateTimeFrom(Reservation $reservation): FrozenTime
    {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');

        return (new FrozenTime($reservation->getUsageTimestampFrom()))
            ->subMinutes((int)$smartLock->buffer);
    }

    /**
     * バッファを足した予約終了時間を返却
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return \Cake\I18n\FrozenTime バッファを足した終了時間
     */
    protected function getDateTimeTo(Reservation $reservation): FrozenTime
    {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');

        return (new FrozenTime($reservation->getUsageTimestampTo()))
            ->addMinutes((int)$smartLock->buffer);
    }

    /**
     * エラーメール送信フラグの変更
     *
     * @param bool $sendFlg エラーメール送信判定フラグ
     * @return void
     */
    public function setErrorMailSendFlg(bool $sendFlg)
    {
        $this->errorMailSendFlg = $sendFlg;
    }
}
