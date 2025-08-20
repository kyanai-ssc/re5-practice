<?php
declare(strict_types=1);

namespace App\Utility\Payment;

use App\Utility\ArrayUtility;
use App\Utility\Payment\Exception\PaymentFailedException;
use Cake\Core\Exception\CakeException;
use Cake\Http\Client;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
use Cake\Utility\Security;
use Cake\Utility\Xml;
use DOMDocument;
use Throwable;

/**
 * SBペイメント
 */
class SbPayment extends AbstractPayment implements LinkPaymentInterface
{
    /**
     * 支払い方法：クレジットカード
     */
    public const PAY_METHOD_CREDIT = 'credit';

    /**
     * 支払い方法：PayPay
     */
    public const PAY_METHOD_PAYPAY = 'paypay';

    /**
     * 支払い方法：ApplePay
     */
    public const PAY_METHOD_APPLE_PAY = 'applepay';

    /**
     * 支払い方法：auPAY
     */
    public const PAY_METHOD_AU_PAY = 'aupay';

    /**
     * 決済結果参照要求を利用可能な支払い方法
     */
    protected const GET_API_AVAILABLE = [
        self::PAY_METHOD_CREDIT,
        self::PAY_METHOD_PAYPAY,
        self::PAY_METHOD_APPLE_PAY,
    ];

    /**
     * 機能ID：クレジットカード決済：決済要求（ワンタイムトークン利用）
     */
    protected const SPS_API_ID_CARD_ONETIME_TOKEN = 'ST01-00131-101';

    /**
     * 機能ID：クレジットカード決済：確定要求
     */
    protected const SPS_API_ID_CARD_PAYMENT = 'ST02-00101-101';

    /**
     * 機能ID：クレジットカード決済：取消返金要求
     */
    protected const SPS_API_ID_CARD_CANCEL = 'ST02-00303-101';

    /**
     * 機能ID：クレジットカード決済：決済結果参照要求
     */
    protected const SPS_API_ID_CARD_GET_PAYMENT = 'MG01-00101-101';

    /**
     * 機能ID：クレジットカード決済：3DS認証要求
     */
    protected const SPS_API_ID_CARD_3D_SECURE_AUTHENTICATION = 'TA02-00101-101';

    /**
     * 機能ID：クレジットカード決済：決済要求（3DS認証情報付加・認証結果ID指定）
     */
    protected const SPS_API_ID_CARD_3D_SECURE_REQUIRE_PAYMENT = 'ST01-00118-101';

    /**
     * 機能ID：PayPay：決済結果参照要求
     */
    protected const SPS_API_ID_PAYPAY_GET_PAYMENT = 'MG01-00101-311';

    /**
     * 機能ID：PayPay：取消返金要求
     */
    protected const SPS_API_ID_PAYPAY_CANCEL = 'ST02-00303-311';

    /**
     * 機能ID：ApplePay：決済結果参照要求
     */
    protected const SPS_API_ID_APPLE_PAY_GET_PAYMENT = 'MG01-00101-601';

    /**
     * 機能ID：ApplePay：取消要求
     */
    protected const SPS_API_ID_APPLE_PAY_CANCEL = 'ST02-00301-601';

    /**
     * 機能ID：ApplePay：返金要求
     */
    protected const SPS_API_ID_APPLE_PAY_REFUND = 'ST02-00401-601';

    /**
     * 機能ID：auPAY：取消返金要求
     */
    protected const SPS_API_ID_AU_PAY_CANCEL = 'ST02-00303-406';

    /**
     * フラグ：OFF
     */
    protected const FLG_OFF = 0;

    /**
     * フラグ：ON
     */
    protected const FLG_ON = 1;

    /**
     * 処理結果ステータス：OK
     */
    protected const API_RESULT_OK = 'OK';

    /**
     * 処理結果ステータス：NG
     */
    protected const API_RESULT_NG = 'NG';

    /**
     * 購入タイプ：都度課金
     */
    protected const PAY_TYPE_EACH = 0;

    /**
     * サービスタイプ：売上
     */
    protected const SERVICE_TYPE_SALES = 0;

    /**
     * 暗号化方式
     */
    protected const CRYPT_METHOD = 'des-ede3-cbc';

    /**
     * マルチバイトエンコーディング
     */
    protected const MULTIBYTE_ENCODING = 'SJIS';

    /**
     * 結果通知のチェックサム順序
     */
    protected const LINK_RESULT_HASH_ORDER = [
        'pay_method',
        'merchant_id',
        'service_id',
        'cust_code',
        'order_id',
        'item_id',
        'item_name',
        'amount',
        'sps_cust_no',
        'sps_payment_no',
        'pay_item_id',
        'tax',
        'pay_type',
        'auto_charge_type',
        'service_type',
        'div_settele',
        'last_charge_month',
        'camp_type',
        'tracking_id',
        'terminal_type',
        'free1',
        'free2',
        'free3',
        'dtl_rowno',
        'dtl_item_id',
        'dtl_item_name',
        'dtl_item_count',
        'dtl_tax',
        'dtl_amount',
        'dtl_free1',
        'dtl_free2',
        'dtl_free3',
        'request_date',
        'res_pay_method',
        'res_result',
        'res_tracking_id',
        'res_sps_cust_no',
        'res_sps_payment_no',
        'res_payinfo_key',
        'res_payment_date',
        'res_err_code',
        'res_date',
        'limit_second',
    ];

    /**
     * 決済ステータス：クレジットカード決済：与信済
     */
    protected const PAYMENT_STATUS_CARD_GRANTED = 1;

    /**
     * 決済ステータス：クレジットカード決済：売上済
     */
    protected const PAYMENT_STATUS_CARD_SOLD = 2;

    /**
     * 決済ステータス：PayPay：与信済
     */
    protected const PAYMENT_STATUS_PAYPAY_GRANTED = 1;

    /**
     * 決済ステータス：PayPay：売上処理中
     */
    protected const PAYMENT_STATUS_PAYPAY_SALES_PROCESSING = 2;

    /**
     * 決済ステータス：PayPay：入金済
     */
    protected const PAYMENT_STATUS_PAYPAY_SOLD = 3;

    /**
     * 決済ステータス：ApplePay：与信済
     */
    protected const PAYMENT_STATUS_APPLE_PAY_GRANTED = 1;

    /**
     * 決済ステータス：ApplePay：売上済
     */
    protected const PAYMENT_STATUS_APPLE_PAY_SOLD = 2;

    /**
     * @var array
     */
    protected $_defaultConfig = [
        'scheme' => 'https',
        'sslVerify' => true,
        'cancelRetry' => 0,
    ];

    protected ?array $lastPayment;

    /**
     * @inheritDoc
     */
    public function executeTokenPayment(array $options): ?array
    {
        if (Hash::get($options, 'isUse3DSecure', false) === true) {
            // 3Dセキュアあり
            $authenticationResult = $this->sendCard3DSecureAuthenticationApi($options);
            if (!isset($authenticationResult)) {
                return null;
            }

            return [
                '@id' => Hash::get($authenticationResult, '@id'),
                'res_sps_transaction_id' => Hash::get($authenticationResult, 'res_sps_transaction_id'),
                'res_tds_authentication_id' => Hash::get($authenticationResult, 'res_tds_authentication_id'),
                'redirect_url' => Hash::get($authenticationResult, 'redirect_url'),
            ];
        } else {
            // 3Dセキュアなし
            $tokenResult = $this->sendCardOnetimeTokenApi($options);
            if (!isset($tokenResult)) {
                return null;
            }

            $trackingId = Hash::get($tokenResult, 'res_tracking_id', '');
            $this->lastPayment = [
                'tracking_id' => $trackingId,
            ];
            $paymentResult = $this->sendCardPaymentApi([
                'tracking_id' => $trackingId,
            ]);
            if (!isset($paymentResult)) {
                // 確定要求に失敗した際は決済取消
                try {
                    $this->cancelLastPayment();
                } catch (Throwable $e) {
                    return null;
                }

                return null;
            }

            return [
                'payment_tran_id' => Hash::get($tokenResult, 'res_sps_transaction_id'),
                'payment_tracking_id' => $trackingId,
                'payment_process_date' => FrozenTime::createFromFormat(
                    'YmdHis',
                    Hash::get($paymentResult, 'res_process_date')
                ),
            ];
        }
    }

    /**
     * 3Dセキュア決済処理を実行
     *
     * 決済要求（3DS認証情報付加・認証結果ID指定）APIと確定要求APIを送信する
     *
     * @param array $options オプション
     * @return array|null
     */
    public function execute3DSecurePayment(array $options): ?array
    {
        $requireResult = $this->sendCard3DSecureRequirePaymentApi($options);
        if (!isset($requireResult)) {
            return null;
        }

        $trackingId = Hash::get($requireResult, 'res_tracking_id');
        $this->lastPayment = [
            'tracking_id' => $trackingId,
        ];
        $paymentResult = $this->sendCardPaymentApi($options + [
            'tracking_id' => $trackingId,
        ]);
        if (!isset($paymentResult)) {
            return null;
        }

        $processDate = Hash::get($paymentResult, 'res_process_date');
        if (isset($processDate)) {
            $processDate = FrozenTime::createFromFormat('YmdHis', $processDate);
        }

        return [
            'payment_tran_id' => Hash::get($requireResult, 'res_sps_transaction_id'),
            'payment_tracking_id' => $trackingId,
            'payment_process_date' => $processDate,
        ];
    }

    /**
     * @inheritDoc
     */
    public function cancelLastPayment(): void
    {
        if (!isset($this->lastPayment)) {
            return;
        }
        $lastPayment = $this->lastPayment;

        $retryCount = $this->getConfig('cancelRetry');
        for ($i = 0; $i <= $retryCount; ++$i) {
            try {
                // API型はクレジットカードのみの前提
                $cancelResult = $this->sendCardCancelApi([
                    'tracking_id' => $lastPayment['tracking_id'],
                ]);
                if (isset($cancelResult)) {
                    $this->lastPayment = null;

                    return;
                }
            } catch (Throwable $e) {
                if ($i >= $retryCount) {
                    throw $e;
                }
                $this->writeLog($e->__toString());
            }
        }
        throw new CakeException();
    }

    /**
     * @inheritDoc
     */
    public function canGetPaymentData(array $options = []): bool
    {
        return ArrayUtility::inArray($options['pay_method'], static::GET_API_AVAILABLE);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentData(array $options = []): ?array
    {
        if ((string)$options['pay_method'] === (string)static::PAY_METHOD_CREDIT) {
            return $this->sendCardGetPaymentApi($options);
        } elseif ((string)$options['pay_method'] === (string)static::PAY_METHOD_PAYPAY) {
            return $this->sendPayPayGetPaymentApi($options);
        } elseif ((string)$options['pay_method'] === (string)static::PAY_METHOD_APPLE_PAY) {
            return $this->sendApplePayGetPaymentApi($options);
        } else {
            throw new CakeException();
        }
    }

    /**
     * @inheritDoc
     */
    public function cancelPayment(array $options = []): bool
    {
        if ((string)$options['pay_method'] === (string)static::PAY_METHOD_CREDIT) {
            return !is_null($this->sendCardCancelApi($options));
        } elseif ((string)$options['pay_method'] === (string)static::PAY_METHOD_PAYPAY) {
            return !is_null($this->sendPayPayCancelApi($options));
        } elseif ((string)$options['pay_method'] === (string)static::PAY_METHOD_APPLE_PAY) {
            if ($options['payment_status'] === static::PAYMENT_STATUS_APPLE_PAY_GRANTED) {
                return !is_null($this->sendApplePayCancelApi($options));
            } elseif ($options['payment_status'] === static::PAYMENT_STATUS_APPLE_PAY_SOLD) {
                return !is_null($this->sendApplePayRefundApi($options));
            } else {
                throw new CakeException();
            }
        } elseif ((string)$options['pay_method'] === (string)static::PAY_METHOD_AU_PAY) {
            return !is_null($this->sendAuPayCancelApi($options));
        } else {
            throw new CakeException();
        }
    }

    /**
     * @inheritDoc
     */
    public function shouldThreeDSecure(array $paymentData, array $options = []): bool
    {
        return in_array(
            Hash::get($paymentData, '@id'),
            [
                static::SPS_API_ID_CARD_3D_SECURE_AUTHENTICATION,
                static::SPS_API_ID_CARD_3D_SECURE_REQUIRE_PAYMENT,
            ],
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function getThreeDSecurePaymentResult(array $parameter, array $options = []): ?array
    {
        throw new CakeException();
    }

    /**
     * @inheritDoc
     */
    public function getLinkPaymentUrl(array $options = []): string
    {
        return $this->getConfig('linkPaymentUrl');
    }

    /**
     * @inheritDoc
     */
    public function getTds2infotokenJsUrl(): string
    {
        return $this->getConfig('tds2infotokenJsUrl');
    }

    /**
     * @inheritDoc
     */
    public function createLinkParameter(array $options): array
    {
        $parameter = [
            'pay_method' => $options['pay_method'],
            'merchant_id' => $this->getConfig('merchantId'),
            'service_id' => $this->getConfig('serviceId'),
            'cust_code' => $options['cust_code'],
            'sps_cust_no' => '',
            'sps_payment_no' => '',
            'order_id' => $options['order_id'],
            'item_id' => $options['item_id'],
            'pay_item_id' => '',
            'item_name' => $options['item_name'],
            'tax' => '',
            'amount' => $options['amount'],
            'pay_type' => static::PAY_TYPE_EACH,
            'auto_charge_type' => '',
            'service_type' => static::SERVICE_TYPE_SALES,
            'div_settele' => '',
            'last_charge_month' => '',
            'camp_type' => '',
            'tracking_id' => '',
            'terminal_type' => '',
            'success_url' => $options['success_url'],
            'cancel_url' => $options['cancel_url'],
            'error_url' => $options['error_url'],
            'pagecon_url' => $options['pagecon_url'],
            'free1' => $options['free1'],
            'free2' => '',
            'free3' => '',
            'free_csv' => '',
            'request_date' => $options['request_date'],
            'limit_second' => $this->getConfig('limitSecond'),
        ];

        $hash = $this->createSpsHashcode($parameter);

        return $parameter + [
            'sps_hashcode' => $hash,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLinkPaymentResult(array $parameter, array $options = []): ?array
    {
        $parameter = ArrayUtility::arrayMapRecursive(function ($value) {
            return mb_convert_encoding($value, mb_internal_encoding(), static::MULTIBYTE_ENCODING);
        }, $parameter);

        $encoding = null;
        if (Hash::get($options, 'forDisplayResult', false)) {
            $encoding = static::MULTIBYTE_ENCODING;
        }

        $merchantId = Hash::get($parameter, 'merchant_id');
        $serviceId = Hash::get($parameter, 'service_id');
        $hashcode = Hash::get($parameter, 'sps_hashcode');
        if (
            !is_string($merchantId) || $merchantId !== $this->getConfig('merchantId')
            || !is_string($serviceId) || $serviceId !== $this->getConfig('serviceId')
            || !is_string($hashcode) || strtolower($hashcode) !== $this->createSpsHashcode(
                $parameter,
                $encoding,
                static::LINK_RESULT_HASH_ORDER
            )
        ) {
            return null;
        }

        $result = Hash::get($parameter, 'res_result');
        $date = Hash::get($parameter, 'res_payment_date');
        if (is_string($date) && $date !== '') {
            $date = FrozenTime::createFromFormat('YmdHis', $date);
        } else {
            $date = null;
        }

        return [
            'result' => is_string($result) && $result === static::API_RESULT_OK,
            'order_id' => Hash::get($parameter, 'order_id'),
            'payment_tracking_id' => Hash::get($parameter, 'res_tracking_id'),
            'payment_process_date' => $date,
        ];
    }

    /**
     * @inheritDoc
     */
    public function isPaymentCompleted(array $paymentData, array $options = []): bool
    {
        $paymentData = Hash::get($paymentData, 'res_pay_method_info');
        if ((string)$options['pay_method'] === (string)static::PAY_METHOD_CREDIT) {
            return ArrayUtility::inArray(Hash::get($paymentData, 'payment_status'), [
                static::PAYMENT_STATUS_CARD_GRANTED,
                static::PAYMENT_STATUS_CARD_SOLD,
            ]);
        } elseif ((string)$options['pay_method'] === (string)static::PAY_METHOD_PAYPAY) {
            return ArrayUtility::inArray(Hash::get($paymentData, 'payment_status'), [
                static::PAYMENT_STATUS_PAYPAY_GRANTED,
                static::PAYMENT_STATUS_PAYPAY_SALES_PROCESSING,
                static::PAYMENT_STATUS_PAYPAY_SOLD,
            ]);
        } elseif ((string)$options['pay_method'] === (string)static::PAY_METHOD_APPLE_PAY) {
            return ArrayUtility::inArray(Hash::get($paymentData, 'payment_status'), [
                static::PAYMENT_STATUS_APPLE_PAY_GRANTED,
                static::PAYMENT_STATUS_APPLE_PAY_SOLD,
            ]);
        } else {
            throw new CakeException();
        }
    }

    /**
     * クレジットカード決済要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendCardOnetimeTokenApi(array $options): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_CARD_ONETIME_TOKEN,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'cust_code' => $options['cust_code'],
                'order_id' => $this->getOrderId(),
                'item_id' => $options['item_id'],
                'item_name' => $options['item_name'],
                'amount' => $options['amount'],
                'free1' => (string)$options['free1'],
                'pay_option_manage' => [
                    'token' => $options['token'],
                    'token_key' => $options['token_key'],
                    'cust_manage_flg' => static::FLG_OFF,
                ],
                'encrypted_flg' => static::FLG_ON,
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ],
            [
                'item_name',
                'free1',
            ]
        );
    }

    /**
     * クレジットカード確定要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendCardPaymentApi(array $options): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_CARD_PAYMENT,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'tracking_id' => $options['tracking_id'],
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ]
        );
    }

    /**
     * クレジットカード取消返金要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendCardCancelApi(array $options): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_CARD_CANCEL,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'tracking_id' => $options['tracking_id'],
                'processing_datetime' => FrozenTime::now()->format('YmdHis'),
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ]
        );
    }

    /**
     * クレジットカード決済結果参照要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendCardGetPaymentApi(array $options = []): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_CARD_GET_PAYMENT,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'tracking_id' => $options['tracking_id'],
                'encrypted_flg' => static::FLG_ON,
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ],
            [],
            [
                'res_pay_method_info.cc_company_code',
                'res_pay_method_info.cardbrand_code',
                'res_pay_method_info.recognized_no',
                'res_pay_method_info.commit_status',
                'res_pay_method_info.payment_status',
            ]
        );
    }

    /**
     * クレジットカード決済3DS認証要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendCard3DSecureAuthenticationApi(array $options = []): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_CARD_3D_SECURE_AUTHENTICATION,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'cust_code' => $options['cust_code'],
                'amount' => $options['amount'],
                'pay_option_manage' => [
                    'token' => $options['token'],
                    'token_key' => $options['token_key'],
                    'tds_info_token' => $options['tds_info_token'],
                    'tds_info_token_key' => $options['tds_info_token_key'],
                    'ok_return_url' => $options['ok_redirectUrl'],
                    'ng_return_url' => $options['ng_redirectUrl'],
                ],
                'encrypted_flg' => static::FLG_ON,
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ],
        );
    }

    /**
     * クレジットカード決済要求（3DS認証情報付加・認証結果ID指定）APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendCard3DSecureRequirePaymentApi(array $options = []): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_CARD_3D_SECURE_REQUIRE_PAYMENT,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'cust_code' => $options['cust_code'],
                'order_id' => $this->getOrderId(),
                'item_id' => $options['item_id'],
                'item_name' => $options['item_name'],
                'amount' => $options['amount'],
                'free1' => (string)$options['free1'],
                'pay_option_manage' => [
                    'cust_manage_flg' => static::FLG_OFF,
                    'tds_authentication_id' => $options['tds_authentication_id'],
                ],
                'encrypted_flg' => static::FLG_ON,
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ],
            [
                'item_name',
                'free1',
            ]
        );
    }

    /**
     * PayPay取消返金要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendPaypayCancelApi(array $options): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_PAYPAY_CANCEL,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'tracking_id' => $options['tracking_id'],
                'processing_datetime' => FrozenTime::now()->format('YmdHis'),
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ]
        );
    }

    /**
     * PayPay決済結果参照要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendPaypayGetPaymentApi(array $options = []): ?array
    {
        $parameter = [
            'merchant_id' => $this->getConfig('merchantId'),
            'service_id' => $this->getConfig('serviceId'),
        ];

        $trackingId = Hash::get($options, 'tracking_id', false);
        if ($trackingId) {
            $parameter['tracking_id'] = $trackingId;
        }

        $afterParameter = [
            'order_id' => $this->getOrderId(),
            'encrypted_flg' => static::FLG_ON,
            'request_date' => FrozenTime::now()->format('YmdHis'),
        ];

        $parameter = array_merge($parameter, $afterParameter);

        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_PAYPAY_GET_PAYMENT,
            $parameter,
            [],
            [
                'res_pay_method_info.order_date',
                'res_pay_method_info.tracking_id',
                'res_pay_method_info.payment_status',
            ]
        );
    }

    /**
     * ApplePay取消要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendApplePayCancelApi(array $options): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_APPLE_PAY_CANCEL,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'tracking_id' => $options['tracking_id'],
                'processing_datetime' => FrozenTime::now()->format('YmdHis'),
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ]
        );
    }

    /**
     * ApplePay返金要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendApplePayRefundApi(array $options): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_APPLE_PAY_REFUND,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'tracking_id' => $options['tracking_id'],
                'processing_datetime' => FrozenTime::now()->format('YmdHis'),
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ]
        );
    }

    /**
     * ApplePay決済結果参照要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendApplePayGetPaymentApi(array $options = []): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_APPLE_PAY_GET_PAYMENT,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'tracking_id' => $options['tracking_id'],
                'encrypted_flg' => static::FLG_ON,
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ],
            [],
            [
                'res_pay_method_info.cc_company_code',
                'res_pay_method_info.cardbrand_code',
                'res_pay_method_info.recognized_no',
                'res_pay_method_info.payment_status',
            ]
        );
    }

    /**
     * AuPay取消返金要求APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendAuPayCancelApi(array $options): ?array
    {
        return $this->sendApi(
            $this->getConfig('url'),
            static::SPS_API_ID_AU_PAY_CANCEL,
            [
                'merchant_id' => $this->getConfig('merchantId'),
                'service_id' => $this->getConfig('serviceId'),
                'tracking_id' => $options['tracking_id'],
                'processing_datetime' => FrozenTime::now()->format('YmdHis'),
                'request_date' => FrozenTime::now()->format('YmdHis'),
            ]
        );
    }

    /**
     * APIの送信
     *
     * @param string $url URL
     * @param string $spsApiId 機能ID
     * @param array $parameter パラメータ
     * @param array $multiByteFields マルチバイト項目
     * @param array $encryptedFields 暗号化項目
     * @return array|null
     */
    protected function sendApi(
        string $url,
        string $spsApiId,
        array $parameter,
        array $multiByteFields = [],
        array $encryptedFields = []
    ): ?array {
        try {
            $client = $this->createApiClient();
            $response = $client->post($url, $this->createXml($spsApiId, $parameter, $multiByteFields)->saveXML());
            if (!$response->isOk()) {
                $this->setErrors([
                    'err_code' => '',
                ]);
                $this->writeReponseLog($response);

                return null;
            }

            $result = Hash::get(Xml::toArray(Xml::build($response->getStringBody())), 'sps-api-response');
            if (!is_array($result) || Hash::get($result, 'res_result') !== static::API_RESULT_OK) {
                $this->setErrors([
                    'err_code' => Hash::get($result, 'res_err_code'),
                ]);
                $this->writeReponseLog($response);

                return null;
            }

            if (!empty($encryptedFields)) {
                $hasError = false;
                $result = ArrayUtility::arrayMapRecursive(
                    function ($value, $key, $path) use ($encryptedFields, &$hasError) {
                        if (
                            !$hasError && is_string($value) && $value !== ''
                            && ArrayUtility::inArray($path, $encryptedFields)
                        ) {
                            $value = $this->decryptParameter($value);
                            if (is_null($value)) {
                                $hasError = true;
                            }
                        }

                        return $value;
                    },
                    $result
                );
                if ($hasError) {
                    $this->writeReponseLog($response);

                    return null;
                }
            }

            return $result;
        } catch (Throwable $e) {
            if (isset($response)) {
                $this->writeReponseLog($response);
            }
            throw new PaymentFailedException($e->getMessage(), null, $e);
        }
    }

    /**
     * API送信用のクライアントを生成
     *
     * @return \Cake\Http\Client
     */
    protected function createApiClient(): Client
    {
        return new Client([
            'adapter' => 'Cake\Http\Client\Adapter\Curl',
            'host' => $this->getConfig('host'),
            'scheme' => $this->getConfig('scheme'),
            'ssl_verify_peer' => $this->getConfig('sslVerify'),
            'ssl_verify_peer_name' => $this->getConfig('sslVerify'),
            'headers' => [
                'Authorization' => $this->getAuthorizationHeader(),
                'Content-Type' => 'application/xml',
            ],
        ]);
    }

    /**
     * 認証ヘッダを取得
     *
     * @return string
     */
    protected function getAuthorizationHeader(): string
    {
        return 'Basic ' . base64_encode(
            sprintf('%s:%s', $this->getConfig('basicAuthId'), $this->getConfig('basicAuthPassword'))
        );
    }

    /**
     * XMLの生成
     *
     * @param string $spsApiId 機能ID
     * @param array $parameter パラメータ
     * @param array $multiByteFields マルチバイト項目
     * @return \DOMDocument
     */
    protected function createXml(string $spsApiId, array $parameter, array $multiByteFields = []): DOMDocument
    {
        $hash = $this->createSpsHashcode($parameter, static::MULTIBYTE_ENCODING);
        $parameter = $this->convertParameter($parameter, $multiByteFields, true);

        $xml = Xml::build([
            'sps-api-request' => [
                '@id' => $spsApiId,
            ] + $parameter + [
                'sps_hashcode' => $hash,
            ],
        ], ['return' => 'domdocument']);
        if (!($xml instanceof DOMDocument)) {
            throw new CakeException();
        }

        $xml->encoding = 'Shift_JIS';

        return $xml;
    }

    /**
     * パラメータをリクエスト用に変換
     *
     * @param array $parameter パラメータ
     * @param array $multiByteFields マルチバイト項目
     * @param bool $shouldEncodeBase64 マルチバイト項目のBase64エンコード
     * @return array
     */
    protected function convertParameter(
        array $parameter,
        array $multiByteFields = [],
        bool $shouldEncodeBase64 = false
    ): array {
        if (!empty($multiByteFields)) {
            $parameter = ArrayUtility::arrayMapRecursive(
                function ($value, $key, $path) use ($multiByteFields, $shouldEncodeBase64) {
                    if (ArrayUtility::inArray($path, $multiByteFields)) {
                        $value = mb_convert_encoding($value, static::MULTIBYTE_ENCODING);
                        if (!is_string($value)) {
                            throw new CakeException();
                        }
                        if ($shouldEncodeBase64) {
                            $value = base64_encode($value);
                        }
                    }

                    return $value;
                },
                $parameter
            );
        }

        return $parameter;
    }

    /**
     * チェックサムを生成
     *
     * @param array $parameter パラメータ
     * @param string|null $encoding エンコーディング
     * @param array $hashOrder ハッシュ順序
     * @return string
     */
    protected function createSpsHashcode(array $parameter, ?string $encoding = null, array $hashOrder = []): string
    {
        unset($parameter['sps_hashcode']);
        $parameter = Hash::flatten($parameter);

        if (!empty($hashOrder)) {
            foreach ($hashOrder as $field) {
                $values[] = Hash::get($parameter, $field);
            }
        } else {
            foreach ($parameter as $key => $value) {
                if (preg_match('/TOKEN$/', $key) === 0) {
                    $values[] = $value;
                }
            }
        }
        $values[] = $this->getConfig('hashKey');

        $values = ArrayUtility::arrayMapRecursive(function ($value) {
            return trim((string)$value, ' ');
        }, $values);

        $data = implode('', $values);
        if (isset($encoding)) {
            $data = mb_convert_encoding($data, $encoding);
        }

        return Security::hash($data, 'sha1', false);
    }

    /**
     * パラメータを復号化
     *
     * @param string $parameter パラメータ
     * @return string|null
     */
    protected function decryptParameter(string $parameter): ?string
    {
        $value = openssl_decrypt(
            $parameter,
            static::CRYPT_METHOD,
            $this->getConfig('encryptKey'),
            OPENSSL_ZERO_PADDING,
            $this->getConfig('encryptIv')
        );
        if ($value === false) {
            return null;
        }

        return trim($value);
    }
}
