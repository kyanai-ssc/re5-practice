<?php
declare(strict_types=1);

namespace App\Form\User\Payment;

use App\Form\AppForm;
use App\Model\Entity\Event;
use App\Model\Entity\PaymentSetting;
use App\Model\Entity\Reservation;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;

/**
 * SbPaymentThreeDSecureForm
 */
class SbPaymentThreeDSecureForm extends AppForm
{
    /**
     * 予約エンティティ
     *
     * @var \App\Model\Entity\Reservation|null
     */
    protected ?Reservation $reservation;

    /**
     * 決済設定エンティティ
     *
     * @var \App\Model\Entity\PaymentSetting|null
     */
    protected ?PaymentSetting $paymentSetting;

    /**
     * 決済結果
     *
     * @var array|null
     */
    protected ?array $paymentResult;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = parent::_buildSchema($schema);

        return $schema->addField('tds_authentication_id', 'string');
    }

    /**
     * @inheritDoc
     */
    protected function _execute(array $data): bool
    {
        /** @var \App\Utility\Payment\SbPayment $sbPayment */
        $sbPayment = $this->getPaymentSetting()->getPaymentModule();
        $reservationPayment = $this->getReservation()->getReservationPayment();
        $sbPayment->setOrderId($reservationPayment->get('payment_order_id'));
        $this->paymentResult = $sbPayment->execute3DSecurePayment($this->createPaymentParameter());

        return true;
    }

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
            !$paymentSetting->isPaymentServiceSb()
            || (string)$paymentSetting->get('payment_service') !== (string)$reservationPayment->get('payment_service')
            || !$reservationPayment->isPaymentProcessing()
        ) {
            return false;
        }

        return true;
    }

    /**
     * 3Dセキュア決済のパラメータを生成
     *
     * @return array
     */
    protected function createPaymentParameter(): array
    {
        $reservation = $this->getReservation();
        $event = $reservation->getEventEntity();
        if (!($event instanceof Event)) {
            throw new CakeException();
        }
        $reservationPayment = $reservation->getReservationPayment();

        return [
            'cust_code' => $this->createCustCode((int)$reservation->get('user_id')),
            'item_id' => $event->get('id'),
            'item_name' => $event->get('name'),
            'amount' => $reservationPayment->get('charge'),
            'free1' => $reservation->get('id'),
            'tds_authentication_id' => $this->getData('tds_authentication_id'),
        ];
    }

    /**
     * 顧客IDを生成
     *
     * @param int $userId リザエン会員ID
     * @return string
     */
    protected function createCustCode(int $userId): string
    {
        return $this->getPaymentSetting()->get('cust_code_prefix') . $userId;
    }

    /**
     * 決済をキャンセル
     *
     * @return void
     */
    public function cancelPayment(): void
    {
        $this->getPaymentSetting()->getPaymentModule()->cancelLastPayment();
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
     * 決済設定を取得
     *
     * @return \App\Model\Entity\PaymentSetting
     */
    public function getPaymentSetting(): PaymentSetting
    {
        if (!isset($this->paymentSetting)) {
            throw new CakeException();
        }

        return $this->paymentSetting;
    }

    /**
     * 決済設定を設定
     *
     * @param \App\Model\Entity\PaymentSetting $paymentSetting 決済設定
     * @return void
     */
    public function setPaymentSetting(PaymentSetting $paymentSetting): void
    {
        $this->paymentSetting = $paymentSetting;
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
