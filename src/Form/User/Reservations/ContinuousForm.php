<?php
declare(strict_types=1);

namespace App\Form\User\Reservations;

use App\Form\Common\Reservations\ContinuousForm as CommonContinuousForm;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;

/**
 * 連続予約フォーム
 */
class ContinuousForm extends CommonContinuousForm
{
    use ContinuousTrait;
    use TermsTrait;

    /**
     * @var bool
     */
    protected $adminFlg = false;

    /**
     * @var array
     */
    protected $optinData = null;

    /**
     * @var mixed|null
     */
    protected $paymentForm = null;

    /**
     * @var bool|null
     */
    protected $requiresPayments = null;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->paymentForm = new PaymentForm();
    }

    /**
     * @inheritDoc
     */
    public function validate(array $data, ?string $validator = null): bool
    {
        $result = parent::validate($data);
        if (!$this->validateReservations()) {
            $result = false;
        }

        if ($this->requiresPayments()) {
            $this->paymentForm->setPaymentTokenNumber($this->getPaymentTokenNumber());
            if (!$this->paymentForm->execute($data)) {
                $result = false;
                $this->setErrors(Hash::merge($this->getErrors(), $this->paymentForm->getErrors()));
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = parent::buildFieldValueOptions();
        $fieldValueOptions += $this->paymentForm->getFieldValueOptions();

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function canContinuous()
    {
        $result = parent::canContinuous();

        return $result && $this->commonData()->existsUserLoginData();
    }

    /**
     * @inheritDoc
     */
    protected function createReservationForm()
    {
        $reservationForm = new ReservationForm();
        $reservationForm->setOptinData($this->optinData);

        return $reservationForm;
    }

    /**
     * オプトインデータを設定
     *
     * @param array $optinData オプトインデータ
     * @return void
     */
    public function setOptinData(array $optinData)
    {
        $this->optinData = $optinData;
    }

    /**
     * 決済の要否を判定
     *
     * @return bool
     */
    public function requiresPayments()
    {
        if (!isset($this->requiresPayments)) {
            /** @var \App\Model\Table\ReservationsTable $reservationsTable */
            $reservationsTable = $this->getTableLocator()->get('Reservations');

            $this->requiresPayments = $reservationsTable->requiresPayments($this->getReservationEntities());
        }

        return $this->requiresPayments;
    }

    /**
     * 決済トークンの数を取得
     *
     * @return int
     */
    public function getPaymentTokenNumber()
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $number = 0;
        foreach ($this->getReservationEntities() as $reservation) {
            if ($reservationsTable->requiresPayments([$reservation])) {
                $number += 1;
            }
        }

        return $number;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentTokens(): ?array
    {
        if (!$this->requiresPayments() || $this->isLinkPayment()) {
            return null;
        }

        return $this->paymentForm->getData('payment_tokens');
    }

    /**
     * リンク型決済の判定
     *
     * @return bool
     */
    public function isLinkPayment()
    {
        return $this->requiresPayments() && $this->paymentForm->isLinkPayment();
    }

    /**
     * 3Dセキュアの判定
     *
     * @return bool
     */
    public function isThreeDSecure()
    {
        $reservationPayment = $this->getFirstReservationPayment();

        return isset($reservationPayment) && $reservationPayment->has('redirect_url');
    }

    /**
     * 3DセキュアのリダイレクトURLを取得
     *
     * @return string
     */
    public function getThreeDSecureUrl()
    {
        $reservationPayment = $this->getFirstReservationPayment();
        if (!isset($reservationPayment)) {
            throw new CakeException();
        }

        return $reservationPayment->get('redirect_url');
    }

    /**
     * 3Dセキュアの認証IDを取得
     *
     * SBペイメント以外はnullが返却される
     *
     * @return string|null
     */
    public function getThreeDSecureAuthenticationId(): ?string
    {
        $reservationPayment = $this->getFirstReservationPayment();
        if (!isset($reservationPayment)) {
            throw new CakeException();
        }

        return $reservationPayment->get('authentication_id');
    }

    /**
     * 先頭の予約決済を取得
     *
     * @return \App\Model\Entity\ReservationPayment|null
     */
    protected function getFirstReservationPayment()
    {
        $reservationPayments = $this->getFirstReservation()->get('reservation_payments');
        if (!is_array($reservationPayments)) {
            return null;
        }

        $reservationPayment = reset($reservationPayments);
        if ($reservationPayment === false) {
            return null;
        }

        return $reservationPayment;
    }

    /**
     * 本人確認情報の値を取得
     *
     * @param array $post POSTデータ
     * @return array|null
     */
    public function getKycValues($post)
    {
        if (empty($post)) {
            return null;
        }

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getData();
        if (
            $systemSettingsTable->getData()->usePayment()
            && isset($paymentSetting)
            && $paymentSetting->requiresKycGmo()
        ) {
            return [
                'emailAddress' => $post['payment_email_address'] ?? null,
                'phoneType' => $post['payment_phone_type'] ?? null,
                'phoneNumber' => $post['payment_phone_number'] ?? null,
            ];
        }

        return null;
    }
}
