<?php
declare(strict_types=1);

namespace App\Form\User\Payment;

use App\Form\AppForm;
use App\Model\Entity\Reservation;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;

/**
 * GmoThreeDSecureForm
 */
class GmoThreeDSecureForm extends AppForm
{
    protected ?Reservation $reservation;
    protected ?array $paymentResult;

    /**
     * @inheritDoc
     */
    public function validate(array $data, ?string $validator = null): bool
    {
        $result = parent::validate($data, $validator);
        if (!$result) {
            return false;
        }

        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();
        $reservationPayment = $this->getReservation()->getReservationPayment();
        if (
            !$paymentSetting->isPaymentServiceGmo()
            || (string)$paymentSetting->get('payment_service') !== (string)$reservationPayment->get('payment_service')
            || $reservationPayment->get('access_id') !== Hash::get($data, 'AccessID')
            || !$reservationPayment->isPaymentProcessing()
        ) {
            return false;
        }

        $this->paymentResult = $paymentSetting->getPaymentModule()->getThreeDSecurePaymentResult([
            'AccessID' => $reservationPayment->get('access_id'),
            'AccessPass' => $reservationPayment->decryptApiInfo($reservationPayment->get('access_pass')),
        ]);

        return true;
    }

    /**
     * 予約を取得
     *
     * @return \App\Model\Entity\Reservation
     */
    public function getReservation(): Reservation
    {
        if (!isset($this->reservation)) {
            throw new CakeException();
        }

        return $this->reservation;
    }

    /**
     * 予約を設定
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return void
     */
    public function setReservation(Reservation $reservation): void
    {
        $this->reservation = $reservation;
    }

    /**
     * 決済結果を取得
     *
     * @return array|null
     */
    public function getPaymentResult(): ?array
    {
        if (!isset($this->paymentResult)) {
            return null;
        }

        return $this->paymentResult;
    }
}
