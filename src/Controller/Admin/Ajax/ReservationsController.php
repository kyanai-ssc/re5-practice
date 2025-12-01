<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\Admin\ReservationsController as BaseReservationsController;
use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxReservationsTrait;
use App\Controller\Traits\AjaxTrait;
use App\Controller\Traits\FileTrait;
use App\Form\Admin\Reservations\CalculateChargeForm;
use App\Form\Admin\Reservations\CalendarDetailForm;
use App\Form\Admin\Reservations\CalendarForm;
use App\Form\Admin\Reservations\CalendarPopupForm;
use App\Form\Admin\Reservations\ReservationForm;
use App\Form\Admin\Reservations\SearchForm;
use App\Form\Admin\Reservations\UpdateStatusForm;
use App\Form\Admin\Reservations\UploadForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;

/**
 * Reservations Controller
 *
 * @property \App\Controller\Component\ImportComponent $Import
 * @property \App\Controller\Component\FileUploadComponent $FileUpload
 */
class ReservationsController extends AdminAppController
{
    use AjaxTrait;
    use AjaxReservationsTrait;
    use FileTrait;

    public const CALENDAR_TIME_OUT = 120;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Ajax');
        $this->loadComponent('Import', [
            'model' => 'Reservations',
        ]);
        $this->loadFileUploadComponent();
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'calendar',
            'calendarPage',
            'calendarPopup',
            'calendarDetail',
            'searchForm',
            'import',
            'updateStatus',
            'saveCheck',
            'changeForm',
            'calculateCharge',
            'setContinuous',
            'viewContinuous',
            'removeContinuous',
            'removeAllContinuous',
            'addVideoMeeting',
            'deleteVideoMeeting',
            'addSmartLock',
            'uploadFile',
            'deleteTmpFile',
        ]);

        return $response;
    }

    /**
     * Calendar method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function calendar()
    {
        set_time_limit(static::CALENDAR_TIME_OUT);

        $sessionKey = 'reservations.calendar.search';
        $selectCalendar = false;
        if (is_scalar($this->getRequest()->getQuery('select_calendar'))) {
            $sessionKey = 'reservations.calendar.select';
            if (is_scalar($this->getRequest()->getQuery('edit_reservation_id'))) {
                $sessionKey = 'reservations.calendar.selectEdit';
            }
            $selectCalendar = true;
        }

        $calendarForm = new CalendarForm();

        $this->calendarAction($sessionKey, $selectCalendar, $calendarForm, true, true);
    }

    /**
     * Calendar page method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function calendarPage()
    {
        set_time_limit(static::CALENDAR_TIME_OUT);

        $sessionKey = 'reservations.calendar.search';
        $selectCalendar = false;
        if (is_scalar($this->getRequest()->getQuery('select_calendar'))) {
            $sessionKey = 'reservations.calendar.select';
            if (is_scalar($this->getRequest()->getQuery('edit_reservation_id'))) {
                $sessionKey = 'reservations.calendar.selectEdit';
            }
            $selectCalendar = true;
        }

        $calendarForm = new CalendarForm();

        $this->calendarAction($sessionKey, $selectCalendar, $calendarForm, true, false);
    }

    /**
     * CalendarPopup method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function calendarPopup()
    {
        $sessionKey = 'reservations.calendar.search';
        $selectCalendar = false;
        if (is_scalar($this->getRequest()->getQuery('select_calendar'))) {
            $sessionKey = 'reservations.calendar.select';
            if (is_scalar($this->getRequest()->getQuery('edit_reservation_id'))) {
                $sessionKey = 'reservations.calendar.selectEdit';
            }
            $selectCalendar = true;
        }

        $calendarPopupForm = new CalendarPopupForm();

        $this->calendarPopupAction($sessionKey, $selectCalendar, $calendarPopupForm);
    }

    /**
     * CalendarDetail method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function calendarDetail()
    {
        $calendarDetailForm = new CalendarDetailForm();
        $searchInputs = $this->SearchInput->getCondition($calendarDetailForm->getDefaultFieldValues());
        if ($this->getRequest()->getSession()->check('reservations.calendar.search.parameter')) {
            $searchInputs += (array)$this->getRequest()->getSession()->read('reservations.calendar.search.parameter');
        }
        if (!$calendarDetailForm->execute($searchInputs)) {
            throw new BadRequestException();
        }

        $searchData = $calendarDetailForm->getData();
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        $reservations = $this->Pagination->paginate($this->fetchTable('Reservations'), [
            'finder' => [
                'calendarDetail' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($calendarDetailForm->getFieldValueOptions('limit'))),
        ]);

        $continuousData = null;
        if ($this->getRequest()->getSession()->check('reservations.add.continuousParameter')) {
            $continuousData = $this->getRequest()->getSession()->read('reservations.add.continuousData');
            if (!is_array($continuousData)) {
                $continuousData = null;
            }
        }

        $calendarDetailForm->setContinuousData($continuousData);
        $eventUnit = $calendarDetailForm->getEventUnit();

        $this->set([
            'reservations' => $reservations,
            'eventUnit' => $eventUnit,
            'calendarDetailForm' => $calendarDetailForm,
        ]);
    }

    /**
     * SearchForm method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function searchForm()
    {
        $this->getRequest()->allowMethod('post');

        $searchForm = new SearchForm();

        $searchPaymentExpiredFlg = null;
        if ($this->isSearchPaymentExpired()) {
            $searchForm->searchPaymentExpiredFlg = true;
            $searchPaymentExpiredFlg = true;
        }

        $searchSmartLockUnlinkedFlg = null;
        if ($this->isSearchReservationUnlinkedSmartLock()) {
            $searchForm->searchSmartLockUnlinkedFlg = true;
            $searchSmartLockUnlinkedFlg = true;
        }

        $this->set([
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'searchPaymentExpiredFlg' => $searchPaymentExpiredFlg,
            'searchSmartLockUnlinkedFlg' => $searchSmartLockUnlinkedFlg,
        ]);
    }

    /**
     * Import method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function import()
    {
        $this->Import->importData(new UploadForm(), false);
    }

    /**
     * UpdateStatus method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function updateStatus()
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->fetchTable('ReservationVideoMeetings');

        $id = $this->getRequest()->getQuery('id');
        if (!$reservationsTable->validatePrimaryKey($id)) {
            throw new BadRequestException();
        }

        $reservation = $reservationsTable->get($id, [
            'finder' => 'edit',
        ]);
        if (!$reservation->canEdit()) {
            throw new BadRequestException();
        }

        $updateStatusForm = new UpdateStatusForm();
        $updateStatusForm->setEntity($reservation);

        $finish = false;
        if ($this->getRequest()->is('post')) {
            if ($updateStatusForm->execute((array)$this->getRequest()->getData())) {
                $finish = $reservationsTable->updateStatus(
                    $reservation,
                    (int)$updateStatusForm->getData('reservation_status_id'),
                    $updateStatusForm->getData('mail_check'),
                    $this->getRequest()->getAttribute('params')
                );
                if (!$finish) {
                    $updateStatusForm->setRulesError();
                }
            }
        } else {
            $this->setRequestData($updateStatusForm->getDefaultFieldValues());
        }

        $valueOptions = [];
        if (!$finish) {
            $valueOptions = $updateStatusForm->getFieldValueOptions();
        }

        $this->set([
            'finish' => $finish,
            'updateStatusForm' => $updateStatusForm,
            'valueOptions' => $valueOptions,
            'statusData' => $updateStatusForm->getStatusData($finish),
            'optionalMessages' => $reservationVideoMeetingsTable->flushErrorMessages(),
        ]);
    }

    /**
     * SaveCheck method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function saveCheck()
    {
        // チェック状態取得
        $checked = $this->SearchInput->getCheckedIds(
            $this->getRequest()->getSession()->read('reservations.list.checked')
        );
        if (!isset($checked['allCheck']) && count($checked) > Configure::readOrFail('Setting.listCheck.limit')) {
            throw new BadRequestException(Message::ERROR_LIST_CHECK_LIMIT);
        }

        // セッション保持
        $this->getRequest()->getSession()->write('reservations.list.checked', $checked);

        $searchPaymentExpiredFlg = null;
        if ($this->isSearchPaymentExpired()) {
            $searchPaymentExpiredFlg = true;
        }

        $searchSmartLockUnlinkedFlg = null;
        if ($this->isSearchReservationUnlinkedSmartLock()) {
            $searchSmartLockUnlinkedFlg = true;
        }

        $this->set([
            'searchPaymentExpiredFlg' => $searchPaymentExpiredFlg,
            'searchSmartLockUnlinkedFlg' => $searchSmartLockUnlinkedFlg,
        ]);
    }

    /**
     * ChangeForm method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function changeForm()
    {
        $reservationForm = new ReservationForm();

        $this->changeFormAction(
            $reservationForm,
            BaseReservationsController::TOKEN_VALIDATION_ADD,
            BaseReservationsController::TOKEN_VALIDATION_EDIT
        );

        $searchPaymentExpiredFlg = null;
        if ($this->isSearchPaymentExpired()) {
            $searchPaymentExpiredFlg = true;
        }

        $searchSmartLockUnlinkedFlg = null;
        if ($this->isSearchReservationUnlinkedSmartLock()) {
            $searchSmartLockUnlinkedFlg = true;
        }

        $this->set([
            'searchPaymentExpiredFlg' => $searchPaymentExpiredFlg,
            'searchSmartLockUnlinkedFlg' => $searchSmartLockUnlinkedFlg,
        ]);
    }

    /**
     * CalculateCharge method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function calculateCharge()
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        $mode = $this->getRequest()->getQuery('mode');
        if (!in_array($mode, ['add', 'edit'], true)) {
            throw new BadRequestException();
        }

        $calculateChargeForm = new CalculateChargeForm();
        if ($mode === 'add') {
            $calculateChargeForm->setContinuousParameter([
                'key' => $this->getRequest()->getQuery('continuous_key'),
                'parameter' => $this->getRequest()->getSession()->read('reservations.add.continuousParameter'),
                'data' => $this->getRequest()->getSession()->read('reservations.add.continuousData'),
            ]);
            if (!$calculateChargeForm->validateContinuousParameter()) {
                throw new BadRequestException(Message::ERROR_CALCULATE_CHARGE);
            }
        } elseif ($mode === 'edit') {
            if (!$reservationsTable->validatePrimaryKey($this->getRequest()->getQuery('reservation_id'))) {
                throw new BadRequestException(Message::ERROR_CALCULATE_CHARGE);
            }
            $calculateChargeForm->setReservationEntity(
                $reservationsTable->get($this->getRequest()->getQuery('reservation_id'), [
                    'finder' => 'edit',
                ])
            );
        }

        $calculateChargeForm->setReservationParameter((array)$this->getRequest()->getQuery());
        if (!$calculateChargeForm->validateReservationParameter()) {
            throw new BadRequestException(Message::ERROR_CALCULATE_CHARGE);
        }

        if (!$calculateChargeForm->execute((array)$this->getRequest()->getData())) {
            throw new BadRequestException(Message::ERROR_CALCULATE_CHARGE);
        }

        $this->set([
            'charge' => $calculateChargeForm->getReservationEntity()->get('charge'),
        ]);
    }

    /**
     * SetContinuous method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function setContinuous()
    {
        $this->setContinuousAction();
    }

    /**
     * ViewContinuous method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function viewContinuous()
    {
        $reservationForm = new ReservationForm();

        $this->viewContinuousAction($reservationForm);
    }

    /**
     * RemoveContinuous method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function removeContinuous()
    {
        $this->removeContinuousAction();
    }

    /**
     * RemoveAllContinuous method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function removeAllContinuous()
    {
        $this->removeAllContinuousAction();
    }

    /**
     * AddVideoMeeting method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function addVideoMeeting()
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->fetchTable('ReservationVideoMeetings');

        $id = $this->getRequest()->getQuery('id');
        if (!$reservationsTable->validatePrimaryKey($id)) {
            throw new BadRequestException();
        }

        $reservation = $reservationsTable->get($id, [
            'finder' => 'edit',
        ]);
        if (!$reservation->canAddVideoMeeting()) {
            throw new BadRequestException();
        }

        $reservationVideoMeetingsTable->videoMeetingProcessOnly($reservation, false, false);

        foreach ($reservationVideoMeetingsTable->flushErrorMessages() as $message) {
            $this->Flash->set($message, [
                'key' => 'reservationsFinish',
                'element' => 'success',
            ]);
        }
        $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
            'key' => 'reservationsFinish',
            'element' => 'success',
        ]);

        $this->set([
            'reservationId' => $reservation->get('id'),
        ]);
    }

    /**
     * DeleteVideoMeeting method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function deleteVideoMeeting()
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->fetchTable('ReservationVideoMeetings');

        $id = $this->getRequest()->getQuery('id');
        if (!$reservationsTable->validatePrimaryKey($id)) {
            throw new BadRequestException();
        }

        $reservation = $reservationsTable->get($id, [
            'finder' => 'edit',
        ]);
        if (!$reservation->canDeleteVideoMeeting()) {
            throw new BadRequestException();
        }

        $reservationVideoMeetingsTable->videoMeetingProcessOnly($reservation, true, false);

        foreach ($reservationVideoMeetingsTable->flushErrorMessages() as $message) {
            $this->Flash->set($message, [
                'key' => 'reservationsFinish',
                'element' => 'success',
            ]);
        }
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'reservationsFinish',
            'element' => 'success',
        ]);

        $this->set([
            'reservationId' => $reservation->get('id'),
        ]);
    }

    /**
     * AddSmartLock method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function addSmartLock()
    {
        $smartLockLinkage = new SmartLockLinkage();

        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $id = $this->getRequest()->getQuery('id');
        if (!$reservationsTable->validatePrimaryKey($id)) {
            throw new BadRequestException();
        }

        // スマートロック連携しない設定の場合はエラー
        $smartLockLinkage = new SmartLockLinkage();
        if (!$smartLockLinkage->useSmartLock()) {
            throw new BadRequestException();
        }

        // 予約を取得
        $reservation = $reservationsTable->get($id, [
            'finder' => 'edit',
        ]);

        // 対象の予約が未連携でなかった場合はエラー
        if (!$reservation->canSmartLockReLink()) {
            throw new BadRequestException();
        }

        // スマートロック連携用の会員情報を取得
        $originalUser = null;
        if ($reservation->has('user_id')) {
            /** @var \Cake\Datasource\EntityInterface|null $originalUser */
            $originalUser = $usersTable->find('SmartLock')
                ->where(['Users.id' => $reservation->get('user_id')])
                ->first();
        }

        // 確定ステータスならスケジュール登録
        $reservation->set('original_user', $originalUser);
        $smartLockLinkage->setErrorMailSendFlg(false);
        $smartLockLinkage->addSchedule($reservation);
        $reservation->set('original_user', null);

        $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
            'key' => 'reservationsFinish',
            'element' => 'success',
        ]);

        $this->set([
            'reservationId' => $reservation->get('id'),
        ]);
    }

    /**
     * Upload method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function uploadFile()
    {
        $this->uploadFileAction();
    }

    /**
     * DeleteTmpFile method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function deleteTmpFile()
    {
        $this->deleteTmpFileAction();
    }

    /**
     * DeleteSavedFile method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function deleteSavedFile()
    {
        $this->deleteSavedFileAction();
    }
}
