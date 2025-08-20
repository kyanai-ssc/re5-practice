<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\ApiAppController;
use App\Form\Api\Reservations\EditForm;
use App\Model\Entity\ReservationStatus;
use Cake\Core\Exception\CakeException;
use Exception;

/**
 * Reservations Controller
 */
class ReservationsController extends ApiAppController
{
    public const AUTHORITY_CHECK_CONTROLLER_NAME = 'ReceptionStatuses';
    public const AUTHORITY_CHECK_ACTION_NAME = 'list';

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->setTranslate(true);
    }

    /**
     * Edit method
     *
     * @param string|null $qrCodeData QRコードのデータ
     * @return \Cake\Http\Response|null|void
     */
    public function edit($qrCodeData = null)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        $editForm = new EditForm();
        try {
            // パラメータチェック
            if (!$editForm->execute((array)$this->getRequest()->getData())) {
                $this->setApiResult(
                    static::API_RESULT_STATUS_BAD_REQUEST,
                    static::API_RESULT_MESSAGE_REQUIRED,
                    $this->getRequest()->getData('access_token')
                );

                return;
            }

            // トークンチェック
            $accessToken = $editForm->getData('access_token');
            if ($this->checkAccesToken($accessToken, $accessToken)) {
                // QRコードに一致する予約情報を取得
                $reservation = $reservationsTable->getByQrCode($qrCodeData);
                if (empty($reservation)) {
                    // 存在しない場合はエラーを返す
                    $this->setApiResult(
                        static::API_RESULT_STATUS_NOT_FOUND,
                        static::API_RESULT_MESSAGE_DATA_NOT_EXIST,
                        $editForm->getData('access_token')
                    );

                    return;
                }

                $event = $reservation->getEventEntity();
                if (!empty($event) && !$labelsTable->isAdminUsableLabel($event->get('label_id'))) {
                    // 担当カテゴリーで無い場合はエラーを返す
                    $this->setApiResult(
                        static::API_RESULT_STATUS_BAD_REQUEST,
                        static::API_RESULT_MESSAGE_NOT_ACCEPTED,
                        $editForm->getData('access_token')
                    );

                    return;
                }

                $reservationStatus = $reservation->get('reservation_status');
                if (
                    (string)$reservationStatus->get('status_type') === ((string)ReservationStatus::STATUS_TYPE_ABSENCE)
                ) {
                    // ステータスが「欠席」であるならエラーを返す
                    $this->setApiResult(
                        static::API_RESULT_STATUS_BAD_REQUEST,
                        static::API_RESULT_MESSAGE_ABSENCE,
                        $editForm->getData('access_token')
                    );

                    return;
                }

                if (!$reservationsTable->checkReservedDate($reservation)) {
                    // 予約利用日当日ではない場合はエラーを返す
                    $this->setApiResult(
                        static::API_RESULT_STATUS_BAD_REQUEST,
                        static::API_RESULT_MESSAGE_NOT_RESERVED_DATE,
                        $editForm->getData('access_token')
                    );

                    return;
                }

                // フォーム情報を取得する
                $result = $reservationsTable->getAppDisplayFormGroups($reservation);

                // ステータスの更新処理
                $updateResult = $reservationsTable->updateReceptionStatus($reservation, [
                    'saveOperation' => [
                        'controller' => 'ReceptionStatuses',
                        'action' => 'edit',
                        'id' => $reservation->get('id'),
                    ],
                ]);
                if ($updateResult['save'] === false) {
                    throw new CakeException();
                }

                // 予約情報とステータス更新結果を返す
                $result['receptions'] = [
                    'status' => $updateResult['status_type'],
                    'message' => $updateResult['message'],
                ];
                $this->setApiResult(
                    static::API_RESULT_STATUS_OK,
                    static::API_RESULT_MESSAGE_SUCCESS,
                    $editForm->getData('access_token'),
                    $result
                );

                return;
            }
        } catch (Exception $e) {
            $this->setExceptionApiResult($e, $this->getRequest()->getData('access_token'));
        }
    }
}
