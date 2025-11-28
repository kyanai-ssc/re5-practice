<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Mailer\DefaultMailer;
use App\Model\AppTable;
use App\Model\Entity\AutoReplyMail;
use App\Model\Entity\AutoReplyMailHistory;
use App\Model\Entity\OptinToken;
use App\Model\Entity\Reservation;
use App\Model\Entity\ReservationGuestCode;
use App\Model\Entity\ReservationPayment;
use App\Model\Entity\ReservationStatus;
use App\Model\Entity\User;
use App\Model\Entity\UserPasswordReminderToken;
use App\Model\Entity\WaitingCancellation;
use App\Model\Entity\WaitingCancellationConfToken;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Query;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Security;
use Throwable;

/**
 * AutoReplyMailHistories Model
 *
 * @method \App\Model\Entity\AutoReplyMailHistory newEmptyEntity()
 * @method \App\Model\Entity\AutoReplyMailHistory newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory get($primaryKey, $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AutoReplyMailHistory[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AutoReplyMailHistoriesTable extends AppTable
{
    use MailerAwareTrait;

    public const BOUNCE_MAIL_TOKEN_PREFIX = 'auto-reply-';
    public const BOUNCE_MAIL_TOKEN_LENGTH = 32;

    public const REMINDER_LOCK_CODE = 0;
    public const REMINDER_CLOSE_LOCK_CODE = 1;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('AutoReplyMails', [
            'foreignKey' => 'auto_reply_mail_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->belongsTo('Reservations', [
            'foreignKey' => 'reservation_id',
        ]);
        $this->belongsTo('WaitingCancellations', [
            'foreignKey' => 'waiting_cancellation_id',
        ]);
        $this->belongsTo('Admins', [
            'foreignKey' => 'admin_id',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema->setColumnType('data', 'json');

        return $schema;
    }

    /**
     * バウンスメール登録用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findBounceMail(Query $query, array $options = [])
    {
        $query->select([
            'user_id',
            'user_mail',
        ]);

        $bounceMailToken = Hash::get($options, 'inputs.bounce_mail_token');
        $query->where([
            'AutoReplyMailHistories.bounce_mail_token' => $bounceMailToken,
        ]);

        return $query;
    }

    /**
     * 再送信用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findResend(Query $query, array $options = [])
    {
        $query->select([
            'id',
            'auto_reply_mail_id',
            'user_id',
            'reservation_id',
            'waiting_cancellation_id',
            'admin_id',
            'user_mail',
            'send_flg',
            'send_timestamp',
            'reminder_setting_timestamp',
            'bounce_mail_token',
            'data',
        ]);

        $id = Hash::get($options, 'inputs.id');
        $query->where([
            'AutoReplyMailHistories.id IN' => (array)$id,
        ]);

        $checkType = Hash::get($options, 'checkType', false);
        if ($checkType) {
            $query->contain([
                'AutoReplyMails' => [
                    'fields' => [],
                ],
            ]);
            $query->where([
                'AutoReplyMails.type IN' => Configure::readOrFail('Master.autoReplyMail.canResend'),
            ]);
        }

        $query->order([
            'AutoReplyMailHistories.id' => 'ASC',
        ]);

        return $query;
    }

    /**
     * 非会員の自動返信メール履歴を生成（トークン、オプトインなど）
     *
     * @param string $mail メールアドレス
     * @param int $type 自動返信メールタイプ
     * @return \App\Model\Entity\AutoReplyMailHistory|null
     */
    public function generateDataForToken($mail, $type)
    {
        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->getTableLocator()->get('AutoReplyMails');

        $autoReplyMailsQuery = $autoReplyMailsTable->find('sendTarget', [
            'inputs' => [
                'type' => $type,
            ],
        ]);

        $autoReplyMailsQuery->select(['id'], true);
        $autoReplyMail = $autoReplyMailsQuery->first();

        if (!($autoReplyMail instanceof EntityInterface)) {
            return null;
        }

        $autoReplyMailHistory = $this->newEntity([
            'auto_reply_mail_id' => $autoReplyMail->get('id'),
            'user_mail' => $mail,
            'bounce_mail_token' => $this->generateBounceMailToken(),
        ], ['validate' => false]);

        return $autoReplyMailHistory;
    }

    /**
     * 会員の自動返信メール履歴を生成
     *
     * @param \App\Model\Entity\User $user 会員
     * @param int|null $type 自動返信メールタイプ
     * @return \App\Model\Entity\AutoReplyMailHistory|null
     */
    public function generateDataForUser(User $user, ?int $type = null)
    {
        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->getTableLocator()->get('AutoReplyMails');

        if ($user->isGuest()) {
            return null;
        }

        if (is_null($type)) {
            if ($user->isNew()) {
                $type = AutoReplyMail::TYPE_USER_ADD;
            } elseif (!$user->withdrew()) {
                $type = AutoReplyMail::TYPE_USER_EDIT;
            } else {
                $type = AutoReplyMail::TYPE_USER_DELETE;
            }
        }

        $autoReplyMailsQuery = $autoReplyMailsTable->find('sendTarget', [
            'inputs' => [
                'type' => $type,
                'user_authority_id' => $user->get('user_authority_id'),
            ],
        ]);
        $autoReplyMailsQuery->select(['id'], true);
        $autoReplyMail = $autoReplyMailsQuery->first();

        if (!($autoReplyMail instanceof EntityInterface)) {
            return null;
        }

        $adminId = null;
        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();

            $adminId = $loginData->get('id');
        }

        $autoReplyMailHistory = $this->newEntity([
            'auto_reply_mail_id' => $autoReplyMail->get('id'),
            'user_id' => $user->get('id'),
            'admin_id' => $adminId,
            'user_mail' => $user->get('mail'),
            'bounce_mail_token' => $this->generateBounceMailToken(),
        ], ['validate' => false]);

        $password = $user->get('plain_password');
        if (((string)$password) !== '') {
            $autoReplyMailHistory->set('user_password', $password);
        }

        return $autoReplyMailHistory;
    }

    /**
     * 予約の自動返信メール履歴を生成
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param int|null $type 自動返信メールタイプ
     * @param array $options オプション
     * @return \App\Model\Entity\AutoReplyMailHistory|null
     */
    public function generateDataForReservation(Reservation $reservation, ?int $type = null, array $options = [])
    {
        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->getTableLocator()->get('AutoReplyMails');
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
        $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

        /** @var \App\Model\Entity\User $user */
        $user = $reservation->getUserEntity();
        /** @var \App\Model\Entity\Event $event */
        $event = $reservation->getEventEntity();

        $sendReserveAddFlg = false;
        if (
            is_null($type) && !$reservation->isNew() && $reservation->hasReservationPayment()
            && $this->commonData()->existsAdminLoginData()
            && Hash::get($options, 'forEdit', false)
        ) {
            /** @var \App\Model\Entity\ReservationPayment $reservationPayment */
            $reservationPayment = $reservation->getReservationPayment();

            if ($reservation->get('payment_tracking_id') !== $reservationPayment->get('payment_tracking_id')) {
                $reservationPaymentStatus = $reservationPaymentsTable->getReservationPaymentStatus(
                    $reservationPayment->get('status'),
                    $reservationPayment->get('payment_limit')
                );

                $sendReserveAddFlg = $reservationPaymentStatus === ReservationPayment::DISPLAY_STATUS_EXPIRED;
            }
        }

        $reservationStatusFromId = null;
        $reservationStatusToId = null;
        if (is_null($type)) {
            if ($reservation->isNew() || $sendReserveAddFlg) {
                $type = AutoReplyMail::TYPE_RESERVE_ADD;
                if (
                    isset($reservation->repeat_reservation) &&
                    $reservation->repeat_reservation === (string)Reservation::RESERVATION_TYPE_REPEAT_RESERVATION
                ) {
                    $type = AutoReplyMail::TYPE_REPEAT_RESERVATION;
                }
                $reservationStatusToId = $reservation->get('reservation_status_id');
            } else {
                if (!$reservation->isDirty('reservation_status_id')) {
                    $type = AutoReplyMail::TYPE_RESERVE_EDIT;
                } else {
                    $oldStatus = $reservation->getOriginal('reservation_status_id');
                    $newStatus = $reservation->get('reservation_status_id');
                    $oldStatusType = $reservationStatusesTable->getReservationStatusType($oldStatus);
                    $newStatusType = $reservationStatusesTable->getReservationStatusType($newStatus);
                    if (((string)$oldStatusType) !== ((string)ReservationStatus::STATUS_TYPE_CANCEL)) {
                        if (((string)$newStatusType) !== ((string)ReservationStatus::STATUS_TYPE_CANCEL)) {
                            $type = AutoReplyMail::TYPE_STATUS_UPDATE;
                            $reservationStatusFromId = $oldStatus;
                            $reservationStatusToId = $newStatus;
                        } else {
                            $type = AutoReplyMail::TYPE_RESERVE_CANCEL;
                            $reservationStatusFromId = $oldStatus;
                            $reservationStatusToId = $newStatus;
                        }
                    } else {
                        if (((string)$newStatusType) !== ((string)ReservationStatus::STATUS_TYPE_CANCEL)) {
                            $type = AutoReplyMail::TYPE_RESERVE_ADD;
                            $reservationStatusToId = $newStatus;
                        } else {
                            $type = AutoReplyMail::TYPE_RESERVE_EDIT;
                        }
                    }
                }
            }
        }

        if (
            (string)$type === (string)AutoReplyMail::TYPE_RESERVE_REMINDER
            || (string)$type === (string)AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE
        ) {
            $reservationStatusFromId = $reservation->get('reservation_status_id');
            $reservationStatusToId = $reservation->get('reservation_status_id');
        }

        $autoReplyMailsQuery = $autoReplyMailsTable->find('sendTarget', [
            'inputs' => [
                'type' => $type,
                'user_authority_id' => $user->get('user_authority_id'),
                'label_id' => $event->get('label_id'),
                'reservation_status_from_id' => $reservationStatusFromId,
                'reservation_status_to_id' => $reservationStatusToId,
            ],
        ]);
        $autoReplyMailsQuery->select(['id'], true);
        $autoReplyMail = $autoReplyMailsQuery->first();
        if (!($autoReplyMail instanceof EntityInterface)) {
            return null;
        }

        $adminId = null;
        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();

            $adminId = $loginData->get('id');
        }

        $autoReplyMailHistory = $this->newEntity([
            'auto_reply_mail_id' => $autoReplyMail->get('id'),
            'user_id' => $reservation->get('user_id'),
            'reservation_id' => $reservation->get('id'),
            'admin_id' => $adminId,
            'user_mail' => $user->get('mail'),
            'bounce_mail_token' => $this->generateBounceMailToken(),
        ], ['validate' => false]);

        return $autoReplyMailHistory;
    }

    /**
     * キャンセル待ちの自動返信メール履歴を生成
     *
     * @param \App\Model\Entity\WaitingCancellation $waitingCancellation キャンセル待ち
     * @return \App\Model\Entity\AutoReplyMailHistory|null
     */
    public function generateDataForWaitingCancellation(WaitingCancellation $waitingCancellation)
    {
        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->getTableLocator()->get('AutoReplyMails');
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

        $userId = null;
        $userAuthorityId = $userAuthoritiesTable->getGuestAuthority()->get('id');
        $mail = $waitingCancellation->get('mail');
        if ($waitingCancellation->has('user')) {
            $userId = $waitingCancellation->get('user')->get('id');
            $userAuthorityId = $waitingCancellation->get('user')->get('user_authority_id');
            $mail = $waitingCancellation->get('user')->get('mail');
        }

        if (((string)$mail) === '') {
            return null;
        }

        $autoReplyMailsQuery = $autoReplyMailsTable->find('sendTarget', [
            'inputs' => [
                'type' => AutoReplyMail::TYPE_RESERVE_CANCELWAIT,
                'user_authority_id' => $userAuthorityId,
                'label_id' => $waitingCancellation->get('event')->get('label_id'),
            ],
        ]);
        $autoReplyMailsQuery->select(['id'], true);
        $autoReplyMail = $autoReplyMailsQuery->first();
        if (!($autoReplyMail instanceof EntityInterface)) {
            return null;
        }

        $autoReplyMailHistory = $this->newEntity([
            'auto_reply_mail_id' => $autoReplyMail->get('id'),
            'user_id' => $userId,
            'waiting_cancellation_id' => $waitingCancellation->get('id'),
            'user_mail' => $mail,
            'bounce_mail_token' => $this->generateBounceMailToken(),
        ], ['validate' => false]);

        return $autoReplyMailHistory;
    }

    /**
     * IDリマインダー送信
     *
     * @param \Cake\Datasource\EntityInterface $user 会員情報
     * @return void
     */
    public function sendIdReminder($user)
    {
        if (!($user instanceof User)) {
            throw new CakeException();
        }

        $autoReplyMailHistory = $this->generateDataForUser($user, AutoReplyMail::TYPE_ID_REMINDER);
        if ($autoReplyMailHistory instanceof AutoReplyMailHistory) {
            $autoReplyMailHistory->setDataEntity([
                'user' => $user,
            ]);

            $this->sendAutoReplyMail($autoReplyMailHistory, false);
        }
    }

    /**
     * パスワードリマインダー送信
     *
     * @param \Cake\Datasource\EntityInterface $user 会員情報
     * @param \Cake\Datasource\EntityInterface $tokens トークン情報
     * @return void
     */
    public function sendPasswordReminder($user, $tokens)
    {
        if (!($user instanceof User)) {
            throw new CakeException();
        }

        $autoReplyMailHistory = $this->generateDataForUser($user, AutoReplyMail::TYPE_PASSWORD_REMINDER);
        if ($autoReplyMailHistory instanceof AutoReplyMailHistory) {
            $autoReplyMailHistory->setDataEntity([
                'user' => $user,
            ]);

            $replaceToken = Configure::read('Master.mailReplace.token');
            $limit = UserPasswordReminderToken::EXPIRATION_ADD_HOUR;
            $additional = [
                $replaceToken[DefaultMailer::REPLACE_ADDITIONAL_CHANGE_URL] => Router::url([
                    'prefix' => 'User',
                    'controller' => 'Reminder',
                    'action' => 'token',
                    '?' => [
                        'token' => $tokens->get('token'),
                    ],
                ], true),
                $replaceToken[DefaultMailer::REPLACE_ADDITIONAL_LIMIT] => $limit,
            ];

            $this->sendAutoReplyMail($autoReplyMailHistory, false, $additional);
        }
    }

    /**
     * ゲストログイン
     *
     * @param \Cake\Datasource\EntityInterface $code コード情報
     * @param string $mail メール
     * @return void
     */
    public function sendGuestLogin($code, $mail)
    {
        $autoReplyMailHistory = $this->generateDataForToken($mail, AutoReplyMail::TYPE_NOT_MEMBER_LOGIN);
        $replaceToken = Configure::read('Master.mailReplace.token');

        $additional = [
            $replaceToken[DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_CODE] => $code->get('code'),
            $replaceToken[DefaultMailer::REPLACE_ADDITIONAL_LIMIT] => ReservationGuestCode::EXPIRATION_ADD_HOUR,
        ];

        $this->sendAutoReplyMail($autoReplyMailHistory, false, $additional);
    }

    /**
     * キャンセル待ち確認メールの送信
     *
     * @param \Cake\Datasource\EntityInterface $tokens トークン情報
     * @return void
     */
    public function sendWaitingCancellationToken($tokens)
    {
        $autoReplyMailHistory = $this->generateDataForToken(
            $tokens->get('mail'),
            AutoReplyMail::TYPE_RESERVE_CANCELWAIT_RELEASE
        );

        $replaceToken = Configure::read('Master.mailReplace.token');
        $additional = [
            $replaceToken[DefaultMailer::REPLACE_ADDITIONAL_CANCELWAIT_RELEASE_URL] => Router::url([
                'prefix' => 'User',
                'controller' => 'WaitingCancellation',
                'action' => 'list-token',
                '?' => [
                    'token' => $tokens->get('token'),
                ],
            ], true),
            $replaceToken[DefaultMailer::REPLACE_ADDITIONAL_LIMIT] => WaitingCancellationConfToken::EXPIRATION_ADD_HOUR,
        ];

        $this->sendAutoReplyMail($autoReplyMailHistory, false, $additional);
    }

    /**
     * オプトインメールの送信
     *
     * @param \Cake\Datasource\EntityInterface $optinToken トークン情報
     * @param array|null $urlParameter URLパラメータ
     * @return void
     */
    public function sendOptin(EntityInterface $optinToken, ?array $urlParameter = null)
    {
        if (!($optinToken instanceof OptinToken)) {
            throw new CakeException();
        }

        $type = [
            OptinToken::TYPE_USER => AutoReplyMail::TYPE_OPTIN_USER,
            OptinToken::TYPE_RESERVATION => AutoReplyMail::TYPE_OPTIN_RESERVE,
            OptinToken::TYPE_MAIL_EDIT => AutoReplyMail::TYPE_OPTIN_MAIL_EDIT,
        ];
        $controller = [
            OptinToken::TYPE_USER => 'User',
            OptinToken::TYPE_RESERVATION => 'Reservations',
            OptinToken::TYPE_MAIL_EDIT => 'User',
        ];

        $registerUrl = [
            OptinToken::TYPE_USER => DefaultMailer::REPLACE_ADDITIONAL_REGISTER_URL_USER,
            OptinToken::TYPE_RESERVATION => DefaultMailer::REPLACE_ADDITIONAL_REGISTER_URL_RESERVE,
            OptinToken::TYPE_MAIL_EDIT => DefaultMailer::REPLACE_ADDITIONAL_MAIL_EDIT_APPROVAL_URL,
        ];

        $replaceToken = Configure::read('Master.mailReplace.token');

        if ($optinToken->get('type') === OptinToken::TYPE_MAIL_EDIT) {
            /** @var \App\Model\Table\UsersTable $usersTable */
            $usersTable = $this->getTableLocator()->get('Users');
            $user = $usersTable->get($optinToken->get('user_id'), [
                'finder' => 'edit',
            ]);
            $user->set('mail', $optinToken->get('mail'));

            $autoReplyMailHistory = $this->generateDataForUser($user, $type[$optinToken->get('type')]);
            if (isset($autoReplyMailHistory)) {
                $user->set('mail', $user->getOriginal('mail'));
                $mailData = [
                    'user' => $user,
                ];
                $autoReplyMailHistory->setDataEntity($mailData);
            }

            $url = Router::url([
                'prefix' => 'User',
                'controller' => $controller[$optinToken->get('type')],
                'action' => 'mailEditApproval',
                '?' => [
                        'token' => $optinToken->get('token'),
                    ],
            ], true);
        } else {
            $autoReplyMailHistory = $this->generateDataForToken(
                $optinToken->get('mail'),
                $type[$optinToken->get('type')]
            );
            $url = Router::url([
                'prefix' => 'User',
                'controller' => $controller[$optinToken->get('type')],
                'action' => 'token',
                '?' => [
                        'token' => $optinToken->get('token'),
                    ] + (array)$urlParameter,
            ], true);
        }

        $this->sendAutoReplyMail($autoReplyMailHistory, false, [
            $replaceToken[$registerUrl[$optinToken->get('type')]] => $url,
            $replaceToken[DefaultMailer::REPLACE_ADDITIONAL_LIMIT] => OptinToken::EXPIRATION_ADD_HOUR,
        ]);
    }

    /**
     * 自動返信メールを送信
     *
     * @param \Cake\Datasource\EntityInterface|null $autoReplyMailHistory 自動返信メール送信履歴
     * @param bool $adminSend 管理者送信フラグ
     * @param array $additional 追加情報
     * @param string|null $transport トランスポート
     * @param bool $catchException 例外キャッチ
     * @return bool 成功時true、失敗時false ($catchExceptionがfalseの場合常にtrue)
     */
    public function sendAutoReplyMail(
        $autoReplyMailHistory,
        bool $adminSend = true,
        array $additional = [],
        ?string $transport = null,
        bool $catchException = false
    ) {
        if (!$autoReplyMailHistory instanceof AutoReplyMailHistory) {
            throw new CakeException();
        }
        if ($autoReplyMailHistory->isSent()) {
            return true;
        }

        $result = $this->getConnection()->transactional(function () use (
            $autoReplyMailHistory,
            $adminSend,
            $additional,
            $transport,
            $catchException
        ) {
            $autoReplyMailHistory->set('send_flg', AutoReplyMailHistory::SEND_FLG_ON);
            $autoReplyMailHistory->set(
                'send_timestamp',
                $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s')
            );

            if (!$this->save($autoReplyMailHistory)) {
                throw new CakeException();
            }

            /** @var \App\Mailer\DefaultMailer $mailer */
            $mailer = $this->getMailer('default');

            if (
                !$mailer->sendAutoReplyMail(
                    $autoReplyMailHistory,
                    $adminSend,
                    $additional,
                    $transport,
                    $catchException
                )
            ) {
                return false;
            }

            return true;
        });

        return $result;
    }

    /**
     * 自動返信メールを再送
     *
     * @param \App\Model\Entity\AutoReplyMailHistory $autoReplyMailHistory 自動返信メール送信履歴
     * @return void
     */
    public function resendAutoReplyMail(AutoReplyMailHistory $autoReplyMailHistory)
    {
        $this->sendAutoReplyMail($autoReplyMailHistory, true);
    }

    /**
     * 予約リマインダーを送信
     *
     * @param int $reminderInterval 送信間隔
     * @param string|\DateTimeInterface $targetDateTime 対象日時(現在日時)
     * @return void
     */
    public function sendReservationReminder(int $reminderInterval, $targetDateTime)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('reservation_reminder_flg')) {
            return;
        }

        $this->getLockForReminder();

        $reservations = $reservationsTable->find('reminder', [
            'inputs' => [
                'reminder_interval' => $reminderInterval,
                'target_date_time' => $targetDateTime,
            ],
        ]);

        $allCount = 0;
        $errorCount = 0;
        foreach ($reservations as $reservation) {
            try {
                $autoReplyMailHistory = $this->generateDataForReservation(
                    $reservation,
                    AutoReplyMail::TYPE_RESERVE_REMINDER
                );
                if (isset($autoReplyMailHistory)) {
                    $reminderUsageTimestamp = $siteSettingsTable->getData()->getReminderUsageTimestamp(
                        $reminderInterval,
                        $reservation->get('usage_timestamp_from')
                    );

                    $autoReplyMailHistory->set('reminder_setting_timestamp', $reminderUsageTimestamp);
                    $autoReplyMailHistory->setDataEntity([
                        'user' => $reservation->getUserEntity(),
                        'reservation' => $reservation,
                    ]);

                    $this->sendAutoReplyMail($autoReplyMailHistory, false);
                }
            } catch (Throwable $e) {
                try {
                    $this->getErrorLogger()->log($e, Router::getRequest());
                } catch (Throwable $e2) {
                    // DO NOTHING
                }
                $errorCount += 1;
            }
            $allCount += 1;
        }

        $this->releaseLockForReminder();

        // エラーメール送信
        if ($errorCount > 0) {
            /** @var \App\Mailer\ErrorMailer $errorMailer */
            $errorMailer = $this->getMailer('Error');

            $errorMailer->sendReminderErrorMail($errorCount, $allCount);
        }
    }

    /**
     * 利用終了リマインダーを送信
     *
     * @param int $reminderInterval 送信間隔
     * @param string|\DateTimeInterface $targetDateTime 対象日時(現在日時)
     * @return void
     */
    public function sendReservationCloseReminder(int $reminderInterval, $targetDateTime)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('reservation_close_reminder_flg')) {
            return;
        }

        $this->getLockForCloseReminder();

        $reservations = $reservationsTable->find('closeReminder', [
            'inputs' => [
                'reminder_interval' => $reminderInterval,
                'target_date_time' => $targetDateTime,
            ],
        ]);

        $allCount = 0;
        $errorCount = 0;
        foreach ($reservations as $reservation) {
            try {
                $autoReplyMailHistory = $this->generateDataForReservation(
                    $reservation,
                    AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE
                );
                if (isset($autoReplyMailHistory)) {
                    $reminderUsageTimestamp = $siteSettingsTable->getData()->getCloseReminderUsageTimestamp(
                        $reminderInterval,
                        $reservation->get('usage_timestamp_to')
                    );

                    $autoReplyMailHistory->set('reminder_setting_timestamp', $reminderUsageTimestamp);
                    $autoReplyMailHistory->setDataEntity([
                        'user' => $reservation->getUserEntity(),
                        'reservation' => $reservation,
                    ]);

                    $this->sendAutoReplyMail($autoReplyMailHistory, false);
                }
            } catch (Throwable $e) {
                try {
                    $this->getErrorLogger()->log($e, Router::getRequest());
                } catch (Throwable $e2) {
                    // DO NOTHING
                }
                $errorCount += 1;
            }
            $allCount += 1;
        }

        $this->releaseLockForCloseReminder();

        // エラーメール送信
        if ($errorCount > 0) {
            /** @var \App\Mailer\ErrorMailer $errorMailer */
            $errorMailer = $this->getMailer('Error');

            $errorMailer->sendCloseReminderErrorMail($errorCount, $allCount);
        }
    }

    /**
     * バウンスメールトークンを生成
     *
     * @return string
     */
    protected function generateBounceMailToken()
    {
        $random = Security::randomString(static::BOUNCE_MAIL_TOKEN_LENGTH);
        $token = static::BOUNCE_MAIL_TOKEN_PREFIX . $random;

        return $token;
    }

    /**
     * 予約リマインダーのロックを取得
     *
     * @return void
     */
    protected function getLockForReminder()
    {
        $this->getLock(static::LOCK_TYPE_REMINDER_MAIL, static::REMINDER_LOCK_CODE);
    }

    /**
     * 予約リマインダーのロックを解放
     *
     * @return void
     */
    protected function releaseLockForReminder()
    {
        $this->releaseLock(static::LOCK_TYPE_REMINDER_MAIL, static::REMINDER_LOCK_CODE);
    }

    /**
     * 利用終了リマインダーのロックを取得
     *
     * @return void
     */
    protected function getLockForCloseReminder()
    {
        $this->getLock(static::LOCK_TYPE_REMINDER_MAIL, static::REMINDER_CLOSE_LOCK_CODE);
    }

    /**
     * 利用終了リマインダーのロックを解放
     *
     * @return void
     */
    protected function releaseLockForCloseReminder()
    {
        $this->releaseLock(static::LOCK_TYPE_REMINDER_MAIL, static::REMINDER_CLOSE_LOCK_CODE);
    }
}
