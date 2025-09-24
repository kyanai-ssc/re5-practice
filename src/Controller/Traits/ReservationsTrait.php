<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use App\Locale\Message;
use App\Model\Table\ReservationsTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;

/**
 * Reservations trait.
 */
trait ReservationsTrait
{
    /**
     * Calendar action
     *
     * @param string $sessionKey セッションキー
     * @param bool $selectCalendar カレンダー選択
     * @param \App\Form\Common\Reservations\CalendarForm $calendarForm カレンダーフォーム
     * @return void
     */
    protected function calendarAction($sessionKey, $selectCalendar, $calendarForm)
    {
        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete($sessionKey . '.data');
        }

        // カレンダークラス取得
        $eventCalendar = $calendarForm->createEventCalendarInstance(
            $this->SearchInput->getCondition([], $this->getRequest()->getSession()->read($sessionKey . '.data'))
        );
        if (!isset($eventCalendar)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // 入力値取得
        $searchInputs = $this->SearchInput->getCondition(
            $calendarForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read($sessionKey . '.data'),
            $this->getRequest()->getSession()->read($sessionKey . '.parameter')
        );
        $searchInputs['calendar_type'] = $eventCalendar->getCalendarType();

        // 入力チェック
        if ($calendarForm->execute($searchInputs)) {
            $searchData = $calendarForm->getData();
        } else {
            $calendarForm->createEventCalendarInstance(
                $this->getRequest()->getSession()->read($sessionKey . '.data')
            );
            $searchData = $this->SearchInput->getFallbackCondition(
                $calendarForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read($sessionKey . '.data'),
                $this->getRequest()->getSession()->read($sessionKey . '.parameter')
            );
            $calendarForm->setData($searchData);
        }

        // セッション保持
        $this->getRequest()->getSession()->write($sessionKey . '.data', $searchData);
        $this->getRequest()->getSession()->write($sessionKey . '.parameter', $calendarForm->getParameters());

        // ビュー変数
        $this->set([
            'calendarForm' => $calendarForm,
            'selectCalendar' => $selectCalendar,
        ]);
    }

    /**
     * Add action
     *
     * @param \App\Form\Common\Reservations\ReservationForm $reservationForm 予約フォーム
     * @param string $tokenKey トークンキー
     * @return bool
     */
    protected function addAction($reservationForm, $tokenKey)
    {
        // 連続予約パラメータチェック
        $reservationForm->setContinuousParameter([
            'key' => $this->getRequest()->getQuery('key'),
            'parameter' => $this->getRequest()->getSession()->read('reservations.add.continuousParameter'),
            'data' => $this->getRequest()->getSession()->read('reservations.add.continuousData'),
        ]);
        if (!$reservationForm->validateContinuousParameter($this->getRequest()->getQuery('input') === 'back')) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }
        $continuousKey = $reservationForm->getContinuousParameter('key');

        // 予約パラメータチェック
        if ($this->getRequest()->getQuery('input') === 'back') {
            $reservationForm->setReservationParameter((array)$this->getRequest()->getSession()->read(
                'reservations.add.continuousData.' . $continuousKey . '.parameter'
            ));
        } else {
            $reservationForm->setReservationParameter((array)$this->getRequest()->getQuery());
        }
        if (!$reservationForm->validateReservationParameter()) {
            $errors = $reservationForm->getErrors();
            if (isset($errors['reservations']['event_error'])) {
                throw new BadRequestException(reset($errors['reservations']['event_error']));
            }
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            // 入力チェック
            $reservationInputs = $this->getRequest()->getData();
            if ($reservationForm->execute((array)$reservationInputs)) {
                if ($this->TokenValidation->validate($tokenKey)) {
                    $continuousKey = $reservationForm->getContinuousParameter('key');
                    if (
                        isset($reservationInputs['reservations']['repeat_reservation'])
                        && $reservationInputs['reservations']['repeat_reservation'] === '2'
                    ) {
                        $canReserveData = $reservationForm->getData('repeatReservations') ?? null;
                        if ($canReserveData) {
                            foreach ($canReserveData as $continuousKey => $data) {
                                $reservationForm->setReservationParameter(
                                    [
                                        'reservation_type' => ReservationsTable::RESERVATION_TYPE_EXISTING_USER,
                                        'user_id' => Hash::get($data, 'reservations.user_id'),
                                        'event_id' => Hash::get($data, 'reservations.event_id'),
                                        'usage_timestamp_from' =>
                                            Hash::get($data, 'reservations.usage_timestamp_from')->format('Y/m/d H:i'),
                                    ]
                                );
                                $this->getRequest()->getSession()
                                    ->write('reservations.add.continuousData.' . $continuousKey, [
                                        'parameter' => $reservationForm->getReservationParameter(),
                                        'data' => $data,
                                    ]);
                            }
                        }
                    } else {
                        $this->getRequest()->getSession()->write('reservations.add.continuousData.' . $continuousKey, [
                            'parameter' => $reservationForm->getReservationParameter(),
                            'data' => $reservationForm->getData(),
                        ]);
                    }
                    $userEntity = $reservationForm->getReservationEntity()->getUserEntity();
                    if (isset($userEntity) && $userEntity->isDirty('password')) {
                        $this->getRequest()->getSession()->write(
                            'reservations.add.crypt',
                            $userEntity->get('crypt_password')
                        );
                    }

                    return true;
                }
            } else {
                $rulesError = $reservationForm->getRulesError();
                if (isset($rulesError)) {
                    $this->Flash->set($rulesError, [
                        'key' => 'reservationsError',
                        'element' => 'error',
                    ]);
                } else {
                    $this->Flash->set((string)__(Message::INVALID_INPUT), [
                        'key' => 'reservationsError',
                        'element' => 'error',
                    ]);
                }
            }
            $this->setRequestData($reservationForm->getData());
        } else {
            // エンティティ初期化
            $reservationInputs = [];
            if ($this->getRequest()->getQuery('input') === 'back') {
                $reservationInputs = $this->getRequest()->getSession()->read(
                    'reservations.add.continuousData.' . $continuousKey . '.data'
                );
            }
            $reservationForm->initializeReservationEntity($reservationInputs);
        }

        // セッション削除
        if (!$reservationForm->isContinuous()) {
            $this->getRequest()->getSession()->delete('reservations.add');
            $reservationForm->clearContinuousKey();
        }

        // トークン生成
        $this->TokenValidation->generate($tokenKey);

        $this->set([
            'reservationForm' => $reservationForm,
            'valueOptions' => $reservationForm->getFieldValueOptions(),
        ]);

        return false;
    }

    /**
     * Add conf action
     *
     * @param \App\Form\Common\Reservations\ContinuousForm $continuousForm 連続予約フォーム
     * @param string $tokenKey トークンキー
     * @param mixed $mailSendFlg メール送信フラグ
     * @param \App\Model\Entity\OptinToken|null $optinToken オプトイントークン
     * @param mixed $paymentMethodId 決済方法
     * @param array|null $saveOperation 操作ログ
     * @param array|null $kycValues 本人確認情報
     * @return bool
     */
    protected function addConfAction(
        $continuousForm,
        $tokenKey,
        $mailSendFlg,
        $optinToken = null,
        $paymentMethodId = null,
        $saveOperation = null,
        $kycValues = null
    ) {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->fetchTable('ReservationVideoMeetings');

        // 連続予約パラメータチェック
        $continuousForm->setConfirm(true);
        $continuousForm->setContinuousParameter([
            'key' => $this->getRequest()->getQuery('key'),
            'parameter' => $this->getRequest()->getSession()->read('reservations.add.continuousParameter'),
            'data' => $this->getRequest()->getSession()->read('reservations.add.continuousData'),
        ]);
        if (!$continuousForm->validateContinuousParameter()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            // データ登録
            if ($continuousForm->execute((array)$this->getRequest()->getData())) {
                if ($this->TokenValidation->validate($tokenKey)) {
                    $saveOptions = [
                        'mailSendFlg' => $mailSendFlg,
                        'cryptPassword' => $this->getRequest()->getSession()->read('reservations.add.crypt'),
                        'optinToken' => $optinToken,
                        'paymentMethodId' => $paymentMethodId,
                        'paymentTokens' => $continuousForm->getPaymentTokens(),
                        'saveOperation' => $saveOperation,
                        'kycValues' => $kycValues,
                    ];
                    if ($reservationsTable->saveMany($continuousForm->getReservationEntities(), $saveOptions)) {
                        // 登録に失敗したデータを保持
                        $continuousData = $continuousForm->getErrorData();
                        if (!empty($continuousData)) {
                            $this->getRequest()->getSession()->write(
                                'reservations.add.continuousData',
                                $continuousData
                            );
                        } else {
                            $this->getRequest()->getSession()->delete('reservations.add');
                        }

                        // ビデオ会議連携のエラーメッセージ
                        foreach ($reservationVideoMeetingsTable->flushErrorMessages() as $message) {
                            $this->Flash->set($message, [
                                'key' => 'reservationsFinish',
                                'element' => 'success',
                            ]);
                        }

                        return true;
                    }
                }
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'reservationsError',
                    'element' => 'error',
                ]);

                $continuousForm->formatKycErrorMessage();
            }

            $rulesError = $continuousForm->getRulesError(true);
            if (isset($rulesError)) {
                $this->Flash->set($rulesError, [
                    'key' => 'reservationsError',
                    'element' => 'error',
                ]);
            }
        }

        // トークン生成
        $this->TokenValidation->generate($tokenKey);

        $this->set([
            'continuousForm' => $continuousForm,
            'valueOptions' => $continuousForm->getFieldValueOptions(),
        ]);

        return false;
    }

    /**
     * Add finish action
     *
     * @return void
     */
    protected function addFinishAction()
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        $reservationIds = $this->getRequest()->getQuery('id');
        if (!is_array($reservationIds) || empty($reservationIds)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }
        foreach ($reservationIds as $reservationId) {
            if (!$reservationsTable->validatePrimaryKey($reservationId)) {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }
        }

        $this->set([
            'reservationIds' => $reservationIds,
        ]);
    }

    /**
     * View action
     *
     * @param int|null $id 予約ID
     * @param \App\Form\Common\Reservations\ReservationForm $reservationForm 予約フォーム
     * @return void
     */
    protected function viewAction($id, $reservationForm)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        // データ取得
        $reservationForm->setReservationEntity($reservationsTable->get($id, [
            'finder' => 'edit',
        ]));

        // 予約パラメータチェック
        $reservationForm->setReservationParameter([]);
        if (!$reservationForm->validateReservationParameter()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->set([
            'reservationForm' => $reservationForm,
            'valueOptions' => $reservationForm->getFieldValueOptions(),
        ]);
    }

    /**
     * Edit action
     *
     * @param int|null $id 予約ID
     * @param \App\Form\Common\Reservations\ReservationForm $reservationForm 予約フォーム
     * @param string $tokenKey トークンキー
     * @return bool
     */
    protected function editAction($id, $reservationForm, $tokenKey)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        // データ取得
        $reservationForm->setReservationEntity($reservationsTable->get($id, [
            'finder' => 'edit',
        ]));
        if (!$reservationForm->getReservationEntity()->canEdit()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // 予約パラメータチェック
        if ($this->getRequest()->is('post') || $this->getRequest()->getQuery('input') !== 'back') {
            $reservationForm->setReservationParameter((array)$this->getRequest()->getQuery());
        } else {
            if (!$this->getRequest()->getSession()->check('reservations.edit.' . $id)) {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }
            $reservationForm->setReservationParameter(
                (array)$this->getRequest()->getSession()->read('reservations.edit.' . $id . '.parameter')
            );
        }
        if (!$reservationForm->validateReservationParameter()) {
            $errors = $reservationForm->getErrors();
            if (isset($errors['reservations']['event_error'])) {
                throw new BadRequestException(reset($errors['reservations']['event_error']));
            }
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            // 入力チェック
            $reservationInputs = $this->getRequest()->getData();
            if ($reservationForm->execute((array)$reservationInputs)) {
                if ($this->TokenValidation->validate($tokenKey . '_' . $id)) {
                    $this->getRequest()->getSession()->write('reservations.edit.' . $id, [
                        'parameter' => $reservationForm->getReservationParameter(),
                        'data' => $reservationForm->getData(),
                    ]);

                    return true;
                }
            } else {
                $rulesError = $reservationForm->getRulesError();
                if (isset($rulesError)) {
                    $this->Flash->set($rulesError, [
                        'key' => 'reservationsError',
                        'element' => 'error',
                    ]);
                } else {
                    $this->Flash->set((string)__(Message::INVALID_INPUT), [
                        'key' => 'reservationsError',
                        'element' => 'error',
                    ]);
                }
            }
            $this->setRequestData($reservationForm->getData());
        } else {
            // エンティティ初期化
            $reservationInputs = [];
            if ($this->getRequest()->getQuery('input') === 'back') {
                $reservationInputs = $this->getRequest()->getSession()->read('reservations.edit.' . $id . '.data');
            }
            $reservationForm->initializeReservationEntity($reservationInputs);
        }

        // セッション削除
        $this->getRequest()->getSession()->delete('reservations.edit.' . $id);

        // トークン生成
        $this->TokenValidation->generate($tokenKey . '_' . $id);

        $this->set([
            'reservationForm' => $reservationForm,
            'valueOptions' => $reservationForm->getFieldValueOptions(),
        ]);

        return false;
    }

    /**
     * Edit conf action
     *
     * @param int|null $id 予約ID
     * @param \App\Form\Common\Reservations\ReservationForm $reservationForm 予約フォーム
     * @param string $tokenKey トークンキー
     * @param mixed $mailSendFlg メール送信フラグ
     * @param array|null $saveOperation 操作ログ
     * @return bool
     */
    protected function editConfAction($id, $reservationForm, $tokenKey, $mailSendFlg, $saveOperation = null)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->fetchTable('ReservationVideoMeetings');

        // セッションチェック
        if (!$this->getRequest()->getSession()->check('reservations.edit.' . $id)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // データ取得
        $reservationForm->setReservationEntity($reservationsTable->get($id, [
            'finder' => 'edit',
        ]));
        if (!$reservationForm->getReservationEntity()->canEdit()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // 予約パラメータチェック
        $reservationForm->setConfirm(true);
        $reservationForm->setReservationParameter(
            (array)$this->getRequest()->getSession()->read('reservations.edit.' . $id . '.parameter')
        );
        if (!$reservationForm->validateReservationParameter()) {
            $errors = $reservationForm->getErrors();
            if (isset($errors['reservations']['event_error'])) {
                throw new BadRequestException(reset($errors['reservations']['event_error']));
            }
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            // データ更新
            $reservationInputs = $this->getRequest()->getSession()->read('reservations.edit.' . $id . '.data');
            if ($reservationForm->execute((array)$reservationInputs)) {
                if ($this->TokenValidation->validate($tokenKey . '_' . $id)) {
                    $saveOptions = [
                        'mailSendFlg' => $mailSendFlg,
                        'saveOperation' => $saveOperation,
                        'forEdit' => true,
                    ];
                    if ($reservationsTable->save($reservationForm->getReservationEntity(), $saveOptions)) {
                        $this->getRequest()->getSession()->delete('reservations.edit.' . $id);

                        // ビデオ会議連携のエラーメッセージ
                        foreach ($reservationVideoMeetingsTable->flushErrorMessages() as $message) {
                            $this->Flash->set($message, [
                                'key' => 'reservationsFinish',
                                'element' => 'success',
                            ]);
                        }

                        return true;
                    }
                }
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'reservationsError',
                    'element' => 'error',
                ]);
            }

            $rulesError = $reservationForm->getRulesError(true);
            if (isset($rulesError)) {
                $this->Flash->set($rulesError, [
                    'key' => 'reservationsError',
                    'element' => 'error',
                ]);
            }
        } else {
            // エンティティ初期化
            $reservationForm->initializeReservationEntity(
                $this->getRequest()->getSession()->read('reservations.edit.' . $id . '.data')
            );
        }

        // トークン生成
        $this->TokenValidation->generate($tokenKey . '_' . $id);

        $this->set([
            'reservationForm' => $reservationForm,
            'valueOptions' => $reservationForm->getFieldValueOptions(),
        ]);

        return false;
    }

    /**
     * Edit finish action
     *
     * @param int|null $id 予約ID
     * @return void
     */
    protected function editFinishAction($id)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        if (!$reservationsTable->validatePrimaryKey($id)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->set([
            'reservationId' => $id,
        ]);
    }

    /**
     * Cancel action
     *
     * @param int|null $id 予約ID
     * @param \App\Form\Common\Reservations\CancelForm $cancelForm キャンセルフォーム
     * @param mixed $mailSendFlg メール送信フラグ
     * @param array|null $saveOperation 操作ログ
     * @return void
     */
    protected function cancelAction($id, $cancelForm, $mailSendFlg, $saveOperation = null)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->fetchTable('ReservationVideoMeetings');

        $this->getRequest()->allowMethod('post');

        // データ取得
        $reservation = $reservationsTable->get($id, [
            'finder' => 'cancel',
        ]);

        // 入力チェック
        $cancelForm->setEntity($reservation);
        if (!$cancelForm->execute((array)$this->getRequest()->getData())) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // キャンセル処理
        $reservationsTable->cancelReservation($reservation, $mailSendFlg, $saveOperation);

        // ビデオ会議連携のエラーメッセージ
        foreach ($reservationVideoMeetingsTable->flushErrorMessages() as $message) {
            $this->Flash->set($message, [
                'key' => 'reservationsFinish',
                'element' => 'success',
            ]);
        }
    }
}
