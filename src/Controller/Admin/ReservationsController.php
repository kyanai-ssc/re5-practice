<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Controller\Traits\ReservationsTrait;
use App\Form\Admin\Reservations\CalendarForm;
use App\Form\Admin\Reservations\CancelForm;
use App\Form\Admin\Reservations\ContinuousForm;
use App\Form\Admin\Reservations\DeleteForm;
use App\Form\Admin\Reservations\DeleteManyForm;
use App\Form\Admin\Reservations\ReservationForm;
use App\Form\Admin\Reservations\SearchForm;
use App\Locale\Message;
use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\ReservationPayment;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Hash;

/**
 * Reservations Controller
 *
 * @property \App\Controller\Component\FileUploadComponent $FileUpload
 */
class ReservationsController extends AdminAppController
{
    use ReservationsTrait;

    public const TOKEN_VALIDATION_ADD = 'reservations_add';
    public const TOKEN_VALIDATION_EDIT = 'reservations_edit';

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadFileUploadComponent();
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'cancel',
            'delete',
            'download',
            'sample',
        ]);

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'list',
        ], 301);
    }

    /**
     * List method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function list()
    {
        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('reservations.list.search');
            $this->getRequest()->getSession()->delete('reservations.list.checked');
        }
        if ($this->SearchInput->checkSearchButtonClick()) {
            $this->getRequest()->getSession()->delete('reservations.list.checked');
        }

        // 入力値取得
        $searchForm = new SearchForm();

        if ($this->isSearchPaymentExpired()) {
            $searchForm->searchPaymentExpiredFlg = true;
        }

        if ($this->isSearchReservationUnlinkedSmartLock()) {
            $searchForm->searchSmartLockUnlinkedFlg = true;
        }

        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('reservations.list.search')
        );

        $searchPaymentExpiredFlg = null;
        // 決済期限切れのリンクから遷移時、決済期限切れで絞り込む
        if ($this->isSearchPaymentExpired()) {
            $searchPaymentExpiredFlg = true;
            $target = Configure::readOrFail(
                'Master.adminSearchItems.itemsSearchInputKey.' . AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS
            );
            $inputReservationPaymentStatus = Hash::get($searchInputs, $target);
            $expiredValue = (string)ReservationPayment::DISPLAY_STATUS_EXPIRED;

            if (!is_array($inputReservationPaymentStatus)) {
                $searchInputs[$target] = [$expiredValue];
            } elseif (!in_array($expiredValue, $inputReservationPaymentStatus, true)) {
                $searchInputs[$target][] = $expiredValue;
            }
        }

        $searchSmartLockUnlinkedFlg = null;
        // スマートロック未連携のリンクから遷移時、スマートロック未連携、ステータス、利用日時を絞り込む
        if ($this->isSearchReservationUnlinkedSmartLock()) {
            $searchSmartLockUnlinkedFlg = true;

            // 必要な項目(スマートロック連携項目・ステータス・利用日時)をセット
            $searchInputs = $searchForm->setSmartLockSearchParameter($searchInputs);
        }

        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('reservations.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $reservations = $this->Pagination->paginate($this->fetchTable('Reservations'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                    'defaultLabelId' => $this->commonData()->getAdminLoginLabel(),
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('reservations.list.search', $searchData);

        // ビュー変数
        $this->set([
            'reservations' => $reservations,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'check' => $this->SearchInput->getListCheckInfo(
                $searchForm->getFieldValueOptions(),
                $this->getRequest()->getSession()->read('reservations.list.checked')
            ),
            'searchPaymentExpiredFlg' => $searchPaymentExpiredFlg,
            'searchSmartLockUnlinkedFlg' => $searchSmartLockUnlinkedFlg,
        ]);
    }

    /**
     * Calendar method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function calendar()
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

        // 初期遷移時に会員IDの指定解除
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete($sessionKey . '.parameter.user_id');
        }

        $calendarForm = new CalendarForm();
        if (!$calendarForm->validateUserId($this->getRequest()->getQuery('user_id'), $selectCalendar)) {
            throw new BadRequestException();
        }

        //userの指定がある場合は予約情報のセッションを破棄
        if ($this->getRequest()->getQuery('initial') !== null) {
            $this->getRequest()->getSession()->delete('reservations.add');
        }

        $this->calendarAction($sessionKey, $selectCalendar, $calendarForm);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $reservationForm = new ReservationForm();

        $result = $this->addAction($reservationForm, static::TOKEN_VALIDATION_ADD);
        if ($result) {
            return $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Reservations',
                'action' => 'add-conf',
                '?' => [
                    'key' => $reservationForm->getContinuousParameter('key'),
                ],
            ]);
        }

        $searchPaymentExpiredFlg = null;
        $searchSmartLockUnlinkedFlg = null;

        $this->set([
            'searchPaymentExpiredFlg' => $searchPaymentExpiredFlg,
            'searchSmartLockUnlinkedFlg' => $searchSmartLockUnlinkedFlg,
        ]);
    }

    /**
     * Add conf method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function addConf()
    {
        $continuousForm = new ContinuousForm();

        $result = $this->addConfAction(
            $continuousForm,
            static::TOKEN_VALIDATION_ADD,
            $this->getRequest()->getData('mail_check'),
            null,
            null,
            $this->getRequest()->getAttribute('params')
        );
        if ($result) {
            // 完了メッセージ
            $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                'key' => 'reservationsFinish',
                'element' => 'success',
            ]);

            return $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Reservations',
                'action' => 'add-finish',
                '?' => [
                    'id' => $continuousForm->getReservationIds(),
                ],
            ]);
        }
    }

    /**
     * Add finish method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function addFinish()
    {
        $this->addFinishAction();
    }

    /**
     * View method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function view($id = null)
    {
        $reservationForm = new ReservationForm();

        $this->viewAction($id, $reservationForm);

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
     * Edit method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        $reservationForm = new ReservationForm();

        $result = $this->editAction($id, $reservationForm, static::TOKEN_VALIDATION_EDIT);

        $searchPaymentExpiredFlg = null;
        if ($this->isSearchPaymentExpired()) {
            $searchPaymentExpiredFlg = true;
        }

        $searchSmartLockUnlinkedFlg = null;
        if ($this->isSearchReservationUnlinkedSmartLock()) {
            $searchSmartLockUnlinkedFlg = true;
        }

        if ($result) {
            return $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Reservations',
                'action' => 'edit-conf',
                'id' => $reservationForm->getReservationEntity()->get('id'),
                '?' => [
                    'search_payment_expired' => $searchPaymentExpiredFlg,
                    'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg,
                ],
            ]);
        }

        $this->set([
            'searchPaymentExpiredFlg' => $searchPaymentExpiredFlg,
            'searchSmartLockUnlinkedFlg' => $searchSmartLockUnlinkedFlg,
        ]);
    }

    /**
     * Edit conf method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function editConf($id = null)
    {
        $reservationForm = new ReservationForm();

        $result = $this->editConfAction(
            $id,
            $reservationForm,
            static::TOKEN_VALIDATION_EDIT,
            $this->getRequest()->getData('mail_check'),
            $this->getRequest()->getAttribute('params')
        );

        $searchPaymentExpiredFlg = null;
        if ($this->isSearchPaymentExpired()) {
            $searchPaymentExpiredFlg = true;
        }

        $searchSmartLockUnlinkedFlg = null;
        if ($this->isSearchReservationUnlinkedSmartLock()) {
            $searchSmartLockUnlinkedFlg = true;
        }

        if ($result) {
            // 完了メッセージ
            $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                'key' => 'reservationsFinish',
                'element' => 'success',
            ]);

            return $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Reservations',
                'action' => 'edit-finish',
                'id' => $reservationForm->getReservationEntity()->get('id'),
                '?' => [
                    'search_payment_expired' => $searchPaymentExpiredFlg,
                    'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg,
                ],
            ]);
        }

        $this->set([
            'searchPaymentExpiredFlg' => $searchPaymentExpiredFlg,
            'searchSmartLockUnlinkedFlg' => $searchSmartLockUnlinkedFlg,
        ]);
    }

    /**
     * Edit finish method
     *
     * @param int $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function editFinish($id = null)
    {
        $this->editFinishAction($id);

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
     * Cancel method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function cancel($id = null)
    {
        $cancelForm = new CancelForm();

        $this->cancelAction(
            $id,
            $cancelForm,
            $this->getRequest()->getData('mail_check'),
            $this->getRequest()->getAttribute('params')
        );

        $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
            'key' => 'reservationsFinish',
            'element' => 'success',
        ]);

        $searchPaymentExpiredFlg = null;
        if ($this->isSearchPaymentExpired()) {
            $searchPaymentExpiredFlg = true;
        }

        $searchSmartLockUnlinkedFlg = null;
        if ($this->isSearchReservationUnlinkedSmartLock()) {
            $searchSmartLockUnlinkedFlg = true;
        }

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'view',
            'id' => $id,
            '?' => [
                'search_payment_expired' => $searchPaymentExpiredFlg,
                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg,
            ],
        ]);
    }

    /**
     * Delete method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function delete($id = null)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->fetchTable('ReservationVideoMeetings');

        $this->getRequest()->allowMethod('post');

        $reservation = $reservationsTable->get($id, [
            'finder' => 'delete',
        ]);

        // 削除可否チェック
        /** @var \App\Model\Entity\Event $event */
        $event = $reservation->getEventEntity();
        if (!$labelsTable->isAdminUsableLabel($event->get('label_id'))) {
            throw new NotFoundException();
        }

        $deleteForm = new DeleteForm();
        if (!$deleteForm->execute((array)$this->getRequest()->getData())) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $reservationsTable->deleteOrFail($reservation, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
            'waitingCancellation' => $deleteForm->getData('waiting_cancellation'),
        ]);

        // ビデオ会議連携のエラーメッセージ
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

        $searchPaymentExpiredFlg = null;
        if ($this->isSearchPaymentExpired()) {
            $searchPaymentExpiredFlg = true;
        }

        $searchSmartLockUnlinkedFlg = null;
        if ($this->isSearchReservationUnlinkedSmartLock()) {
            $searchSmartLockUnlinkedFlg = true;
        }

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Reservations',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery') + [
                'search_payment_expired' => $searchPaymentExpiredFlg,
                'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg,
            ],
        ]);
    }

    /**
     * DeleteMany method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function deleteMany()
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->fetchTable('ReservationVideoMeetings');

        /** @var array|null $checked */
        $checked = $this->getRequest()->getSession()->read('reservations.list.checked');

        /** @var array|null $condition */
        $condition = $this->getRequest()->getSession()->read('reservations.list.search');

        if (!is_array($checked) || empty($checked) || !isset($condition)) {
            throw new BadRequestException(Message::ERROR_ONE_OR_MORE_SELECT);
        }

        if (!is_array($condition)) {
            $condition = [];
        }

        $selectInfo = [
            'checked' => $checked,
            'condition' => $condition,
        ];

        $reservations = $reservationsTable->find('checkReservations', $selectInfo);

        // 削除可否チェック
        foreach ($reservations as $reservation) {
            if (!$labelsTable->isAdminUsableLabel($reservation->getEventEntity()->get('label_id'))) {
                throw new NotFoundException();
            }
        }

        $deleteManyForm = new DeleteManyForm();
        $deleteManyForm->setDeleteChecked($selectInfo);

        // ビデオ会議連携の件数制限チェック
        if (!$deleteManyForm->canDeleteVideoMeeting()) {
            throw new BadRequestException(Message::ERROR_VIDEO_METTING_DELETE_MANY);
        }

        $searchPaymentExpiredFlg = null;
        if ($this->isSearchPaymentExpired()) {
            $searchPaymentExpiredFlg = true;
        }

        $searchSmartLockUnlinkedFlg = null;
        if ($this->isSearchReservationUnlinkedSmartLock()) {
            $searchSmartLockUnlinkedFlg = true;
        }

        if ($this->getRequest()->is('post')) {
            $deleteManyInputs = (array)$this->getRequest()->getData();
            if ($deleteManyForm->execute($deleteManyInputs)) {
                if (
                    $reservationsTable->deleteReservations(
                        $checked,
                        $condition,
                        $this->getRequest()->getAttribute('params'),
                        $deleteManyForm->getData('waiting_cancellation'),
                        $deleteManyForm->hasVideoMeeting()
                    )
                ) {
                    // ビデオ会議連携のエラーメッセージ
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

                    return $this->redirect([
                        'prefix' => 'Admin',
                        'controller' => 'Reservations',
                        'action' => 'list',
                        '?' => Configure::read('Setting.searchInput.searchQuery') + [
                            'search_payment_expired' => $searchPaymentExpiredFlg,
                            'search_smart_lock_unlinked' => $searchSmartLockUnlinkedFlg,
                        ],
                    ]);
                } else {
                    throw new BadRequestException(Message::DELETE_MANY_ERROR);
                }
            } else {
                throw new BadRequestException(Message::DELETE_MANY_ERROR);
            }
        }

        // ビュー変数
        $this->set([
            'reservations' => $reservations,
            'checked' => json_encode($selectInfo),
            'deleteManyForm' => $deleteManyForm,
            'searchPaymentExpiredFlg' => $searchPaymentExpiredFlg,
            'searchSmartLockUnlinkedFlg' => $searchSmartLockUnlinkedFlg,
        ]);
    }

    /**
     * Download method チェックしたデータのダウンロード
     *
     * @return \Cake\Http\Response|null|void
     */
    public function downloadChecked()
    {
        set_time_limit(180);

        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        /** @var array|null $checked */
        $checked = $this->getRequest()->getSession()->read('reservations.list.checked');

        /** @var array|null $condition */
        $condition = $this->getRequest()->getSession()->read('reservations.list.search');
        if (!is_array($checked) || empty($checked) || !isset($condition)) {
            throw new BadRequestException(Message::ERROR_ONE_OR_MORE_SELECT);
        }

        if (!is_array($condition)) {
            $condition = [];
        }

        // 管理者に紐づかないカテゴリーで検索された場合は強制的に担当カテゴリで検索する
        $condition = $labelsTable->setSearchLabelID($condition, 'event_label_id');

        $fileName = Configure::readOrFail('Setting.csv.download.reservation.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $callback = $reservationsTable->createCsvCheck($checked, $condition);

        return $this->FileDownload->setStreamDownloadResponse(
            $fileName,
            Configure::readOrFail('Setting.csv.download.reservation.type'),
            $callback
        );
    }

    /**
     * Download method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function download()
    {
        set_time_limit(180);
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        /** @var array|null $searchData */
        $searchData = $this->getRequest()->getSession()->read('reservations.list.search');

        if (!isset($searchData)) {
            throw new BadRequestException();
        }

        // 管理者に紐づかないカテゴリーで検索された場合は強制的に担当カテゴリで検索する
        $searchData = $labelsTable->setSearchLabelID($searchData, 'event_label_id');

        $fileName = Configure::readOrFail('Setting.csv.download.reservation.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $callback = $reservationsTable->createCsv(
            $searchData,
            'searchList',
            ['defaultLabelId' => $this->commonData()->getAdminLoginLabel()]
        );

        return $this->FileDownload->setStreamDownloadResponse(
            $fileName,
            Configure::readOrFail('Setting.csv.download.reservation.type'),
            $callback
        );
    }

    /**
     * Sample method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function sample()
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        $fileName = Configure::readOrFail('Setting.csv.import.reservation.sample.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $filePath = $reservationsTable->createSampleCsv();

        return $this->FileDownload->setDownloadResponse(
            $fileName,
            Configure::readOrFail('Setting.csv.import.reservation.sample.type'),
            $filePath,
            true
        );
    }
}
