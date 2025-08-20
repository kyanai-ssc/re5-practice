<?php
declare(strict_types=1);

namespace App\Utility\Payment;

use App\Model\Entity\PaymentSetting;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;

class PaymentFactory
{
    protected const ERROR_LOG_SCOPE = 'payment';
    protected const ROLLBACK_CANCEL_RETRY = 3;
    protected const SB_LIMIT_SECOND = 300;

    /**
     * 決済モジュールを生成
     *
     * @param \App\Model\Entity\PaymentSetting $paymentSetting 決済設定
     * @return \App\Utility\Payment\PaymentInterface
     */
    public static function createPaymentModule(PaymentSetting $paymentSetting): PaymentInterface
    {
        if ($paymentSetting->isPaymentServiceGmo()) {
            return static::createGmoPaymentModule($paymentSetting->get('environment'), [
                'shopId' => $paymentSetting->get('shop_id'),
                'shopPass' => $paymentSetting->decryptApiInfo($paymentSetting->get('shop_password')),
            ]);
        } elseif ($paymentSetting->isPaymentServiceSb()) {
            return static::createSbPaymentModule($paymentSetting->get('environment'), [
                'merchantId' => $paymentSetting->get('merchant_id'),
                'serviceId' => $paymentSetting->get('service_id'),
                'hashKey' => $paymentSetting->decryptApiInfo($paymentSetting->get('hash_key')),
                'basicAuthId' => $paymentSetting->get('basic_auth_id'),
                'basicAuthPassword' => $paymentSetting->decryptApiInfo($paymentSetting->get('basic_auth_password')),
                'encryptKey' => $paymentSetting->decryptApiInfo($paymentSetting->get('encrypt_key')),
                'encryptIv' => $paymentSetting->decryptApiInfo($paymentSetting->get('encrypt_iv')),
            ]);
        } else {
            throw new CakeException();
        }
    }

    /**
     * GMOのモジュールを生成
     *
     * @param int $environment 環境
     * @param array $config 設定
     * @return \App\Utility\Payment\GmoPayment
     */
    public static function createGmoPaymentModule(int $environment, array $config): GmoPayment
    {
        $configKey = 'Setting.payment.' . PaymentSetting::PAYMENT_SERVICE_GMO;

        return new GmoPayment($config + [
            'host' => Configure::readOrFail($configKey . '.host.' . $environment),
            'tokenJsUrl' => Configure::readOrFail($configKey . '.tokenJsUrl.' . $environment),
            'errorLog' => static::ERROR_LOG_SCOPE,
            'cancelRetry' => static::ROLLBACK_CANCEL_RETRY,
        ] + Configure::readOrFail($configKey . '.config'));
    }

    /**
     * SBのモジュールを生成
     *
     * @param int $environment 環境
     * @param array $config 設定
     * @return \App\Utility\Payment\SbPayment
     */
    public static function createSbPaymentModule(int $environment, array $config): SbPayment
    {
        $configKey = 'Setting.payment.' . PaymentSetting::PAYMENT_SERVICE_SB;

        return new SbPayment($config + [
            'host' => Configure::readOrFail($configKey . '.host.' . $environment),
            'tokenJsUrl' => Configure::readOrFail($configKey . '.tokenJsUrl.' . $environment),
            'tds2infotokenJsUrl' => Configure::readOrFail($configKey . '.tds2infotokenJsUrl.' . $environment),
            'errorLog' => static::ERROR_LOG_SCOPE,
            'cancelRetry' => static::ROLLBACK_CANCEL_RETRY,
            'linkPaymentUrl' => Configure::readOrFail($configKey . '.linkPaymentUrl.' . $environment),
            'limitSecond' => static::SB_LIMIT_SECOND,
        ] + Configure::readOrFail($configKey . '.config'));
    }
}
