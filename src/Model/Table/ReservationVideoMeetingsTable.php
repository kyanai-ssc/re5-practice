<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Organizer;
use App\Model\Entity\Reservation;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Http\Exception\InternalErrorException;
use Cake\Utility\Hash;
use Exception;
use Throwable;

/**
 * ReservationVideoMeetings Model
 *
 * @method \App\Model\Entity\ReservationVideoMeeting newEmptyEntity()
 * @method \App\Model\Entity\ReservationVideoMeeting newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting get($primaryKey, $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationVideoMeeting[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ReservationVideoMeetingsTable extends AppTable
{
    /**
     * @var int
     */
    protected $processCount = 0;

    /**
     * @var array|null
     */
    protected $rollbackMeetings = null;

    /**
     * @var array|null
     */
    protected $errorMessages = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Organizers', [
            'foreignKey' => 'organizer_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Reservations', [
            'foreignKey' => 'reservation_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * ビデオ会議連携の処理を単体で行う
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param bool $deleteFlg 削除フラグ
     * @param bool $checkEnded 終了日時のチェック
     * @return void
     */
    public function videoMeetingProcessOnly(Reservation $reservation, bool $deleteFlg = false, bool $checkEnded = true)
    {
        $lockId = $this->getLockIdForZoom($reservation);
        $lockCode = null;
        if (isset($lockId)) {
            $lockCode = $this->generateLockCode((string)$lockId);
            $this->getLock(static::LOCK_TYPE_ZOOM_CONNECT_USER, $lockCode);
        }

        try {
            $this->videoMeetingProcess($reservation, null, $deleteFlg, $checkEnded);
        } catch (Throwable $e) {
            $this->videoMeetingProcessRollBack($e);
            throw $e;
        } finally {
            if (isset($lockCode)) {
                $this->releaseLock(static::LOCK_TYPE_ZOOM_CONNECT_USER, $lockCode);
            }
        }
    }

    /**
     * ビデオ会議連携の処理を行う
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param \App\Model\Entity\Reservation|null $oldReservation 変更前データ
     * @param bool $deleteFlg 削除フラグ
     * @param bool $checkEnded 終了日時のチェック
     * @return void
     */
    public function videoMeetingProcess(
        Reservation $reservation,
        ?Reservation $oldReservation = null,
        bool $deleteFlg = false,
        bool $checkEnded = true
    ) {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if (
            !$systemSettingsTable->getData()->canCoordinateVideoMeeting()
            || (isset($oldReservation) && !$this->shouldProcessOnEdit($reservation, $oldReservation))
        ) {
            return;
        }

        // ロールバック用
        $this->rollbackMeetings = [];

        $result = $this->getConnection()->transactional(function () use ($reservation, $deleteFlg, $checkEnded) {
            /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
            $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');

            // 登録済みのビデオ会議情報
            $reserved = $reservation->getReservedVideoMeeting();

            $deleting = null;
            if (!$deleteFlg && $this->shouldProcessOnReserve($reservation, $checkEnded)) {
                // 枠のビデオ会議主催者
                $organizer = $reservation->getEventOrganizer();
                if (!isset($organizer)) {
                    throw new CakeException();
                }

                // 制限カウント用
                $this->processCount += 1;

                if (
                    !isset($reserved)
                    || (string)$reserved->get('organizer_id') !== (string)$organizer->get('id')
                    || (string)$reserved->get('video_meeting_type') !== (string)$organizer->get('video_meeting_type')
                ) {
                    // ビデオ会議作成
                    $videoMeeting = $this->createMeeting($reservation);
                } else {
                    // ビデオ会議更新 (更新エラー時は再作成)
                    $videoMeeting = $this->updateMeeting($reservation, $reserved);
                }

                // ビデオ会議作成時は旧データを削除
                if (isset($reserved) && (!isset($videoMeeting) || $videoMeeting->isNew())) {
                    $this->deleteOrFail($reserved);
                    $deleting = $reserved;
                }

                // ビデオ会議情報をDBへ保存
                if (isset($videoMeeting) && $videoMeeting->isNew()) {
                    $this->saveOrFail($videoMeeting);

                    // 自動返信メール用のデータ
                    if ($reservation->has('auto_reply_mail_histories')) {
                        foreach ($reservation->get('auto_reply_mail_histories') as $autoReplyMailHistory) {
                            $mailData = $autoReplyMailHistory->get('data');
                            if (isset($mailData['reservation'])) {
                                $mailData['reservation'] = $reservation->toArray();
                                $autoReplyMailHistory->set('data', $mailData);
                                $autoReplyMailHistoriesTable->saveOrFail($autoReplyMailHistory);
                            }
                        }
                    }
                }
            } elseif (isset($reserved) && $this->shouldProcessOnDelete($reservation, $checkEnded)) {
                $deleting = $reserved;

                // 制限カウント用
                $this->processCount += 1;

                $this->deleteAll([
                    'reservation_id' => $reservation->get('id'),
                ]);
            }

            // ビデオ会議削除
            if (isset($deleting)) {
                $this->deleteMeeting($reservation, $deleting);
            }

            return true;
        });
        if (!$result) {
            throw new CakeException();
        }
    }

    /**
     * ビデオ会議連携のロールバックを行う
     *
     * @param \Throwable|null $exception 発生した例外
     * @return void
     */
    public function videoMeetingProcessRollBack($exception = null)
    {
        if (!isset($this->rollbackMeetings)) {
            return;
        }
        $rollbackMeetings = $this->rollbackMeetings;
        $this->rollbackMeetings = null;

        $result = true;
        foreach ($rollbackMeetings as $meeting) {
            // エラー発生時も次のデータのロールバックは行う
            $success = $this->executeSafe(function () use ($meeting) {
                $apiResult = $meeting['module']->rollbackApi();
                if (!isset($apiResult)) {
                    throw new CakeException();
                }

                foreach ($apiResult as $data) {
                    if ($data['type'] === 'delete') {
                        $this->rollbackDeletingVideoMeeting(
                            $meeting['reservation'],
                            $meeting['organizer'],
                            $data['result']
                        );
                    }
                }
            }, false);
            if (!$success) {
                $result = false;
            }
        }

        if (!$result) {
            // InternalErrorException の第3引数に指定するため
            if (!is_null($exception) && !($exception instanceof Exception)) {
                $exception = new CakeException($exception->__toString());
            }

            throw new InternalErrorException(Message::ERROR_VIDEO_MEETING_ROLLBACK, null, $exception);
        }
    }

    /**
     * 予約時の連携要否を判定
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param bool $checkEnded 終了日時のチェック
     * @return bool
     */
    public function shouldProcessOnReserve(Reservation $reservation, $checkEnded = true)
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if (
            !$systemSettingsTable->getData()->canCoordinateVideoMeeting()
            || is_null($reservation->getEventOrganizer())
            || !$reservation->isKeepStockStatus()
            || ($checkEnded && $reservation->isReservationEnded())
        ) {
            return false;
        }

        return true;
    }

    /**
     * 予約変更時の連携要否を判定
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param \App\Model\Entity\Reservation $oldEntity 変更前データ
     * @return bool
     */
    public function shouldProcessOnEdit(Reservation $reservation, Reservation $oldEntity)
    {
        $data = [
            'event' => $reservation->get('event_id'),
            'from' => $reservation->getUsageTimestampFrom()->format('Y-m-d H:i:s'),
            'to' => $reservation->getUsageTimestampTo()->format('Y-m-d H:i:s'),
            'status' => $reservation->isKeepStockStatus(),
        ];
        $oldData = [
            'event' => $oldEntity->get('event_id'),
            'from' => $oldEntity->getUsageTimestampFrom()->format('Y-m-d H:i:s'),
            'to' => $oldEntity->getUsageTimestampTo()->format('Y-m-d H:i:s'),
            'status' => $oldEntity->isKeepStockStatus(),
        ];

        $change = false;
        foreach ($data as $key => $value) {
            if ((string)$value !== (string)$oldData[$key]) {
                $change = true;
                break;
            }
        }
        if (!$change) {
            return false;
        }

        return true;
    }

    /**
     * 削除時の連携要否を判定
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param bool $checkEnded 終了日時のチェック
     * @return bool
     */
    public function shouldProcessOnDelete(Reservation $reservation, $checkEnded = true)
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if (
            !$systemSettingsTable->getData()->canCoordinateVideoMeeting()
            || is_null($reservation->getReservedVideoMeeting())
            || ($checkEnded && $reservation->isReservationEnded())
        ) {
            return false;
        }

        return true;
    }

    /**
     * API連携の回数制限チェック
     *
     * @return bool
     */
    public function withinApiLimit()
    {
        if ($this->processCount >= Configure::readOrFail('Setting.videoMeeting.apiLimit')) {
            return false;
        }

        return true;
    }

    /**
     * 連携エラー時に表示するメッセージを取得する
     *
     * @return array
     */
    public function flushErrorMessages()
    {
        $out = array_keys(array_filter((array)$this->errorMessages));
        $this->errorMessages = null;

        return $out;
    }

    /**
     * Zoom連携ユーザーのロック用IDを取得
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return int|null
     */
    public function getLockIdForZoom($reservation)
    {
        /** @var \App\Model\Table\ZoomConnectUsersTable $zoomConnectUsersTable */
        $zoomConnectUsersTable = $this->getTableLocator()->get('ZoomConnectUsers');

        $organizer = $reservation->getEventOrganizer();
        if (
            !isset($organizer)
            || (string)$organizer->get('video_meeting_type') !== (string)Organizer::VIDEO_MEETING_TYPE_ZOOM
            || (string)$organizer->get('zoom_connect_type') !== (string)Organizer::ZOOM_CONNECT_TYPE_OAUTH
        ) {
            return null;
        }

        $zoomConnectUser = $zoomConnectUsersTable->getData();
        if (!isset($zoomConnectUser)) {
            return null;
        }

        return $zoomConnectUser->get('id');
    }

    /**
     * 連携エラー時に表示するメッセージをセットする
     *
     * @param string $message メッセージ
     * @return void
     */
    protected function addErrorMessage($message)
    {
        if (!isset($this->errorMessages)) {
            $this->errorMessages = [];
        }
        $this->errorMessages[$message] = true;
    }

    /**
     * ビデオ会議を作成
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return \App\Model\Entity\ReservationVideoMeeting|null
     */
    protected function createMeeting($reservation)
    {
        $event = $reservation->getEventEntity();
        $organizer = $reservation->getEventOrganizer();
        if (!isset($event) || !isset($organizer)) {
            throw new CakeException();
        }

        if ((string)$organizer->get('video_meeting_type') === (string)Organizer::VIDEO_MEETING_TYPE_ZOOM) {
            $options = [
                'topic' => $event->get('name'),
            ];
        } elseif ((string)$organizer->get('video_meeting_type') === (string)Organizer::VIDEO_MEETING_TYPE_MEET) {
            $options = [
                'summary' => $event->get('name'),
            ];
        } else {
            throw new CakeException();
        }

        // ビデオ会議連携用のモジュール
        $videoMeetingModule = $organizer->createVideoMeetingModule();

        // ロールバック用に保持
        $this->rollbackMeetings[] = [
            'reservation' => $reservation,
            'organizer' => $organizer,
            'module' => $videoMeetingModule,
        ];

        // API連携
        try {
            $apiResult = $videoMeetingModule->createMeeting(
                $reservation->get('usage_timestamp_from'),
                $reservation->get('usage_timestamp_to'),
                $options
            );
        } catch (Exception $e) {
            // エラーメッセージの出し分け
            $message = Message::ERROR_CREATE_VIDEO_MEETING;
            if (!$this->commonData()->existsAdminLoginData()) {
                $message = Message::INFO_VIDEO_MEETING_FAILED_PUBLIC;
            }
            throw new InternalErrorException($message, null, $e);
        }

        // 連携エラー時は完了画面にエラー表示
        if (!isset($apiResult)) {
            $messages = [
                Organizer::VIDEO_MEETING_TYPE_ZOOM => __(Message::INFO_CREATE_INVALID_ZOOM),
                Organizer::VIDEO_MEETING_TYPE_MEET => __(Message::INFO_CREATE_INVALID_SERVICE_ACCOUNT),
            ];
            $this->addErrorMessage((string)$messages[$organizer->get('video_meeting_type')]);

            return null;
        }

        // エンティティ生成
        $videoMeeting = $this->createEntityByApiResult($organizer, $apiResult);
        $videoMeeting->set('reservation_id', $reservation->get('id'));
        $reservation->set('reservation_video_meetings', [$videoMeeting]);

        return $videoMeeting;
    }

    /**
     * ビデオ会議を更新 (更新エラー時は再作成)
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param \App\Model\Entity\ReservationVideoMeeting $videoMeeting ビデオ会議予約
     * @return \App\Model\Entity\ReservationVideoMeeting|null
     */
    protected function updateMeeting($reservation, $videoMeeting)
    {
        $event = $reservation->getEventEntity();
        $organizer = $reservation->getEventOrganizer();
        if (!isset($event) || !isset($organizer)) {
            throw new CakeException();
        }

        if ((string)$organizer->get('video_meeting_type') === (string)Organizer::VIDEO_MEETING_TYPE_ZOOM) {
            $options = [
                'topic' => $event->get('name'),
            ];
        } elseif ((string)$organizer->get('video_meeting_type') === (string)Organizer::VIDEO_MEETING_TYPE_MEET) {
            $options = [
                'summary' => $event->get('name'),
            ];
        } else {
            throw new CakeException();
        }

        // ビデオ会議連携用のモジュール
        $videoMeetingModule = $organizer->createVideoMeetingModule();

        // ロールバック用に保持
        $this->rollbackMeetings[] = [
            'reservation' => $reservation,
            'organizer' => $organizer,
            'module' => $videoMeetingModule,
        ];

        // API連携
        try {
            $apiResult = $videoMeetingModule->updateMeeting(
                $videoMeeting->get('video_meeting_id'),
                $reservation->get('usage_timestamp_from'),
                $reservation->get('usage_timestamp_to'),
                $options
            );
        } catch (Exception $e) {
            // エラーメッセージの出し分け
            $message = Message::ERROR_UPDATE_VIDEO_MEETING;
            if (!$this->commonData()->existsAdminLoginData()) {
                $message = Message::INFO_VIDEO_MEETING_FAILED_PUBLIC;
            }
            throw new InternalErrorException($message, null, $e);
        }

        // 連携エラー時は完了画面にエラー表示しビデオ会議を再作成
        if (!isset($apiResult)) {
            $messages = [
                Organizer::VIDEO_MEETING_TYPE_ZOOM => __(Message::INFO_UPDATE_CHANGE_ZOOM),
                Organizer::VIDEO_MEETING_TYPE_MEET => __(Message::INFO_UPDATE_CHANGE_SERVICE_ACCOUNT),
            ];
            $this->addErrorMessage((string)$messages[$organizer->get('video_meeting_type')]);

            return $this->createMeeting($reservation);
        }

        return $videoMeeting;
    }

    /**
     * ビデオ会議を削除
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param \App\Model\Entity\ReservationVideoMeeting $videoMeeting ビデオ会議予約
     * @return bool
     */
    protected function deleteMeeting($reservation, $videoMeeting)
    {
        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->getTableLocator()->get('Organizers');

        $organizer = $organizersTable->getOrganizerForApi($videoMeeting->get('organizer_id'));
        if (
            !isset($organizer)
            || (string)$videoMeeting->get('video_meeting_type') !== (string)$organizer->get('video_meeting_type')
        ) {
            return false;
        }

        // API連携用のモジュール
        $videoMeetingModule = $organizer->createVideoMeetingModule();

        // ロールバック用に保持
        $this->rollbackMeetings[] = [
            'reservation' => $reservation,
            'organizer' => $organizer,
            'module' => $videoMeetingModule,
        ];

        // API連携
        try {
            $apiResult = $videoMeetingModule->deleteMeeting($videoMeeting->get('video_meeting_id'));
        } catch (Exception $e) {
            // エラーメッセージの出し分け
            $message = Message::ERROR_DELETE_VIDEO_MEETING;
            if (!$this->commonData()->existsAdminLoginData()) {
                $message = Message::INFO_VIDEO_MEETING_FAILED_PUBLIC;
            }
            throw new InternalErrorException($message, null, $e);
        }

        // 連携エラー時は完了画面にエラー表示
        if (!isset($apiResult)) {
            $messages = [
                Organizer::VIDEO_MEETING_TYPE_ZOOM => __(Message::ERROR_DELETE_CHANGE_ZOOM),
                Organizer::VIDEO_MEETING_TYPE_MEET => __(Message::ERROR_DELETE_CHANGE_SERVICE_ACCOUNT),
            ];
            $this->addErrorMessage((string)$messages[$organizer->get('video_meeting_type')]);

            return false;
        }

        return true;
    }

    /**
     * APIの結果からエンティティを生成
     *
     * @param \App\Model\Entity\Organizer $organizer 主催者
     * @param array $apiResult APIの結果
     * @return \App\Model\Entity\ReservationVideoMeeting
     */
    protected function createEntityByApiResult($organizer, $apiResult)
    {
        $data = [
            'organizer_id' => $organizer->get('id'),
            'video_meeting_type' => $organizer->get('video_meeting_type'),
        ];

        if ((string)$organizer->get('video_meeting_type') === (string)Organizer::VIDEO_MEETING_TYPE_ZOOM) {
            $data += [
                'video_meeting_url' => Hash::get($apiResult, 'join_url'),
                'video_meeting_id' => Hash::get($apiResult, 'id'),
                'video_meeting_password' => Hash::get($apiResult, 'password'),
            ];
        } elseif ((string)$organizer->get('video_meeting_type') === (string)Organizer::VIDEO_MEETING_TYPE_MEET) {
            $data += [
                'video_meeting_url' => Hash::get($apiResult, 'event.hangoutLink'),
                'video_meeting_id' => Hash::get($apiResult, 'event.id'),
            ];
        } else {
            throw new CakeException();
        }

        return $this->newEntity($data, ['validate' => false]);
    }

    /**
     * ビデオ会議削除のロールバック
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param \App\Model\Entity\Organizer $organizer 主催者
     * @param array $apiResult APIの結果
     * @return void
     */
    protected function rollbackDeletingVideoMeeting($reservation, $organizer, $apiResult)
    {
        $newVideoMeeting = $this->createEntityByApiResult($organizer, $apiResult);
        $newVideoMeeting->set('reservation_id', $reservation->get('id'));
        $reservation->set('reservation_video_meetings', [$newVideoMeeting]);

        $result = $this->getConnection()->transactional(function () use ($newVideoMeeting, $reservation) {
            $this->deleteAll([
                'reservation_id' => $reservation->get('id'),
            ]);
            $this->saveOrFail($newVideoMeeting);

            return true;
        });
        if (!$result) {
            throw new CakeException();
        }
    }
}
