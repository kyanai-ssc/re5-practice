<?php
declare(strict_types=1);

namespace App\Utility\SmartLock;

use App\Exception\SmartLockException;
use App\Model\Entity\Reservation;
use App\Model\Entity\SmartLock;
use App\Model\Entity\SystemSetting;
use Cake\Console\Arguments;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Http\Client\Request;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
use Exception;

/**
 * RemoteLock class.
 */
class RemoteLock extends AbstractSmartLock
{
    /**
     * 再試行する HTTP ステータスコード (アクセストークン不正)
     *
     * @var int[]
     * @see https://connect.lockstate.jp/api/docs#http-status-codes
     */
    public const RETRY_STATUS_CODE = [403];

    /**
     * Accept
     *
     * @var string
     */
    public const API_ACCEPT = 'application/json';

    /**
     * grant type: リモートロックは client_credentials
     *
     * @var string
     */
    public const API_GRANT_TYPE = 'client_credentials';

    /**
     * API URL: アクセスゲスト取得
     *
     * @var string
     */
    public const API_URL_CREATE_ACCESS_PERSONS = '/access_persons';

    /**
     * API URL: アクセスゲスト更新
     *
     * @var string
     */
    public const API_URL_UPDATE_ACCESS_PERSONS = '/access_persons/%s';

    /**
     * API URL: アクセスゲスト削除
     *
     * @var string
     */
    public const API_URL_DELETE_ACCESS_PERSONS = '/access_persons/%s';

    /**
     * API URL: 予約詳細
     *
     * @var string
     */
    public const API_URL_GET_BOOKING_DETAIL = '/bookings/%s';

    /**
     * API URL: アクセス権限登録
     *
     * @var string
     */
    public const API_URL_GRANT_ACCESS = '/access_persons/%s/accesses';

    /**
     * API URL: アクセス権限削除
     *
     * @var string
     */
    public const API_URL_DELETE_ACCESS = '/access_persons/%s/accesses/%s';

    /**
     * API URL: 予約(booking)作成
     *
     * @var string
     */
    public const API_URL_CREATE_BOOKING = '/bookings';

    /**
     * API URL: 予約(booking)変更
     *
     * @var string
     */
    public const API_URL_CHANGE_BOOKING = '/bookings/%s';

    /**
     * API URL: 予約(booking)キャンセル
     *
     * @var string
     */
    public const API_URL_CANCEL_BOOKING = '/bookings/%s/deactivate';

    /**
     * アクセスゲスト取得時のタイプ
     *
     * @var string
     */
    public const API_ACCESS_PERSONS_TYPE = 'access_guest';

    /**
     * アクセスタイプ: LOCK
     *
     * @var string
     */
    public const API_ACCESSIBLE_TYPE_LOCK = 'lock';

    /**
     * 予約(booking)作成時のタイプ
     *
     * @var string
     */
    public const API_CREATE_BOOKINGS_TYPE = 'booking';

    /**
     * エラー時の送信メール: アクセスゲスト作成
     *
     * @var int
     */
    protected const SEND_MAIL_CREATE_ACCESS_GUEST = 1;

    /**
     * エラー時の送信メール: アクセス権限作成
     *
     * @var int
     */
    protected const SEND_MAIL_GRANT_ACCESS = 2;

    /**
     * エラー時の送信メール: 予約情報更新
     *
     * @var int
     */
    protected const SEND_MAIL_UPDATE_RESERVATION = 3;

    /**
     * エラー時の送信メール: アクセスゲスト更新
     *
     * @var int
     */
    protected const SEND_MAIL_UPDATE_ACCESS_GUEST = 4;

    /**
     * エラー時の送信メール: アクセス権限削除
     *
     * @var int
     */
    protected const SEND_MAIL_DELETE_GRANT = 5;

    /**
     * エラー時の送信メール: 予約(booking)作成
     *
     * @var int
     */
    protected const SEND_MAIL_CREATE_BOOKING = 6;

    /**
     * エラー時の送信メール: 予約(booking)変更
     *
     * @var int
     */
    protected const SEND_MAIL_CHANGE_BOOKING = 7;

    /**
     * エラー時の送信メール: 予約(booking)キャンセル
     *
     * @var int
     */
    protected const SEND_MAIL_CANCEL_BOOKING = 8;

    /**
     * 予約キャンセルをスキップする予約詳細取得APIのレスポンス
     *
     * @var array
     */
    public const CANCEL_API_SKIP_CONDITION = [
        'http_status' => 404,
        'booking_status' => 'deactivated',
    ];

    /**
     * アクセスゲストID
     *
     * エラーメール送信時の DB 保存前に参照が必要な値なのでプロパティに持たせる
     *
     * @var string
     */
    protected $accessGuestId = '';

    /**
     * @inheritDoc
     */
    protected function initialize(array $options): array
    {
        $options['env'] = Configure::readOrFail('Env.smartLock.remoteLock');

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
            && $reservation->reservation_smart_lock->get('smart_lock_user_id');
    }

    /**
     * @inheritDoc
     */
    public function addSchedule(Reservation $reservation): bool
    {
        if (!$reservation->get('event') || !$reservation->event->get('event_smart_lock')) {
            throw new CakeException();
        }

        $bookingData = $this->createBooking($reservation);

        // 予約情報更新
        $data = [
            'reservation_id' => $reservation->get('id'),
            'smart_lock_user_id' => (string)Hash::get($bookingData, 'attributes.access_person_id', ''),
            'smart_lock_pin' => (string)Hash::get($bookingData, 'attributes.pin', ''),
            'smart_lock_key_url' => (string)Hash::get($bookingData, 'attributes.universal_access_key_url', ''),
            'smart_lock_key_id' => (string)Hash::get($bookingData, 'id', ''),
        ];

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
        $smartLockKeyId = (string)$reservationSmartLock->get('smart_lock_key_id');
        if (empty($smartLockKeyId)) {
            $this->accessGuestId = (string)$reservationSmartLock->get('smart_lock_user_id');
        }

        if ($reservation->get('event_id') === $oldReservation->get('event_id')) {
            // 予約枠が変わらない場合
            $attributes = [];
            if (!empty($reservationSmartLock->get('smart_lock_pin'))) {
                $attributes['pin'] = $reservationSmartLock->get('smart_lock_pin');
            }

            if (empty($smartLockKeyId)) {
                // 予約スマートロック.合鍵IDが登録されていない場合、アクセスゲストのみ更新
                $this->updateAccessGuest($reservation, $oldReservation, $this->accessGuestId, $attributes);
            } else {
                // 予約スマートロック.合鍵IDが登録されている場合、予約(booking)の変更のみ行う
                $this->updateBooking($reservation, $oldReservation, $smartLockKeyId, $attributes);
            }

            return true;
        }

        // 予約枠が変わる場合
        $event = $reservation->event;
        $attributes = [
            'name' => sprintf('%s_%s', $event->get('name'), $reservation->get('id')),
            'generate_pin' => true,
        ];
        $eventSmartLock = $reservation->event->event_smart_lock;
        $oldEventSmartLock = $oldReservation->event->event_smart_lock;

        if (!empty($smartLockKeyId)) {
            // 予約スマートロック.合鍵IDが登録されている場合、予約(booking)の変更のみ行う
            $attributes['device_id'] = $eventSmartLock->get('smart_lock_device_key');

            $bookingData = $this->updateBooking($reservation, $oldReservation, $smartLockKeyId, $attributes);

            $reservationSmartLock->set('smart_lock_pin', (string)Hash::get($bookingData, 'attributes.pin', ''));
        } else {
            // 予約スマートロック.合鍵IDが登録されていない場合
            if ($oldEventSmartLock->get('smart_lock_device_key') !== $eventSmartLock->get('smart_lock_device_key')) {
                // 変更前後の予約枠.デバイスキー変わっている場合、予約(booking)を作成
                $bookingData = $this->createBooking($reservation);

                // 予約スマートロック情報更新
                $reservationSmartLock
                    ->set('smart_lock_user_id', (string)Hash::get($bookingData, 'attributes.access_person_id', ''));
                $reservationSmartLock->set('smart_lock_pin', (string)Hash::get($bookingData, 'attributes.pin', ''));
                $reservationSmartLock->set(
                    'smart_lock_key_url',
                    (string)Hash::get($bookingData, 'attributes.universal_access_key_url', '')
                );
                $reservationSmartLock->set('smart_lock_key_id', (string)Hash::get($bookingData, 'id', ''));
            } else {
                // 変更前後の予約枠.デバイスキーが同一の場合、アクセスゲスト更新
                $accessGuest = $this->updateAccessGuest(
                    $reservation,
                    $oldReservation,
                    $this->accessGuestId,
                    $attributes
                );

                // 予約スマートロック情報更新
                $reservationSmartLock->set('smart_lock_pin', Hash::get($accessGuest, 'attributes.pin', ''));
            }
        }

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

        if (
            $oldEventSmartLock->get('smart_lock_device_key') !== $eventSmartLock->get('smart_lock_device_key') &&
            empty($smartLockKeyId)
        ) {
            // 変更前後の予約枠.デバイスキー変わっている場合、変更前のアクセス権限を削除する
            $oldReservationSmartLock = $oldReservation->reservation_smart_lock;
            $this->deleteAccess(
                $this->accessGuestId,
                $oldReservationSmartLock->get('smart_lock_grant_id'),
                $oldReservation
            );
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
        $reservationSmartLock = $reservation->reservation_smart_lock;
        $smartLockKeyId = (string)$reservationSmartLock->get('smart_lock_key_id');
        if (empty($smartLockKeyId)) {
            $this->accessGuestId = (string)$reservationSmartLock->get('smart_lock_user_id');
        }

        /** @var \App\Model\Table\ReservationSmartLocksTable $reservationSmartLocksTable */
        $reservationSmartLocksTable = $this->getTableLocator()->get('ReservationSmartLocks');
        // 予約スマートロック情報をクリアする
        $reservationSmartLock->set('smart_lock_user_id');
        $reservationSmartLock->set('smart_lock_grant_id');
        $reservationSmartLock->set('smart_lock_pin');
        $reservationSmartLock->set('smart_lock_key_url');
        $reservationSmartLock->set('smart_lock_key_id');
        $reservationSmartLocksTable->save($reservationSmartLock);

        // リモートロック側を削除する
        if (empty($smartLockKeyId)) {
            // 予約スマートロック.合鍵IDが登録されていない場合、アクセスゲストを削除
            $this->sendProcessApi(
                $this->getConfig('env.apiUrl') . sprintf(static::API_URL_DELETE_ACCESS_PERSONS, $this->accessGuestId),
                Request::METHOD_DELETE,
                '',
                null,
                static::SEND_MAIL_DELETE_GRANT,
                $reservation
            );
        } else {
            $reservationSmartLock->set('smart_lock_key_id', $smartLockKeyId);
            // 予約スマートロック.合鍵IDが登録されている場合、予約(booking)をキャンセル
            // 予約詳細取得API
            $bookingData = $this->sendProcessApi(
                $this->getConfig('env.apiUrl') . sprintf(static::API_URL_GET_BOOKING_DETAIL, $smartLockKeyId),
                Request::METHOD_GET,
                '',
                null,
                static::SEND_MAIL_CANCEL_BOOKING,
                $reservation,
                null,
                [static::CANCEL_API_SKIP_CONDITION['http_status']]
            );

            $attributeStatus = (string)Hash::get($bookingData, 'data.attributes.status');
            if (
                (int)Hash::get($bookingData, 'statusCode') === static::CANCEL_API_SKIP_CONDITION['http_status']
                || $attributeStatus === static::CANCEL_API_SKIP_CONDITION['booking_status']
            ) {
                return true;
            }

            // キャンセルAPI
            $this->sendProcessApi(
                $this->getConfig('env.apiUrl') . sprintf(static::API_URL_CANCEL_BOOKING, $smartLockKeyId),
                Request::METHOD_PUT,
                '',
                null,
                static::SEND_MAIL_CANCEL_BOOKING,
                $reservation
            );
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function initialSetting(Arguments $args, string $secretKey): void
    {
        /** @var \App\Model\Table\SmartLocksTable $smartLocksTable */
        $smartLocksTable = $this->getTableLocator()->get('SmartLocks');
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
        $smartLocksTable->getConnection()->transactional(
            function () use ($args, $secretKey, $smartLocksTable, $systemSettingsTable) {
                // トークン取得
                $result = $this->sendTokenApi(
                    $this->getConfig('env.authUrl') . static::API_URL_OAUTH_TOKEN,
                    Request::METHOD_POST,
                    static::CONTENT_TYPE_URLENCODED,
                    [
                        'client_id' => $args->getOption('api-key'),
                        'client_secret' => $secretKey,
                        'grant_type' => static::API_GRANT_TYPE,
                    ]
                );

                // 返却値が空の場合はエラーとする
                $token = (string)Hash::get($result, 'access_token', '');
                if (!$token) {
                    throw new CakeException('token api error : access_token is empty.');
                }

                $smartLock = $smartLocksTable->getUpsertEntity([
                    'type' => SmartLock::TYPE_REMOTE_LOCK,
                    'client_id' => $args->getOption('api-key'),
                    'client_secret' => $secretKey,
                    'buffer' => $args->getOption('buffer'),
                    'organizations_id' => null,
                    'token' => $token,
                    'expires' => FrozenTime::createFromTimestamp(
                        (int)Hash::get($result, 'created_at', 0) + (int)Hash::get($result, 'expires_in', 0),
                        Configure::read('App.defaultTimezone')
                    ),
                    'refresh_token' => null,
                    'form_item_id' => null,
                ]);

                if (!$smartLocksTable->save($smartLock)) {
                    throw new CakeException('smart_locks save error.');
                }

                $systemSetting = $systemSettingsTable->getData();
                $systemSetting->set('smart_lock_use_flg', SystemSetting::SMART_LOCK_USE_FLG_ON);

                if (!$systemSettingsTable->save($systemSetting)) {
                    throw new CakeException('system_settings save error.');
                }

                return true;
            }
        );
        $smartLocksTable->deleteCacheData();
        $systemSettingsTable->deleteCacheData();
    }

    /**
     * アクセスゲスト作成
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return array アクセスゲスト
     */
    protected function createAccessGuest(Reservation $reservation): array
    {
        // 開始時間 - バッファ
        $dateTimeFrom = $this->getDateTimeFrom($reservation);
        // 終了時間 ＋ バッファ
        $dateTimeTo = $this->getDateTimeTo($reservation);

        /** @var \App\Model\Entity\Event $event */
        $event = $reservation->get('event');
        $attributes = [
            'name' => sprintf('%s_%s', $event->get('name'), $reservation->get('id')),
            'generate_pin' => true,
            'starts_at' => sprintf(
                '%sT%s',
                $dateTimeFrom->format('Y-m-d'),
                $dateTimeFrom->format('H:i:s')
            ),
            'ends_at' => sprintf(
                '%sT%s',
                $dateTimeTo->format('Y-m-d'),
                $dateTimeTo->format('H:i:s')
            ),
        ];

        $result = $this->sendProcessApi(
            $this->getConfig('env.apiUrl') . static::API_URL_CREATE_ACCESS_PERSONS,
            Request::METHOD_POST,
            static::CONTENT_TYPE_JSON,
            [
                'type' => static::API_ACCESS_PERSONS_TYPE,
                'attributes' => $attributes,
            ],
            static::SEND_MAIL_CREATE_ACCESS_GUEST,
            $reservation
        );

        return (array)Hash::get($result, 'data', []);
    }

    /**
     * アクセスゲスト更新
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param \App\Model\Entity\Reservation $oldReservation 変更前予約
     * @param string $accessGuestId アクセスゲストID
     * @param array $attributes 開始時間/終了時間以外に送信したい値
     * @return array アクセスゲスト
     */
    protected function updateAccessGuest(
        Reservation $reservation,
        Reservation $oldReservation,
        string $accessGuestId,
        array $attributes = []
    ): array {
        // 開始時間 - バッファ
        $dateTimeFrom = $this->getDateTimeFrom($reservation);
        // 終了時間 ＋ バッファ
        $dateTimeTo = $this->getDateTimeTo($reservation);

        $attributes['starts_at'] = sprintf(
            '%sT%s',
            $dateTimeFrom->format('Y-m-d'),
            $dateTimeFrom->format('H:i:s')
        );
        $attributes['ends_at'] = sprintf(
            '%sT%s',
            $dateTimeTo->format('Y-m-d'),
            $dateTimeTo->format('H:i:s')
        );

        $result = $this->sendProcessApi(
            $this->getConfig('env.apiUrl') . sprintf(static::API_URL_UPDATE_ACCESS_PERSONS, $accessGuestId),
            Request::METHOD_PUT,
            static::CONTENT_TYPE_JSON,
            ['attributes' => $attributes],
            static::SEND_MAIL_UPDATE_ACCESS_GUEST,
            $oldReservation
        );

        return (array)Hash::get($result, 'data', []);
    }

    /**
     * アクセス権限登録
     *
     * @param string $accessGuestId アクセスゲストId
     * @param string $smartLockDeviceKey デバイスキー
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return string 権限ID
     */
    protected function grantAccess(string $accessGuestId, string $smartLockDeviceKey, Reservation $reservation): string
    {
        $result = $this->sendProcessApi(
            $this->getConfig('env.apiUrl') . sprintf(static::API_URL_GRANT_ACCESS, $accessGuestId),
            Request::METHOD_POST,
            static::CONTENT_TYPE_JSON,
            [
                'attributes' => [
                    'accessible_type' => static::API_ACCESSIBLE_TYPE_LOCK,
                    'accessible_id' => $smartLockDeviceKey,
                ],
            ],
            static::SEND_MAIL_GRANT_ACCESS,
            $reservation
        );

        return (string)Hash::get($result, 'data.id', '');
    }

    /**
     * アクセス権限削除
     *
     * @param string $accessGuestId アクセスゲストId
     * @param string $grantId 権限ID
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return void
     */
    protected function deleteAccess(string $accessGuestId, string $grantId, Reservation $reservation): void
    {
        $this->sendProcessApi(
            $this->getConfig('env.apiUrl') . sprintf(static::API_URL_DELETE_ACCESS, $accessGuestId, $grantId),
            Request::METHOD_DELETE,
            '',
            [],
            static::SEND_MAIL_DELETE_GRANT,
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

        $smartLockType = 'RemoteLOCK';
        $subject = (string)Configure::readOrFail('Setting.mail.errorSubject.SmartLockLinkage');
        if (!$anotherReservation) {
            $anotherReservation = $reservation;
        }
        switch ($errorMailType) {
            case static::SEND_MAIL_CREATE_ACCESS_GUEST:
                $subject = sprintf($subject, $smartLockType, 'アクセスゲスト作成');
                $template = 'smart_lock/error_remote_lock_create_access_guest';
                break;

            case static::SEND_MAIL_GRANT_ACCESS:
                $subject = sprintf($subject, $smartLockType, 'アクセス権限');
                $template = 'smart_lock/error_remote_lock_grant_access';
                break;

            case static::SEND_MAIL_UPDATE_RESERVATION:
                $subject = (string)Configure::readOrFail('Setting.mail.errorSubject.SmartLockUpdateReservation');
                $template = 'smart_lock/error_remote_lock_update_reservation';
                break;

            case static::SEND_MAIL_UPDATE_ACCESS_GUEST:
                $subject = sprintf($subject, $smartLockType, 'アクセスゲスト更新');
                $template = 'smart_lock/error_remote_lock_delete_grant';
                break;

            case static::SEND_MAIL_DELETE_GRANT:
                $subject = sprintf($subject, $smartLockType, 'アクセス権限削除');
                $template = 'smart_lock/error_remote_lock_delete_grant';
                break;

            case static::SEND_MAIL_CREATE_BOOKING:
                $subject = sprintf($subject, $smartLockType, '予約新規作成');
                $template = 'smart_lock/error_remote_lock_create_booking';
                break;

            case static::SEND_MAIL_CHANGE_BOOKING:
                $subject = sprintf($subject, $smartLockType, '予約の変更');
                $template = 'smart_lock/error_remote_lock_change_booking';
                break;

            case static::SEND_MAIL_CANCEL_BOOKING:
                $subject = sprintf($subject, $smartLockType, '予約のキャンセル');
                $template = 'smart_lock/error_remote_lock_cancel_booking';
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
            'accessGuestId' => $this->accessGuestId,
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
     * 予約(booking)作成
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return array 予約(booking)情報
     */
    protected function createBooking(Reservation $reservation)
    {
        // デバイスキー
        $eventSmartLock = $reservation->event->event_smart_lock;
        $deviceKey = (string)$eventSmartLock->get('smart_lock_device_key');
        // 開始時間 - バッファ
        $dateTimeFrom = $this->getDateTimeFrom($reservation);
        // 終了時間 ＋ バッファ
        $dateTimeTo = $this->getDateTimeTo($reservation);

        /** @var \App\Model\Entity\Event $event */
        $event = $reservation->get('event');
        $attributes = [
            'name' => sprintf('%s_%s', $event->get('name'), $reservation->get('id')),
            'device_id' => $deviceKey,
            'starts_at' => sprintf(
                '%sT%s',
                $dateTimeFrom->format('Y-m-d'),
                $dateTimeFrom->format('H:i:s')
            ),
            'ends_at' => sprintf(
                '%sT%s',
                $dateTimeTo->format('Y-m-d'),
                $dateTimeTo->format('H:i:s')
            ),
        ];

        $result = $this->sendProcessApi(
            $this->getConfig('env.apiUrl') . static::API_URL_CREATE_BOOKING,
            Request::METHOD_POST,
            static::CONTENT_TYPE_JSON,
            [
                'type' => static::API_CREATE_BOOKINGS_TYPE,
                'attributes' => $attributes,
            ],
            static::SEND_MAIL_CREATE_BOOKING,
            $reservation
        );

        return (array)Hash::get($result, 'data', []);
    }

    /**
     * 予約(booking)変更
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param \App\Model\Entity\Reservation $oldReservation 変更前予約
     * @param string $reservationSmartLockId 合鍵ID
     * @param array $attributes 開始時間/終了時間以外に送信したい値
     * @return array 予約(booking)情報
     */
    protected function updateBooking(
        Reservation $reservation,
        Reservation $oldReservation,
        string $reservationSmartLockId,
        array $attributes = []
    ) {
        // 開始時間 - バッファ
        $dateTimeFrom = $this->getDateTimeFrom($reservation);
        // 終了時間 ＋ バッファ
        $dateTimeTo = $this->getDateTimeTo($reservation);

        $attributes['starts_at'] = sprintf(
            '%sT%s',
            $dateTimeFrom->format('Y-m-d'),
            $dateTimeFrom->format('H:i:s')
        );
        $attributes['ends_at'] = sprintf(
            '%sT%s',
            $dateTimeTo->format('Y-m-d'),
            $dateTimeTo->format('H:i:s')
        );

        $result = $this->sendProcessApi(
            $this->getConfig('env.apiUrl') . sprintf(static::API_URL_CHANGE_BOOKING, $reservationSmartLockId),
            Request::METHOD_PUT,
            static::CONTENT_TYPE_JSON,
            [
                'type' => static::API_CREATE_BOOKINGS_TYPE,
                'attributes' => $attributes,
            ],
            static::SEND_MAIL_CHANGE_BOOKING,
            $oldReservation
        );

        return (array)Hash::get($result, 'data', []);
    }
}
