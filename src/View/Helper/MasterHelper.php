<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\I18n\FrozenTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\View\Helper;

/**
 * MasterHelper class.
 */
class MasterHelper extends Helper
{
    use LocatorAwareTrait;

    /**
     * 都道府県の選択肢を取得
     *
     * @return array 選択肢
     */
    public function getPrefectureValues()
    {
        /** @var \App\Model\Table\PrefecturesTable $prefecturesTable */
        $prefecturesTable = $this->getTableLocator()->get('Prefectures');

        return $prefecturesTable->getValueOptions();
    }

    /**
     * 都道府県の名称を取得
     *
     * @param int $prefectureId 都道府県ID
     * @return string 名称
     */
    public function getPrefectureName(int $prefectureId)
    {
        /** @var \App\Model\Table\PrefecturesTable $prefecturesTable */
        $prefecturesTable = $this->getTableLocator()->get('Prefectures');

        return $prefecturesTable->getPrefectureName($prefectureId);
    }

    /**
     * 予約ステータスの選択肢を取得
     *
     * @return array 選択肢
     */
    public function getReservationStatusValues()
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        return $reservationStatusesTable->getValueOptions();
    }

    /**
     * 予約ステータスの名称を取得
     *
     * @param int $reservationStatusId 予約ステータスID
     * @return string 名称
     */
    public function getReservationStatusName(int $reservationStatusId)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        return $reservationStatusesTable->getReservationStatusName($reservationStatusId);
    }

    /**
     * 予約ステータスのタイプを取得
     *
     * @param int $reservationStatusId 予約ステータスID
     * @return int タイプ
     */
    public function getReservationStatusType(int $reservationStatusId)
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');

        return $reservationStatusesTable->getReservationStatusType($reservationStatusId);
    }

    /**
     * 受付ステータスの名称を取得
     *
     * @param int $receptionStatusId 受付ステータスID
     * @return mixed 名称
     */
    public function getReceptionStatusName($receptionStatusId = null)
    {
        /** @var \App\Model\Table\ReceptionStatusesTable $receptionStatusesTable */
        $receptionStatusesTable = $this->getTableLocator()->get('ReceptionStatuses');

        return $receptionStatusesTable->getReceptionStatusName($receptionStatusId);
    }

    /**
     * 決済方法の選択肢を取得
     *
     * @return array 選択肢
     */
    public function getPaymentMethodValues()
    {
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        return $paymentMethodsTable->getValueOptions();
    }

    /**
     * 決済方法の名称を取得
     *
     * @param int $paymentMethodId 決済方法ID
     * @return string 名称
     */
    public function getPaymentMethodName(int $paymentMethodId)
    {
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        return $paymentMethodsTable->getPaymentMethodName($paymentMethodId);
    }

    /**
     * 決済方法のタイプを取得
     *
     * @param int $paymentMethodId 決済方法ID
     * @return int タイプ
     */
    public function getPaymentMethodType(int $paymentMethodId)
    {
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        return $paymentMethodsTable->getPaymentMethodType($paymentMethodId);
    }

    /**
     * 決済ステータスの選択肢を取得
     *
     * @return array 選択肢
     */
    public function getPaymentStatusValues()
    {
        /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
        $paymentStatusesTable = $this->getTableLocator()->get('PaymentStatuses');

        return $paymentStatusesTable->getValueOptions();
    }

    /**
     * 決済ステータスの名称を取得
     *
     * @param int $paymentStatusId 決済ステータスID
     * @return string 名称
     */
    public function getPaymentStatusName(int $paymentStatusId)
    {
        /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
        $paymentStatusesTable = $this->getTableLocator()->get('PaymentStatuses');

        return $paymentStatusesTable->getPaymentStatusName($paymentStatusId);
    }

    /**
     * 決済連携状況を取得
     *
     * @param int $status ステータス
     * @param \Cake\I18n\FrozenTime|null $paymentLimit 有効期限
     * @return int 決済連携状況
     */
    public function getReservationPaymentStatus(int $status, ?FrozenTime $paymentLimit)
    {
        /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
        $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');

        return $reservationPaymentsTable->getReservationPaymentStatus($status, $paymentLimit);
    }
}
