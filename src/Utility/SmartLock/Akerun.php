<?php
declare(strict_types=1);

namespace App\Utility\SmartLock;

use App\Exception\SmartLockException;
use App\Locale\Message;
use App\Model\Entity\EventSmartLock;
use App\Model\Entity\FormItem;
use App\Model\Entity\Reservation;
use App\Model\Entity\SmartLock;
use App\Model\Entity\SystemSetting;
use App\Model\Entity\User;
use Cake\Console\Arguments;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Http\Client\Request;
use Cake\Http\Client\Response;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
use Exception;

/**
 * Akerun class.
 */
class Akerun extends AbstractSmartLock
{
    /**
     * 再試行する HTTP ステータスコード (アクセストークン不正)
     *
     * @var int[]
     * @see https://developers.akerun.com/
     */
    public const RETRY_STATUS_CODE = [401];

    /**
     * ユーザーが存在すると判定するステータスコード
     *
     * @var int[]
     * @see https://developers.akerun.com/#show-user
     */
    public const USER_EXISTS_STATUS_CODE = [200];

    /**
     * grant type: アケルンは refresh_token
     *
     * @var string
     */
    public const API_GRANT_TYPE = 'refresh_token';

    /**
     * アクセストークン初期作成の際の grant type: authorization_code
     *
     * @var string
     */
    public const API_GRANT_TYPE_AUTHORIZE = 'authorization_code';

    /**
     * 各種認証時に指定するコールバックのURL
     * Env.smartLock.akerun.redirectUri でURLが指定されている場合はそちらを利用する
     */
    public const API_CALLBACK_URI = '/admin/smart-locks/akerun-callback';

    /**
     * API URL: 合鍵作成
     *
     * @var string
     */
    public const API_URL_CREATE_KEY = '/organizations/%s/keys';

    /**
     * API URL: 合鍵更新
     *
     * @var string
     */
    public const API_URL_UPDATE_KEY = '/organizations/%s/keys/%s';

    /**
     * API URL: 合鍵削除
     *
     * @var string
     */
    public const API_URL_DELETE_KEY = '/organizations/%s/keys/%s';

    /**
     * API URL: ユーザー追加
     *
     * @var string
     */
    public const API_URL_CREATE_USER = '/organizations/%s/users';

    /**
     * API URL: ユーザー取得
     *
     * @var string
     */
    public const API_URL_GET_USER = '/organizations/%s/users';

    /**
     * API URL: ユーザー詳細
     *
     * @var string
     */
    public const API_URL_GET_USER_DETAIL = '/organizations/%s/users/%s';

    /**
     * スケジュール連携時のパラメータ: schedule_type
     *
     * @var string
     */
    protected const SCHEDULE_TYPE = 'temporary';

    /**
     * エラー時の送信メール: 合鍵作成
     *
     * @var int
     */
    protected const SEND_MAIL_CREATE_KEY = 1;

    /**
     * エラー時の送信メール: 合鍵更新
     *
     * @var int
     */
    protected const SEND_MAIL_UPDATE_KEY = 2;

    /**
     * エラー時の送信メール: 予約情報更新
     *
     * @var int
     */
    protected const SEND_MAIL_UPDATE_RESERVATION = 3;

    /**
     * エラー時の送信メール: 合鍵削除
     *
     * @var int
     */
    protected const SEND_MAIL_DELETE_KEY = 4;

    /**
     * エラー時の送信メール: ユーザー登録
     *
     * @var int
     */
    protected const SEND_MAIL_CREATE_USER = 5;

    /**
     * エラー時の送信メール: 会員情報更新
     *
     * @var int
     */
    protected const SEND_MAIL_USER_UPDATE = 6;

    /**
     * アケルンユーザー登録のエラー内容がユーザー重複によるものかを判定する条件
     *
     * @var array
     */
    public const AKERUN_USER_DUPLICATE_CONDITIONS = [
        'HTTP_STATUS' => 403,
        'CODE' => 'not_allowed',
        'MESSAGE' => 'User is already in the organization.',
    ];

    /**
     * アケルンユーザー詳細のエラー内容がuser_not_foundによるものかを判定する条件
     *
     * @var array
     */
    public const AKERUN_USER_NOT_FOUND = [
        'HTTP_STATUS' => 404,
        'CODE' => 'user_not_found',
    ];

    /**
     * アケルンユーザー登録のエラー内容がregistered_mailによるものかを判定する条件
     *
     * @var array
     */
    public const AKERUN_REGISTERED_MAIL = [
        'HTTP_STATUS' => 400,
        'CODE' => 'registered_mail',
    ];

    /**
     * Accept
     *
     * @var string|null
     */
    public const API_ACCEPT = null;

    /**
     * @inheritDoc
     */
    protected function initialize(array $options): array
    {
        $options['env'] = Configure::readOrFail('Env.smartLock.akerun');

        // 設定ファイルで値が指定されていない場合、規定のURIをセットする
        if (!isset($options['env']['redirectUri'])) {
            $options['env']['redirectUri'] = 'https://' . Configure::read('Client.host') . static::API_CALLBACK_URI;
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function isLinked(Reservation $reservation): bool
    {
        if (!$reservation->get('reservation_smart_lock')) {
            $reservation->setSmartLockInfo();
        }

        return $reservation->get('reservation_smart_lock')
            && $reservation->reservation_smart_lock->get('smart_lock_key_id');
    }

    /**
     * @inheritDoc
     */
    public function addSchedule(Reservation $reservation): bool
    {
        if (!$reservation->get('event') || !$reservation->event->get('event_smart_lock')) {
            throw new CakeException();
        }

        // アケルンユーザーID取得
        $akerunUserId = $this->getAkerunUserId($reservation->user_id, $reservation->get('original_user'));
        // 合鍵作成
        $keys = $this->createKey($reservation, $akerunUserId);

        // 予約スマートロック情報更新
        $data = [
            'reservation_id' => $reservation->get('id'),
            'smart_lock_key_id' => Hash::get($keys, 'key.id', ''),
        ];
        if (Hash::get($keys, 'key.key_url.url')) {
            $data['smart_lock_key_url'] = Hash::get($keys, 'key.key_url.url');
        }
        try {
            /** @var \App\Model\Table\ReservationSmartLocksTable $reservationSmartLocksTable */
            $reservationSmartLocksTable = $this->getTableLocator()->get('ReservationSmartLocks');
            $entity = $reservation->get('reservation_smart_lock');
            if ($entity) {
                /** @var \App\Model\Entity\ReservationSmartLock $entity */
                $entity = $reservationSmartLocksTable->patchEntity($entity, $data);
            } else {
                $entity = $reservationSmartLocksTable->newEntity($data);
            }
            $reservation->set('reservation_smart_lock', $entity);
            $reservationSmartLocksTable->save($entity);
        } catch (Exception $e) {
            // メール送信
            $this->sendErrorMail($reservation, static::SEND_MAIL_UPDATE_RESERVATION);
            // エラーログ出力
            // SmartLockException では error.log を出さないので例外のエラーメッセージもログファイルに出力する。
            $this->writeLog($e->getMessage());
            $this->writeLog(sprintf('data = %s', serialize($data)));

            throw new SmartLockException();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function changeSchedule(Reservation $reservation, Reservation $oldReservation): bool
    {
        if (
            !$reservation->get('reservation_smart_lock')
            || !$reservation->get('event')
            || !$reservation->event->get('event_smart_lock')
            || !$oldReservation->get('reservation_smart_lock')
            || !$oldReservation->get('event')
            || !$oldReservation->event->get('event_smart_lock')
        ) {
            throw new CakeException();
        }

        $reservationSmartLock = $reservation->reservation_smart_lock;
        $eventSmartLock = $reservation->event->event_smart_lock;
        $oldEventSmartLock = $oldReservation->event->event_smart_lock;
        $reservationSmartLockUpdateFunction = function ($keys, $reservationSmartLock, $reservation, $oldReservation) {
            // 予約スマートロック情報更新
            $reservationSmartLock->set('smart_lock_key_id', Hash::get($keys, 'key.id', ''));
            $reservationSmartLock->set('smart_lock_key_url', Hash::get($keys, 'key.key_url.url'));
            /** @var \App\Model\Table\ReservationSmartLocksTable $reservationSmartLocksTable */
            $reservationSmartLocksTable = $this->getTableLocator()->get('ReservationSmartLocks');
            try {
                $reservationSmartLocksTable->save($reservationSmartLock);
            } catch (Exception $e) {
                // メール送信
                $this->sendErrorMail($oldReservation, static::SEND_MAIL_UPDATE_RESERVATION, $reservation);
                // エラーログ出力
                // SmartLockException では error.log を出さないので例外のエラーメッセージもログファイルに出力する。
                $this->writeLog($e->getMessage());
                $this->writeLog(sprintf('data = %s', serialize($reservationSmartLock->toArray())));

                throw new SmartLockException();
            }
        };
        if (
            $reservation->get('event_id') !== $oldReservation->get('event_id')
            && $eventSmartLock->get('smart_lock_device_key') !== $oldEventSmartLock->get('smart_lock_device_key')
        ) {
            // 予約枠が変わり、変更前と変更後の予約枠.デバイスキーが異なる場合
            // 合鍵作成
            $akerunUserId = $this->getAkerunUserId($reservation->user_id, $reservation->get('original_user'));
            $keys = $this->createKey($reservation, $akerunUserId, $oldReservation);

            // 予約スマートロック情報更新
            $reservationSmartLockUpdateFunction($keys, $reservationSmartLock, $reservation, $oldReservation);

            // 変更前の合鍵削除
            $this->deleteKey($oldReservation);
        } else {
            // 合鍵更新
            /** @var \App\Model\Entity\SmartLock $smartLock */
            $smartLock = $this->getConfig('smartLock');
            // 開始時間 - バッファ
            $dateTimeFrom = $this->getDateTimeFrom($reservation);
            // 終了時間 ＋ バッファ
            $dateTimeTo = $this->getDateTimeTo($reservation);
            $data = [
                'organization_id' => $smartLock->get('organizations_id'),
                'key_id' => $reservationSmartLock->get('smart_lock_key_id'),
                'schedule_type' => static::SCHEDULE_TYPE,
                'temporary_schedule' => [
                    'start_datetime' => $this->dateTimeFormat($dateTimeFrom),
                    'end_datetime' => $this->dateTimeFormat($dateTimeTo),
                ],
            ];
            $data = $this->setUseKeyUrlParameter($data, $eventSmartLock);
            $keys = $this->sendProcessApi(
                $this->getConfig('env.apiUrl') . sprintf(
                    static::API_URL_UPDATE_KEY,
                    $smartLock->get('organizations_id'),
                    $reservationSmartLock->get('smart_lock_key_id')
                ),
                Request::METHOD_PUT,
                static::CONTENT_TYPE_JSON,
                $data,
                static::SEND_MAIL_UPDATE_KEY,
                $oldReservation
            );

            $reservationSmartLockUpdateFunction($keys, $reservationSmartLock, $reservation, $oldReservation);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteSchedule(Reservation $reservation): bool
    {
        if (!$reservation->get('reservation_smart_lock')) {
            throw new CakeException();
        }

        // 合鍵削除には予約スマートロッククリア前の情報が必要なので複製しておく
        $oldReservation = clone $reservation;
        $oldReservation->reservation_smart_lock = clone $reservation->reservation_smart_lock;

        // 予約スマートロック情報更新
        $reservationSmartLock = $reservation->reservation_smart_lock;
        $reservationSmartLock->set('smart_lock_key_id');
        $reservationSmartLock->set('smart_lock_key_url');
        /** @var \App\Model\Table\ReservationSmartLocksTable $reservationSmartLocksTable */
        $reservationSmartLocksTable = $this->getTableLocator()->get('ReservationSmartLocks');
        try {
            $reservationSmartLocksTable->save($reservationSmartLock);
        } catch (Exception $e) {
            // メール送信
            $this->sendErrorMail($oldReservation, static::SEND_MAIL_UPDATE_RESERVATION);
            // エラーログ出力
            // SmartLockException では error.log を出さないので例外のエラーメッセージもログファイルに出力する。
            $this->writeLog($e->getMessage());
            $this->writeLog(sprintf('data = %s', serialize($oldReservation->reservation_smart_lock->toArray())));

            throw new SmartLockException();
        }

        // 合鍵削除
        $this->deleteKey($oldReservation);

        return true;
    }

    /**
     * ユーザー登録
     *
     * @param \App\Model\Entity\User $user 会員
     * @return string アケルンユーザーID
     */
    public function addUser(User $user): string
    {
        return $this->getAkerunUserId($user->id, $user->get('original_user'));
    }

    /**
     * @inheritDoc
     */
    public function initialSetting(Arguments $args, string $secretKey): void
    {
        /** @var \App\Model\Table\SmartLocksTable $smartLocksTable */
        $smartLocksTable = $this->getTableLocator()->get('SmartLocks');
        $smartLock = $smartLocksTable->getUpsertEntity([
            'type' => SmartLock::TYPE_AKERUN,
            'client_id' => $args->getOption('api-key'),
            'client_secret' => $secretKey,
            'buffer' => $args->getOption('buffer'),
            'organizations_id' => $args->getOption('organizations-id'),
            'token' => null,
            'expires' => null,
            'refresh_token' => null,
        ]);

        // 予期しない原因で保存に失敗した場合、例外をスロー
        if (!$smartLocksTable->save($smartLock)) {
            throw new CakeException('smart_locks save error.');
        }

        // AkerunユーザーIDの顧客項目に説明文が設定されていない場合は初期説明文をセットする
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->fetchTable('FormItems');
        $formItem = $formItemsTable->find('initialSettingForAkerun')->first();
        if (
            $formItem instanceof FormItem
            && !$formItem->has('description')
        ) {
            $formItem->set('description', Configure::readOrFail(
                'Setting.smartLock.akerun.formItemAkerunUserIdDefaultDescription'
            ));
            if (!$formItemsTable->save($formItem, ['checkRules' => false])) {
                throw new CakeException('SmartLockSetting Error: failed to update formItem.');
            }
        }

        // 認証用のURLを出力
        $authorizeUrl = "%s?client_id=%s&redirect_uri=%s&response_type=code\n";
        printf(
            $authorizeUrl,
            $this->getConfig('env.authUrl') . static::API_URL_OAUTH_CODE,
            $args->getOption('api-key'),
            $this->getConfig('env.redirectUri')
        );
    }

    /**
     * 認証コードを元にトークンの取得を行い、DBへ保存する
     *
     * @param array|string|null $code 認証コード
     * @return void
     * @throws \Exception
     */
    public function saveAccessToken($code): void
    {
        if (!is_string($code) || $code === '') {
            throw new CakeException('invalid code.');
        }

        /** @var \App\Model\Table\SmartLocksTable $smartLocksTable */
        $smartLocksTable = $this->getTableLocator()->get('SmartLocks');
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        $smartLocksTable->getConnection()->transactional(function () use (
            $code,
            $smartLocksTable,
            $systemSettingsTable
        ) {
            $smartLock = $smartLocksTable->getData();

            if ($smartLock->has('token')) {
                throw new CakeException('token already exists.');
            }

            $result = $this->sendTokenApi(
                $this->getConfig('env.authUrl') . static::API_URL_OAUTH_TOKEN,
                Request::METHOD_POST,
                static::CONTENT_TYPE_URLENCODED,
                [
                    'grant_type' => static::API_GRANT_TYPE_AUTHORIZE,
                    'client_id' => $smartLock->get('client_id'),
                    'client_secret' => $smartLock->get('client_secret'),
                    'code' => $code,
                    'redirect_uri' => $this->getConfig('env.redirectUri'),
                ]
            );

            // 返却値が空の場合はエラーとする
            $token = (string)Hash::get($result, 'access_token', '');
            if (!$token) {
                throw new CakeException('token api error : access_token is empty.');
            }

            $updateData = [
                'token' => $token,
                'expires' => FrozenTime::createFromTimestamp(
                    (int)Hash::get($result, 'created_at', 0) + (int)Hash::get($result, 'expires_in', 0),
                    Configure::read('App.defaultTimezone')
                ),
                'refresh_token' => Hash::get($result, 'refresh_token'),
            ];
            $smartLock = $smartLocksTable->patchEntity($smartLock, $updateData, ['validate' => false]);

            if (!$smartLocksTable->save($smartLock)) {
                throw new CakeException('smart_locks save error.');
            }

            $systemSetting = $systemSettingsTable->getData();
            $systemSetting->set('smart_lock_use_flg', SystemSetting::SMART_LOCK_USE_FLG_ON);

            if (!$systemSettingsTable->save($systemSetting)) {
                throw new CakeException('system_settings save error.');
            }

            return true;
        });
        $smartLocksTable->deleteCacheData();
        $systemSettingsTable->deleteCacheData();
    }

    /**
     * 合鍵作成
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param string $akerunUserId アケルンユーザーID
     * @param \App\Model\Entity\Reservation|null $oldReservation 変更前予約
     * @return array 鍵情報
     */
    protected function createKey(
        Reservation $reservation,
        string $akerunUserId,
        ?Reservation $oldReservation = null
    ): array {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');

        // 開始時間 - バッファ
        $dateTimeFrom = $this->getDateTimeFrom($reservation);
        // 終了時間 ＋ バッファ
        $dateTimeTo = $this->getDateTimeTo($reservation);

        $eventSmartLock = $reservation->event->event_smart_lock;
        $data = [
            'organization_id' => $smartLock->get('organizations_id'),
            'user_id' => $akerunUserId,
            'akerun_id' => $eventSmartLock->get('smart_lock_device_key'),
            'schedule_type' => static::SCHEDULE_TYPE,
            'temporary_schedule' => [
                'start_datetime' => $this->dateTimeFormat($dateTimeFrom),
                'end_datetime' => $this->dateTimeFormat($dateTimeTo),
            ],
        ];
        $data = $this->setUseKeyUrlParameter($data, $eventSmartLock);

        return $this->sendProcessApi(
            $this->getConfig('env.apiUrl') . sprintf(static::API_URL_CREATE_KEY, $smartLock->get('organizations_id')),
            Request::METHOD_POST,
            static::CONTENT_TYPE_JSON,
            $data,
            static::SEND_MAIL_CREATE_KEY,
            $oldReservation ?: $reservation,
            $reservation
        );
    }

    /**
     * 合鍵削除
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return void
     */
    protected function deleteKey(Reservation $reservation): void
    {
        $reservationSmartLock = $reservation->reservation_smart_lock;
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');

        $this->sendProcessApi(
            $this->getConfig('env.apiUrl') . sprintf(
                static::API_URL_DELETE_KEY,
                $smartLock->get('organizations_id'),
                $reservationSmartLock->get('smart_lock_key_id')
            ),
            Request::METHOD_DELETE,
            static::CONTENT_TYPE_JSON,
            [
                'organization_id' => $smartLock->get('organizations_id'),
                'key_id' => $reservationSmartLock->get('smart_lock_key_id'),
            ],
            static::SEND_MAIL_DELETE_KEY,
            $reservation
        );
    }

    /**
     * @inheritDoc
     */
    protected function sendErrorMail(
        Reservation $reservation,
        int $errorMailType = 0,
        ?Reservation $anotherReservation = null
    ): void {
        if (!$this->errorMailSendFlg) {
            return;
        }
        /** @var \App\Model\Entity\Event $event */
        $event = $reservation->get('event');

        /** @var \App\Model\Table\AdminMailsTable $adminMailsTable */
        $adminMailsTable = $this->getTableLocator()->get('AdminMails');
        // 送信先メールアドレス取得
        $adminMails = $adminMailsTable->find('AutoReplyMail', [
            'inputs' => [
                'label_id' => $event->get('label_id'),
            ],
        ])->all()->combine('id', 'mail')->toArray();

        $smartLockType = 'Akerun';
        $subject = (string)Configure::readOrFail('Setting.mail.errorSubject.SmartLockLinkage');
        if (!$anotherReservation) {
            $anotherReservation = $reservation;
        }
        switch ($errorMailType) {
            case static::SEND_MAIL_CREATE_KEY:
                $subject = sprintf($subject, $smartLockType, '合鍵作成');
                $template = 'smart_lock/error_akerun_create_key';
                break;

            case static::SEND_MAIL_UPDATE_KEY:
                $subject = sprintf($subject, $smartLockType, '合鍵更新');
                $template = 'smart_lock/error_akerun_update_key';
                break;

            case static::SEND_MAIL_UPDATE_RESERVATION:
                $subject = (string)Configure::readOrFail('Setting.mail.errorSubject.SmartLockUpdateReservation');
                $template = 'smart_lock/error_akerun_update_reservation';
                break;

            case static::SEND_MAIL_DELETE_KEY:
                $subject = sprintf($subject, $smartLockType, '合鍵削除');
                $template = 'smart_lock/error_akerun_delete_key';
                break;

            default:
                throw new CakeException();
        }

        // 開始時間 - バッファ
        $dateTimeFrom = $this->getDateTimeFrom($reservation);
        // 終了時間 ＋ バッファ
        $dateTimeTo = $this->getDateTimeTo($reservation);

        $info = [
            'reservation' => $reservation,
            'anotherReservation' => $anotherReservation,
            'dateTimeFrom' => $dateTimeFrom,
            'dateTimeTo' => $dateTimeTo,
            'smartLock' => $this->getConfig('smartLock'),
            'afterRedirectPaymentFlg' => $reservation->get('after_redirect_payment_flg'),
            'smartLockAddReservationFlg' => false,
        ];
        if ($reservation->get('smart_lock_add_reservation_flg')) {
            $info['smartLockAddReservationFlg'] = true;
        }

        // メール送信
        $this->getMailer('Admin')->send('smartLockErrorMail', [
            $adminMails,
            $info,
            $subject,
            $template,
        ]);
    }

    /**
     * アケルン連携用のフォーマットに変換する
     *
     * ISO-8601 形式だと YYYY-MM-DDTHH:MI:SS+00:00 だが、
     * アケルン側は秒を抜いた YYYY-MM-DDTHH:MI+00:00 を要求してくるので、秒を抜いた値に変換する
     *
     * @param \Cake\I18n\FrozenTime $datetime 変換したい時間
     * @return string YYYY-MM-DDTHH:MI+00:00
     */
    protected function dateTimeFormat(FrozenTime $datetime): string
    {
        $formatted = $datetime->format(FrozenTime::ATOM);
        $position = (int)strpos($formatted, '+');

        return mb_substr($formatted, 0, $position - 3) . mb_substr($formatted, $position);
    }

    /**
     * アケルンユーザー ID を取得する
     *
     * @param int $userId 会員ID
     * @param \Cake\Datasource\EntityInterface|null $originalUser save前の会員entity
     * @return string アケルンユーザーID
     */
    protected function getAkerunUserId(int $userId, $originalUser = null): string
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        /** @var \App\Model\Entity\User $user */
        $user = $usersTable->find('SmartLock')
            ->where(['Users.id' => $userId])
            ->firstOrFail();
        if (
            $user->has('user_smart_lock') && $user->get('user_smart_lock')->smart_lock_user_id
            && isset($originalUser)
            && $originalUser->has('user_smart_lock')
            && $originalUser->get('user_smart_lock')->smart_lock_user_id
            && $user->get('user_smart_lock')->smart_lock_user_id
                === $originalUser->get('user_smart_lock')->smart_lock_user_id
        ) {
            return $user->get('user_smart_lock')->smart_lock_user_id;
        }

        // 新規ユーザー追加
        $akerunUser = $this->createUser($user, $originalUser);
        $akerunUserId = Hash::get($akerunUser, 'akerun_user_id', '');
        $data = [
            'user_id' => $user->get('id'),
            'smart_lock_user_id' => $akerunUserId,
        ];
        try {
            /** @var \App\Model\Table\UserSmartLocksTable $userSmartLocksTable */
            $userSmartLocksTable = $this->getTableLocator()->get('UserSmartLocks');
            $entity = $user->get('user_smart_lock');
            if ($entity) {
                /** @var \App\Model\Entity\UserSmartLock $entity */
                $entity = $userSmartLocksTable->patchEntity($entity, $data);
            } else {
                $entity = $userSmartLocksTable->newEntity($data);
            }
            $userSmartLocksTable->save($entity);
        } catch (Exception $e) {
            // メール送信
            $this->sendUserErrorMail(static::SEND_MAIL_USER_UPDATE, $user, $akerunUser);
            // エラーログ出力
            // SmartLockException では error.log を出さないので例外のエラーメッセージもログファイルに出力する。
            $this->writeLog($e->getMessage());
            $this->writeLog(sprintf('data = %s', serialize($data)));

            throw new SmartLockException();
        }

        return $akerunUserId;
    }

    /**
     * アケルンへユーザー登録
     *
     * @param \App\Model\Entity\User $user 会員情報
     * @param \Cake\Datasource\EntityInterface|null $originalUser save前の会員entity
     * @return array アケルンユーザー情報
     */
    protected function createUser(User $user, $originalUser = null): array
    {
        $this->getToken();

        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');
        $data = [
            'organization_id' => $smartLock->get('organizations_id'),
            'user_name' => $this->getUserName($user),
        ];
        if ($smartLock->get('app_use_flg') && $user->get('mail')) {
            $data['user_mail'] = $user->get('mail');
        }

        // ユーザー登録は複数のAPIを実行するため sendProcessApi は呼ばずに API 連携する
        if ($user->get('user_smart_lock') && $user->user_smart_lock->smart_lock_user_id) {
            $tokenRetryCount = (int)$this->getConfig('env.tokenRetryCount');
            $count = 1;
            $responseForError = null;
            while ($count <= $tokenRetryCount) {
                $detailResponse = $this->getUserDetail($user->user_smart_lock->smart_lock_user_id);
                $responseForError = $detailResponse;
                if ($detailResponse->isOk()) {
                    // ユーザー詳細が取得できた場合は連携しない
                    $data['akerun_user_id'] = $user->user_smart_lock->smart_lock_user_id;

                    return $data;
                } elseif ($detailResponse->getStatusCode() === static::AKERUN_USER_NOT_FOUND['HTTP_STATUS']) {
                    // user_not_found の場合は既存ユーザー追加を実行
                    $body = json_decode($detailResponse->getStringBody(), true);
                    if (is_array($body) && Hash::get($body, 'code', '') === static::AKERUN_USER_NOT_FOUND['CODE']) {
                        $addResponse = $this->addExistingUser($user->user_smart_lock->smart_lock_user_id);
                        $responseForError = $addResponse;
                        if ($addResponse->isOk()) {
                            // 登録OKの場合はIDを返す
                            $data['akerun_user_id'] = $user->user_smart_lock->smart_lock_user_id;

                            return $data;
                        } elseif ($addResponse->getStatusCode() === static::AKERUN_USER_NOT_FOUND['HTTP_STATUS']) {
                            // user_not_found の場合はエラーメッセージ表示
                            $body = json_decode($addResponse->getStringBody(), true);
                            if (
                                is_array($body)
                                && Hash::get($body, 'code', '') === static::AKERUN_USER_NOT_FOUND['CODE']
                            ) {
                                $this->handleCreateUserError($data, $user, $addResponse->getStatusCode(), $body);

                                throw new SmartLockException((string)__(Message::ERROR_NOT_FOUND_AKERUN_USER));
                            }
                        }
                    }
                }

                if (!in_array($responseForError->getStatusCode(), static::RETRY_STATUS_CODE)) {
                    // 再試行以外のステータスならループを抜ける
                    break;
                }

                $count++;
                // 繰り返しごとにディレイを入れる
                sleep((int)$this->getConfig('env.delaySecond'));
            }

            if (!$responseForError) {
                return [];
            }

            $body = json_decode($responseForError->getStringBody(), true);
            $this->handleCreateUserError($data, $user, $responseForError->getStatusCode(), $body);

            throw new SmartLockException();
        } else {
            $tokenRetryCount = (int)$this->getConfig('env.tokenRetryCount');
            $count = 1;
            $response = null;
            $akerunUserDuplicate = false;
            while ($count <= $tokenRetryCount) {
                $akerunUserDuplicate = false;

                $response = $this->addNewUser($data);

                if ($response->getStatusCode() === static::AKERUN_REGISTERED_MAIL['HTTP_STATUS']) {
                    $body = json_decode($response->getStringBody(), true);
                    if (is_array($body) && Hash::get($body, 'code', '') === static::AKERUN_REGISTERED_MAIL['CODE']) {
                        // registered_mail の場合はエラーメッセージ表示
                        if (isset($originalUser) && $originalUser->get('forReservation')) {
                            $this->handleCreateUserError($data, $user, $response->getStatusCode(), $body);

                            throw new SmartLockException((string)__(Message::ERROR_AKERUN_REGISTERED_MAIL_RESERVATION));
                        } else {
                            $this->handleCreateUserError($data, $user, $response->getStatusCode(), $body);

                            throw new SmartLockException((string)__(Message::ERROR_AKERUN_REGISTERED_MAIL));
                        }
                    }
                }

                if ($response->getStatusCode() === static::AKERUN_USER_DUPLICATE_CONDITIONS['HTTP_STATUS']) {
                    $body = json_decode($response->getStringBody(), true);
                    if (
                        is_array($body)
                        && Hash::get($body, 'code', '')
                            === static::AKERUN_USER_DUPLICATE_CONDITIONS['CODE']
                        && Hash::get($body, 'message', '')
                            === static::AKERUN_USER_DUPLICATE_CONDITIONS['MESSAGE']
                    ) {
                        $akerunUserDuplicate = true;
                        // 既に登録済みのエラーであればユーザー ID を取得する
                        $response = $this->getUserList([
                            'user_mail' => $user->get('mail'),
                        ]);
                    }
                }

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

            $akerunUserId = '';
            $body = json_decode($response->getStringBody(), true);
            if (is_array($body)) {
                if ($akerunUserDuplicate) {
                    $akerunUserId = (string)Hash::get($body, 'users.0.id', '');
                } else {
                    $akerunUserId = (string)Hash::get($body, 'user.id', '');
                }
            } else {
                $body = [];
            }

            if (!$response->isOk() || !$akerunUserId) {
                $this->handleCreateUserError($data, $user, $response->getStatusCode(), $body);

                throw new SmartLockException();
            }

            $data['akerun_user_id'] = $akerunUserId;

            return $data;
        }
    }

    /**
     * ユーザー一覧APIの実行
     *
     * @param array $conditions 検索条件
     * @return \Cake\Http\Client\Response
     */
    protected function getUserList(array $conditions = []): Response
    {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');

        return $this->sendApi(
            $this->getConfig('env.apiUrl') . sprintf(
                static::API_URL_GET_USER,
                $smartLock->get('organizations_id')
            ),
            Request::METHOD_GET,
            static::CONTENT_TYPE_TEXT,
            $conditions,
            $this->getToken()
        );
    }

    /**
     * ユーザー詳細APIの実行
     *
     * @param string $akerunUserId AkerunユーザーのID
     * @return \Cake\Http\Client\Response
     */
    protected function getUserDetail(string $akerunUserId): Response
    {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');

        return $this->sendApi(
            $this->getConfig('env.apiUrl') . sprintf(
                static::API_URL_GET_USER_DETAIL,
                $smartLock->get('organizations_id'),
                $akerunUserId
            ),
            Request::METHOD_GET,
            static::CONTENT_TYPE_JSON,
            [],
            $this->getToken()
        );
    }

    /**
     * 新規ユーザー追加APIの実行
     *
     * @param array $userData ユーザーの情報
     * @return \Cake\Http\Client\Response
     */
    protected function addNewUser(array $userData = []): Response
    {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');

        return $this->sendApi(
            $this->getConfig('env.apiUrl') . sprintf(
                static::API_URL_CREATE_USER,
                $smartLock->get('organizations_id')
            ),
            Request::METHOD_POST,
            static::CONTENT_TYPE_JSON,
            $userData + [
                'organization_id' => $smartLock->get('organizations_id'),
            ],
            $this->getToken()
        );
    }

    /**
     * 既存ユーザー追加APIの実行
     *
     * @param string $akerunUserId AkerunユーザーのID
     * @param array $userData ユーザーの情報
     * @return \Cake\Http\Client\Response
     */
    protected function addExistingUser(string $akerunUserId, array $userData = []): Response
    {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');

        return $this->sendApi(
            $this->getConfig('env.apiUrl') . sprintf(
                static::API_URL_CREATE_USER,
                $smartLock->get('organizations_id')
            ),
            Request::METHOD_POST,
            static::CONTENT_TYPE_JSON,
            $userData + [
                'organization_id' => $smartLock->get('organizations_id'),
                'user_id' => $akerunUserId,
            ],
            $this->getToken()
        );
    }

    /**
     * アケルンユーザー名取得
     *
     * @param \App\Model\Entity\User $user 会員情報
     * @return string ユーザー名
     */
    protected function getUserName(User $user): string
    {
        $userName = '';

        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');

        $additionValues = $user->get('addition_values');
        if (is_array($additionValues)) {
            $values = Hash::get($additionValues, sprintf('item_%s', $smartLock->get('form_item_id')), '');
            $userName = is_array($values) ? implode(' ', $values) : $values;
        }

        if (!$userName) {
            $userName = sprintf(
                Configure::read('Setting.smartLock.akerun.default_user_name'),
                $user->get('id')
            );
        }

        return $userName;
    }

    /**
     * ユーザー連携エラーメール送信
     *
     * @param int $sendMailType エラータイプ
     * @param \App\Model\Entity\User $user 会員情報
     * @param array $data API への送信値
     * @return void
     */
    protected function sendUserErrorMail(int $sendMailType, User $user, array $data): void
    {
        if (!$this->errorMailSendFlg) {
            return;
        }
        /** @var \App\Model\Table\AdminMailsTable $adminMailsTable */
        $adminMailsTable = $this->getTableLocator()->get('AdminMails');
        // 送信先メールアドレス取得
        $adminMails = $adminMailsTable->find('AutoReplyMail')
            ->all()->combine('id', 'mail')->toArray();

        $smartLockType = 'Akerun';
        $subject = (string)Configure::readOrFail('Setting.mail.errorSubject.SmartLockLinkage');
        switch ($sendMailType) {
            case static::SEND_MAIL_CREATE_USER:
                $subject = sprintf($subject, $smartLockType, 'ユーザー作成');
                $template = 'smart_lock/error_akerun_create_user';
                break;

            case static::SEND_MAIL_USER_UPDATE:
                $subject = (string)Configure::readOrFail('Setting.mail.errorSubject.SmartLockUpdateUser');
                $template = 'smart_lock/error_akerun_update_user';
                break;

            default:
                throw new CakeException();
        }

        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->getConfig('smartLock');
        $info = [
            'smartLock' => $smartLock,
            'user' => $user,
            'user_name' => Hash::get($data, 'user_name', ''),
            'user_mail' => Hash::get($data, 'user_mail', ''),
        ];

        // メール送信
        $this->getMailer('Admin')->send('smartLockErrorMail', [
            $adminMails,
            $info,
            $subject,
            $template,
        ]);
    }

    /**
     * 合鍵 URL 利用時に必要な固定値をセットする
     *
     * @param array $data パラメータ
     * @param \App\Model\Entity\EventSmartLock $eventSmartLock 予約枠スマートロック情報
     * @return array $data
     */
    private function setUseKeyUrlParameter(array $data, EventSmartLock $eventSmartLock): array
    {
        if ($eventSmartLock->get('smart_lock_key_url_flg')) {
            $data['enable_key_url'] = true;
            // URL 利用時はスケジュール更新にパスワードが必須になるので空を設定する
            $data['key_url_password'] = '';
        } else {
            $data['enable_key_url'] = false;
        }

        return $data;
    }

    /**
     * アケルンユーザー登録時のエラー処理
     *
     * @param array $data パラメータ
     * @param \App\Model\Entity\User $user ユーザー
     * @param int $statusCode ステータスコード
     * @param array $responseBody レスポンスボディ
     * @return void
     */
    private function handleCreateUserError(array $data, User $user, int $statusCode, array $responseBody): void
    {
        $this->sendUserErrorMail(static::SEND_MAIL_CREATE_USER, $user, $data);
        // エラーログ
        $this->writeLog(
            sprintf(
                'http status = %s, response_body = %s',
                $statusCode,
                serialize($responseBody)
            )
        );
    }
}
