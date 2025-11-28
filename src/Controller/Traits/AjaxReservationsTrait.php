<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use App\Locale\Message;
use App\Model\EventCalendar\PaginateTypeInterface;
use App\Model\Table\ReservationsTable;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;

/**
 * AjaxReservations trait.
 */
trait AjaxReservationsTrait
{
    use FileTrait;

    /**
     * Calendar action
     *
     * @param string $sessionKey セッションキー
     * @param bool $selectCalendar カレンダー選択
     * @param \App\Form\Common\Reservations\CalendarForm $calendarForm カレンダーフォーム
     * @param bool $isAdmin 管理者側フラグ
     * @param bool $saveSession セッション保存フラグ
     * @return void
     */
    protected function calendarAction($sessionKey, $selectCalendar, $calendarForm, $isAdmin, $saveSession)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        // カレンダークラス取得
        $eventCalendar = $calendarForm->createEventCalendarInstance(
            $this->SearchInput->getCondition([], $this->getRequest()->getSession()->read($sessionKey . '.data'))
        );
        if (!isset($eventCalendar)) {
            throw new BadRequestException();
        }

        // 入力値取得
        $searchInputs = $this->SearchInput->getCondition(
            $calendarForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read($sessionKey . '.data'),
            $this->getRequest()->getSession()->read($sessionKey . '.parameter')
        );

        if ($eventCalendar instanceof PaginateTypeInterface) {
            $paginateDefaultValues = $eventCalendar->paginateDefaultValues();
            $searchInputs['limit'] = (string)$paginateDefaultValues['limit'];
        }

        $searchInputs['display_all_time'] = $this->getRequest()->getCookie(
            'display_all_time',
            Configure::read('Master.common.flg.off')
        );
        $searchInputs['calendar_type'] = $eventCalendar->getCalendarType();
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($calendarForm->execute($searchInputs)) {
            $searchData = $calendarForm->getData();
        } else {
            $eventCalendar = $calendarForm->createEventCalendarInstance(
                $this->getRequest()->getSession()->read($sessionKey . '.data')
            );
            if (!isset($eventCalendar)) {
                throw new CakeException();
            }

            $searchData = $this->SearchInput->getFallbackCondition(
                $calendarForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read($sessionKey . '.data'),
                $this->getRequest()->getSession()->read($sessionKey . '.parameter')
            );
            $calendarForm->setData($searchData);
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // 会員ID
        $userId = null;
        if ($isAdmin) {
            $userId = Hash::get($searchData, 'user_id');
        } else {
            if ($this->commonData()->existsUserLoginData()) {
                $userId = $this->commonData()->getUserLoginData()->get('id');
            }
        }
        if (isset($userId)) {
            $userId = (int)$userId;
        } else {
            $userId = null;
        }

        $continuousData = null;
        if ($this->getRequest()->getSession()->check('reservations.add.continuousParameter')) {
            $continuousData = $this->getRequest()->getSession()->read('reservations.add.continuousData');
            if (!is_array($continuousData)) {
                $continuousData = null;
            }
        }

        // カレンダー生成
        $reservationsTable->createEventCalendar(
            $eventCalendar,
            $searchData,
            $this->Pagination,
            $userId,
            $selectCalendar,
            $continuousData
        );
        $date = $eventCalendar->getDate();
        if (isset($date)) {
            $searchData['date'] = $date->format('Y/m/d');
            $calendarForm->setData($searchData);
        }

        // セッション保持
        if ($saveSession) {
            $this->getRequest()->getSession()->write($sessionKey . '.data', $searchData);
            $this->getRequest()->getSession()->write($sessionKey . '.parameter', $calendarForm->getParameters());
        }

        // ビュー変数
        $this->set([
            'calendarForm' => $calendarForm,
            'valueOptions' => $calendarForm->getFieldValueOptions(),
            'selectCalendar' => $selectCalendar,
        ]);
    }

    /**
     * CalendarPopup action
     *
     * @param string $sessionKey セッションキー
     * @param bool $selectCalendar カレンダー選択
     * @param \App\Form\Common\Reservations\CalendarPopupForm $calendarPopupForm ポップアップフォーム
     * @return void
     */
    protected function calendarPopupAction($sessionKey, $selectCalendar, $calendarPopupForm)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        // ポップアップクラス取得
        $calendarPopup = $reservationsTable->getEventCalendarPopup($this->getRequest()->getQuery('popup_type'));
        if (!isset($calendarPopup)) {
            throw new BadRequestException();
        }

        // 入力値取得
        $searchInputs = (array)$this->getRequest()->getData();
        if ($this->getRequest()->getSession()->check($sessionKey . '.parameter')) {
            $searchInputs += (array)$this->getRequest()->getSession()->read($sessionKey . '.parameter');
        }

        // 入力チェック
        $calendarPopupForm->setCalendarPopup($calendarPopup);
        if (!$calendarPopupForm->execute($searchInputs)) {
            throw new BadRequestException();
        }

        // 会員ID
        $userId = null;
        if ($calendarPopup->isAdmin()) {
            $userId = $calendarPopupForm->getData('user_id');
        } else {
            if ($this->commonData()->existsUserLoginData()) {
                $userId = $this->commonData()->getUserLoginData()->get('id');
            }
        }
        if (isset($userId)) {
            $userId = (int)$userId;
        } else {
            $userId = null;
        }

        $continuousData = null;
        if ($this->getRequest()->getSession()->check('reservations.add.continuousParameter')) {
            $continuousData = $this->getRequest()->getSession()->read('reservations.add.continuousData');
            if (!is_array($continuousData)) {
                $continuousData = null;
            }
        }

        // カレンダー生成
        $reservationsTable->createEventCalendarPopup(
            $calendarPopup,
            $calendarPopupForm->getData(),
            $userId,
            $selectCalendar,
            $continuousData
        );
        if (empty($calendarPopup->getEvents())) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // ビュー変数
        $this->set([
            'calendarPopupForm' => $calendarPopupForm,
            'valueOptions' => $calendarPopupForm->getFieldValueOptions(),
            'selectCalendar' => $selectCalendar,
        ]);
    }

    /**
     * ChangeForm action
     *
     * @param \App\Form\Common\Reservations\ReservationForm $reservationForm 予約フォーム
     * @param string $addTokenKey 登録トークンキー
     * @param string $editTokenKey 編集トークンキー
     * @return void
     */
    protected function changeFormAction($reservationForm, $addTokenKey, $editTokenKey)
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        $mode = $this->getRequest()->getQuery('mode');
        if (!in_array($mode, ['add', 'edit', 'detail'], true)) {
            throw new BadRequestException();
        }

        if ($mode === 'add') {
            $reservationForm->setContinuousParameter([
                'key' => $this->getRequest()->getQuery('continuous_key'),
                'parameter' => $this->getRequest()->getSession()->read('reservations.add.continuousParameter'),
                'data' => $this->getRequest()->getSession()->read('reservations.add.continuousData'),
            ]);
            if (!$reservationForm->validateContinuousParameter()) {
                throw new BadRequestException();
            }

            $data = $this->getRequest()->getData();
            unset($data['users']['password']);
            unset($data['users']['password_confirm']);
            $this->setRequestData($data);
        } else {
            if (!$reservationsTable->validatePrimaryKey($this->getRequest()->getQuery('reservation_id'))) {
                throw new BadRequestException();
            }
            $reservationForm->setReservationEntity(
                $reservationsTable->get($this->getRequest()->getQuery('reservation_id'), [
                    'finder' => 'edit',
                ])
            );
        }

        $reservationForm->setChangeForm(true);

        $reservationForm->setReservationParameter((array)$this->getRequest()->getQuery());
        if (!$reservationForm->validateReservationParameter()) {
            throw new BadRequestException();
        }
        $reservationForm->initializeReservationEntity($this->getRequest()->getData());

        $tokenKey = 'reservations';
        if ($mode === 'add') {
            $tokenKey = $addTokenKey;
        } elseif ($mode === 'edit') {
            $tokenKey = $editTokenKey . '_' . $reservationForm->getReservationEntity()->get('id');
        }
        $this->TokenValidation->generate($tokenKey);

        $this->set([
            'reservationForm' => $reservationForm,
            'valueOptions' => $reservationForm->getFieldValueOptions(),
            'mode' => $mode,
        ]);
    }

    /**
     * SetContinuous method
     *
     * @return void
     */
    protected function setContinuousAction()
    {
        $this->getRequest()->allowMethod('post');

        if (!$this->getRequest()->getSession()->check('reservations.add.continuousData.0.parameter.user_id')) {
            throw new BadRequestException();
        }
        $userId = $this->getRequest()->getSession()->read('reservations.add.continuousData.0.parameter.user_id');

        $this->getRequest()->getSession()->write('reservations.add.continuousParameter', [
            'reservation_type' => ReservationsTable::RESERVATION_TYPE_EXISTING_USER,
            'user_id' => $userId,
        ]);

        $this->set([
            'userId' => $userId,
        ]);
    }

    /**
     * ViewContinuous method
     *
     * @param \App\Form\Common\Reservations\ReservationForm $reservationForm 予約フォーム
     * @return void
     */
    protected function viewContinuousAction($reservationForm)
    {
        $this->getRequest()->allowMethod('post');

        $continuousKey = $this->getRequest()->getQuery('key');
        if (!is_scalar($continuousKey)) {
            throw new BadRequestException();
        }

        if (!$this->getRequest()->getSession()->check('reservations.add.continuousData.' . $continuousKey)) {
            throw new BadRequestException();
        }

        if ($continuousKey) {
            $index = $continuousKey;
        } else {
            $index = Configure::readOrFail('Setting.file.defaultIndex');
        }

        $fileSessionKey = $this->makeFileSessionKey($index);
        $fileSession = $this->request->getSession()->read($fileSessionKey);
        $reservationForm->setFileSession($fileSession, $index);

        $reservationForm->setConfirm(true);
        $reservationForm->setContinuousParameter([
            'key' => $continuousKey,
            'parameter' => $this->getRequest()->getSession()->read('reservations.add.continuousParameter'),
            'data' => $this->getRequest()->getSession()->read('reservations.add.continuousData'),
        ]);
        if (!$reservationForm->validateContinuousParameter()) {
            throw new BadRequestException();
        }

        $reservationForm->setReservationParameter((array)$this->getRequest()->getSession()->read(
            'reservations.add.continuousData.' . $continuousKey . '.parameter'
        ));
        if (!$reservationForm->validateReservationParameter()) {
            throw new BadRequestException();
        }
        $reservationForm->initializeReservationEntity(
            $this->getRequest()->getSession()->read('reservations.add.continuousData.' . $continuousKey . '.data')
        );

        $this->set([
            'reservationForm' => $reservationForm,
            'valueOptions' => $reservationForm->getFieldValueOptions(),
        ]);
    }

    /**
     * RemoveContinuous method
     *
     * @return void
     */
    protected function removeContinuousAction()
    {
        $this->getRequest()->allowMethod('post');

        $continuousKey = $this->getRequest()->getQuery('key');
        $currentContinuousKey = $this->getRequest()->getQuery('current_key');
        if (!is_scalar($continuousKey) || !is_scalar($currentContinuousKey)) {
            throw new BadRequestException();
        }

        if (!$this->getRequest()->getSession()->check('reservations.add.continuousData.' . $continuousKey)) {
            throw new BadRequestException();
        }

        $this->getRequest()->getSession()->delete('reservations.add.continuousData.' . $continuousKey);
        if (empty($this->getRequest()->getSession()->read('reservations.add.continuousData'))) {
            $this->getRequest()->getSession()->delete('reservations.add');
        }

        $sessionKey = 'file.add.' . $continuousKey;
        $files = $this->getRequest()->getSession()->read($sessionKey);
        // 削除した予約のアップロードファイルを削除する
        $this->FileUpload->deleteTempFiles($files);
        $this->getRequest()->getSession()->delete($sessionKey);

        $reloadKey = null;
        if ((string)$currentContinuousKey !== '' && ((string)$currentContinuousKey) !== ((string)$continuousKey)) {
            $reloadKey = $currentContinuousKey;
        }

        $this->set([
            'isEmpty' => !$this->getRequest()->getSession()->check('reservations.add'),
            'reloadKey' => $reloadKey,
        ]);
    }

    /**
     * RemoveAllContinuous method
     *
     * @return void
     */
    protected function removeAllContinuousAction()
    {
        $this->getRequest()->allowMethod('post');

        if (!$this->getRequest()->getSession()->check('reservations.add')) {
            throw new BadRequestException();
        }
        // 連続予約で一時アップロードしていたファイルを削除する
        $this->removeAttachTmpFile();
        $this->getRequest()->getSession()->delete('reservations.add');
        $this->getRequest()->getSession()->delete('file.add');
    }
}
