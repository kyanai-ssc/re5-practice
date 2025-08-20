<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\PaymentSetting;
use App\Utility\Payment\LinkPaymentInterface;
use App\Utility\Payment\SbPayment;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\View\Helper;

/**
 * PaymentHelper class.
 */
class PaymentHelper extends Helper
{
    use LocatorAwareTrait;

    protected const APP_JS_URL = [
        PaymentSetting::PAYMENT_SERVICE_GMO => 'common/payment_gmo',
        PaymentSetting::PAYMENT_SERVICE_SB => 'common/payment_sb',
    ];

    protected const FIELDS_FOR_JS = [
        PaymentSetting::PAYMENT_SERVICE_GMO => [
            'shop_id',
        ],
        PaymentSetting::PAYMENT_SERVICE_SB => [
            'merchant_id',
            'service_id',
        ],
    ];

    /**
     * API型決済の利用判定
     *
     * @return bool
     */
    public function usesApiPayment(): bool
    {
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        return $paymentMethodsTable->usesApiPayment();
    }

    /**
     * 決済トークン用のJSを取得
     *
     * @return array
     */
    public function getTokenJsUrl(): array
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();

        return [
            $paymentSetting->getPaymentModule()->getTokenJsUrl(),
            static::APP_JS_URL[$paymentSetting->get('payment_service')],
        ];
    }

    /**
     * JSで利用する設定を取得
     *
     * @return array
     */
    public function getSettingForJs(): array
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();

        $setting = [];
        foreach (static::FIELDS_FOR_JS[$paymentSetting->get('payment_service')] as $field) {
            $setting[$field] = $paymentSetting->get($field);
        }

        return $setting;
    }

    /**
     * 決済トークンのエラーコードを取得
     *
     * @return array
     */
    public function getTokenErrorCodes(): array
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        return Configure::readOrFail(
            'Setting.payment.' . $paymentSettingsTable->getDataOrFail()->get('payment_service') . '.tokenError'
        );
    }

    /**
     * リンク型決済のURLを取得
     *
     * @return string
     */
    public function getLinkPaymentUrl(): string
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $payment = $paymentSettingsTable->getDataOrFail()->getPaymentModule();
        if (!($payment instanceof LinkPaymentInterface)) {
            throw new CakeException();
        }

        return $payment->getLinkPaymentUrl();
    }

    /**
     * 本人確認情報のエラーがあればエラーメッセージを返す
     *
     * @param \App\Form\Common\Reservations\ContinuousForm $continuousForm 連続予約フォーム
     * @return string|null
     */
    public function getKycError($continuousForm): ?string
    {
        $errors = $continuousForm->getErrors();

        if (isset($errors['kyc'])) {
            return $errors['kyc'];
        }

        return null;
    }

    /**
     * カード利用者決済情報トークン用のJSを取得
     *
     * @return string
     */
    public function getTds2infotokenJsUrl(): string
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $payment = $paymentSettingsTable->getDataOrFail()->getPaymentModule();
        if (!($payment instanceof SbPayment)) {
            throw new CakeException();
        }

        return $payment->getTds2infotokenJsUrl();
    }
}
