<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\ReceptionStatuses\SearchForm;
use App\Form\Admin\Reservations\ReservationForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Hash;

/**
 * ReceptionStatuses Controller
 */
class ReceptionStatusesController extends AdminAppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'ReceptionStatuses',
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
            $this->getRequest()->getSession()->delete('receptionStatuses.list.search');
        }

        $searchForm = new SearchForm();
        $reservationForms = [];
        $searchExec = $this->SearchInput->shouldKeepCondition();
        if ($searchExec) {
            // 入力値取得
            $searchInputs = $this->SearchInput->getCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('receptionStatuses.list.search')
            );
            $this->setRequestData($searchInputs);

            // 入力チェック
            if ($searchForm->execute($searchInputs)) {
                $searchData = $searchForm->getData();
            } else {
                $searchData = $this->SearchInput->getFallbackCondition(
                    $searchForm->getDefaultFieldValues(),
                    $this->getRequest()->getSession()->read('receptionStatuses.list.search')
                );
            }
            $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

            // データ取得
            $reservations = $this->Pagination->paginate($this->fetchTable('Reservations'), [
                'finder' => [
                    'receptionSearchList' => [
                        'inputs' => $searchData,
                        'defaultLabelId' => $this->commonData()->getAdminLoginLabel(),
                    ],
                ],
                'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
            ]);
            foreach ($reservations as $key => $reservation) {
                $reservationForm = new ReservationForm();
                $reservationForm->setReservationEntity($reservation);
                $reservationForm->setReservationParameter([]);
                if (!$reservationForm->validateReservationParameter()) {
                    throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
                }
                $reservationForms[$key] = $reservationForm;
            }
            // セッション保持
            $this->getRequest()->getSession()->write('receptionStatuses.list.search', $searchData);
        }

        // ビュー変数
        $this->set([
            'reservationForms' => $reservationForms,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'searchExec' => $searchExec,
        ]);
    }

    /**
     * 更新処理
     *
     * @param string|int $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        $reservation = $reservationsTable->get($id, [
            'finder' => 'updateReceptionStatus',
        ]);

        // 削除可否チェック
        /** @var \App\Model\Entity\Event $event */
        $event = $reservation->getEventEntity();
        if (!$labelsTable->isAdminUsableLabel($event->get('label_id'))) {
            throw new NotFoundException();
        }

        // ステータスを更新
        $result = $reservationsTable->updateReceptionStatus($reservation, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ]);
        if (Hash::get($result, 'save', false) === false) {
            throw new CakeException();
        }

        $message = (string)Hash::get($result, 'message');
        if (!empty($message)) {
            $this->Flash->set($message, [
                'key' => 'receptionStatusesFinish',
                'element' => 'success',
            ]);
        }

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'ReceptionStatuses',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }
}
