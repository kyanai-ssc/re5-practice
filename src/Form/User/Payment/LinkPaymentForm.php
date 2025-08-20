<?php
declare(strict_types=1);

namespace App\Form\User\Payment;

use App\Form\AppForm;
use App\Model\Entity\PaymentMethod;
use App\Model\Entity\PaymentSetting;
use App\Model\Entity\Reservation;
use App\Utility\DateTimeUtility;
use App\Utility\Payment\LinkPaymentInterface;
use Cake\Core\Exception\CakeException;
use Cake\Routing\Router;

/**
 * LinkPaymentForm
 */
class LinkPaymentForm extends AppForm
{
    protected ?Reservation $reservation;
    protected ?array $paymentResult;
    protected bool $forDisplayResult = true;

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
        $payment = $paymentSetting->getPaymentModule();
        if (!($payment instanceof LinkPaymentInterface)) {
            throw new CakeException();
        }

        $reservationPayment = $this->getReservation()->getReservationPayment();
        $paymentResult = $payment->getLinkPaymentResult($data, [
            'forDisplayResult' => $this->forDisplayResult,
        ]);
        if (
            !isset($paymentResult)
            || (string)$paymentResult['order_id'] !== (string)$reservationPayment->get('payment_order_id')
            || (string)$paymentSetting->get('payment_service') !== (string)$reservationPayment->get('payment_service')
        ) {
            return false;
        }

        $this->paymentResult = $paymentResult;

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
     * 画面表示用の結果かどうかをセット
     *
     * @param bool $forDisplayResult 画面表示用の結果かどうか
     * @return void
     */
    public function setForDisplayResult(bool $forDisplayResult): void
    {
        $this->forDisplayResult = $forDisplayResult;
    }

    /**
     * リンク型決済のパラメータを生成
     *
     * @return array
     */
    public function createLinkParameter(): array
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();
        $payment = $paymentSetting->getPaymentModule();

        $reservation = $this->getReservation();
        $reservationPayment = $reservation->getReservationPayment();
        if (
            !($payment instanceof LinkPaymentInterface)
            || (string)$paymentSetting->get('payment_service') !== (string)$reservationPayment->get('payment_service')
        ) {
            throw new CakeException();
        }

        $created = DateTimeUtility::convertToDateTimeObject($reservationPayment->get('created'));
        if (!isset($created)) {
            throw new CakeException();
        }
        $paymentMethod = $paymentMethodsTable->getPaymentMethodType($reservation->get('payment_method_id'));
        $event = $reservation->getEventEntity();
        if (!isset($event)) {
            throw new CakeException();
        }

        return $payment->createLinkParameter([
            'pay_method' => PaymentMethod::LINKAGE_PAYMENT_METHOD[PaymentSetting::PAYMENT_SERVICE_SB][$paymentMethod],
            'cust_code' => $this->createCustCode((int)$reservation->get('user_id')),
            'order_id' => $reservationPayment->get('payment_order_id'),
            'item_id' => $event->get('id'),
            'item_name' => $event->get('name'),
            'amount' => $reservationPayment->get('charge'),
            'success_url' => Router::url([
                'prefix' => 'User',
                'controller' => 'Payment',
                'action' => 'finish',
                'id' => $reservation->get('id'),
            ], true),
            'cancel_url' => Router::url([
                'prefix' => 'User',
                'controller' => 'Payment',
                'action' => 'cancel',
                'id' => $reservation->get('id'),
            ], true),
            'error_url' => Router::url([
                'prefix' => 'User',
                'controller' => 'Payment',
                'action' => 'error',
                'id' => $reservation->get('id'),
            ], true),
            'pagecon_url' => Router::url([
                'prefix' => 'User',
                'controller' => 'Payment',
                'action' => 'result',
                'id' => $reservation->get('id'),
            ], true),
            'free1' => $reservation->get('id'),
            'request_date' => $created->format('YmdHis'),
        ]);
    }

    /**
     * 決済結果を取得
     *
     * @return array
     */
    public function getPaymentResult(): array
    {
        if (!isset($this->paymentResult)) {
            throw new CakeException();
        }

        return $this->paymentResult;
    }

    /**
     * 顧客IDを生成
     *
     * @param int $userId 会員ID
     * @return string
     */
    protected function createCustCode(int $userId): string
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        return $paymentSettingsTable->getDataOrFail()->get('cust_code_prefix') . $userId;
    }
}
