<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\ArrayUtility;
use App\Utility\Payment\GmoPayment;
use App\Utility\Payment\PaymentInterface;
use App\Utility\StringUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Text;

/**
 * ReservationPayment Entity
 *
 * @property int $id
 * @property int $reservation_id
 * @property int $charge
 * @property string|null $payment_order_id
 * @property int $payment_service
 * @property int $payment_method_id
 * @property \Cake\I18n\FrozenTime|null $payment_limit
 * @property int $status
 * @property string|null $access_id
 * @property string|null $access_pass
 * @property string|null $payment_tran_id
 * @property string|null $payment_tracking_id
 * @property \Cake\I18n\FrozenTime|null $payment_process_date
 * @property \Cake\I18n\FrozenTime|null $payment_cancel_date
 * @property int $receive_result_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Reservation $reservation
 */
class ReservationPayment extends AppEntity
{
    /**
     * ステータス：未決済
     */
    public const STATUS_UNSETTLED = 1;

    /**
     * ステータス：決済期限切れ
     */
    public const STATUS_EXPIRED = 2;

    /**
     * ステータス：決済済み
     */
    public const STATUS_COMPLETED = 3;

    /**
     * ステータス：取消/返金済み
     */
    public const STATUS_CANCEL = 4;

    /**
     * ステータス：決済エラー
     */
    public const STATUS_ERROR = 5;

    /**
     * 決済済みのステータス
     */
    public const PAID_STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_CANCEL,
    ];

    /**
     * 決済処理続行中のステータス
     */
    public const PAYMENT_PROCESSING_STATUSES = [
        self::STATUS_UNSETTLED,
        self::STATUS_EXPIRED,
    ];

    /**
     * 結果通知受信フラグ：ON
     */
    public const RECEIVE_RESULT_FLG_ON = 1;

    /**
     * 結果通知受信フラグ：OFF
     */
    public const RECEIVE_RESULT_FLG_OFF = 0;

    public const PAYMENT_ORDER_ID = '%PREFIX%r-%RESERATION_ID%-%PAYMENT_ID%';

    /**
     * 表示用ステータス：決済中
     */
    public const DISPLAY_STATUS_UNSETTLED = 1;

    /**
     * 表示用ステータス：決済期限切れ
     */
    public const DISPLAY_STATUS_EXPIRED = 2;

    /**
     * 表示用ステータス：決済済み
     */
    public const DISPLAY_STATUS_COMPLETED = 3;

    /**
     * 表示用ステータス：取消/返金済み
     */
    public const DISPLAY_STATUS_CANCEL = 4;

    /**
     * 表示用ステータス：決済エラー
     */
    public const DISPLAY_STATUS_ERROR = 5;

    /**
     * 本人確認情報 電話番号タイプ：勤務先
     */
    public const PHONE_TYPE_WORK = 1;

    /**
     * 本人確認情報 電話番号タイプ：自宅
     */
    public const PHONE_TYPE_HOME = 2;

    /**
     * 本人確認情報 電話番号タイプ：携帯
     */
    public const PHONE_TYPE_MOBILE = 3;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'reservation_id' => true,
        'charge' => false,
        'payment_order_id' => false,
        'payment_service' => false,
        'payment_method_id' => false,
        'payment_limit' => false,
        'status' => false,
        'access_id' => false,
        'access_pass' => false,
        'payment_tran_id' => false,
        'payment_tracking_id' => false,
        'payment_process_date' => false,
        'payment_cancel_date' => false,
        'receive_result_flg' => false,
        'created' => false,
        'modified' => false,
        'reservation' => false,
        'payment_token' => true,
        'kyc_values' => true,
    ];

    protected $_virtual = [
        'payment_token',
        'kyc_values',
    ];

    /**
     * @var array|null
     */
    protected $paymentData;

    /**
     * @var array|null
     */
    protected $paymentError;

    /**
     * 決済オーダーIDを生成
     *
     * @return string
     */
    public function generatePaymentOrderId()
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();
        if ($paymentSetting->isPaymentServiceGmo()) {
            $pattern = [
                '/%PREFIX%/',
                '/%RESERATION_ID%/',
                '/%PAYMENT_ID%/',
            ];
            $replacement = [
                $paymentSetting->get('order_id_prefix'),
                $this->get('reservation_id'),
                $this->get('id'),
            ];
            $paymentOrderId = preg_replace($pattern, $replacement, static::PAYMENT_ORDER_ID);
            if (!is_string($paymentOrderId)) {
                throw new CakeException();
            }

            return $paymentOrderId;
        } elseif ($paymentSetting->isPaymentServiceSb()) {
            return Text::uuid();
        } else {
            throw new CakeException();
        }
    }

    /**
     * API型の決済を実行
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param \App\Utility\Payment\PaymentInterface $payment 決済モジュール
     * @return bool
     */
    public function executeApiPayment(Reservation $reservation, PaymentInterface $payment)
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();

        $this->set([
            'payment_order_id' => $this->generatePaymentOrderId(),
        ], ['guard' => false]);

        $payment->setOrderId($this->get('payment_order_id'));
        $payment->setDataId((string)$reservation->get('id'));
        $paymentData = $payment->executeTokenPayment($this->createApiPaymentParameter($reservation));
        if (!isset($paymentData)) {
            return false;
        }

        if ($paymentSetting->is3DSecureFlgOn() && $payment->shouldThreeDSecure($paymentData)) {
            if ($paymentSetting->isPaymentServiceGmo()) {
                $this->set([
                    'access_id' => Hash::get($paymentData, 'access_id'),
                    'access_pass' => Hash::get($paymentData, 'access_pass'),
                    'payment_limit' => FrozenTime::now()->addSeconds(
                        Configure::readOrFail(
                            'Setting.payment.' . PaymentSetting::PAYMENT_SERVICE_GMO . '.threeDSecureLimit'
                        )
                    )->format('Y-m-d H:i:s'),
                    'redirect_url' => Hash::get($paymentData, 'redirect_url'),
                ], ['guard' => false]);
            } elseif ($paymentSetting->isPaymentServiceSb()) {
                $this->set([
                    'payment_tran_id' => Hash::get($paymentData, 'res_sps_transaction_id'),
                    'authentication_id' => Hash::get($paymentData, 'res_tds_authentication_id'),
                    'payment_limit' => FrozenTime::now()->addSeconds(
                        Configure::readOrFail(
                            'Setting.payment.' . PaymentSetting::PAYMENT_SERVICE_SB . '.threeDSecureLimit'
                        )
                    )->format('Y-m-d H:i:s'),
                    'redirect_url' => Hash::get($paymentData, 'redirect_url'),
                ], ['guard' => false]);
            } else {
                throw new CakeException();
            }
        } else {
            $this->set([
                'payment_tran_id' => Hash::get($paymentData, 'payment_tran_id'),
                'payment_tracking_id' => Hash::get($paymentData, 'payment_tracking_id'),
                'payment_process_date' => Hash::get($paymentData, 'payment_process_date'),
                'status' => static::STATUS_COMPLETED,
            ], ['guard' => false]);
        }

        return true;
    }

    /**
     * 3Dセキュアの判定
     *
     * @return bool
     */
    public function isThreeDSecure()
    {
        return $this->has('redirect_url');
    }

    /**
     * 決済完了の判定
     *
     * @return bool
     */
    public function isPaid()
    {
        return ArrayUtility::inArray($this->get('status'), static::PAID_STATUSES);
    }

    /**
     * APIで決済完了を判定
     *
     * @return bool
     */
    public function isPaymentCompleted()
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();
        $payment = $paymentSetting->getPaymentModule();

        if ($paymentSetting->isPaymentServiceGmo()) {
            return $payment->isPaymentCompleted($this->getPaymentData());
        } elseif ($paymentSetting->isPaymentServiceSb()) {
            $paymentMethodType = $paymentMethodsTable->getPaymentMethodType($this->get('payment_method_id'));

            $payMethod = PaymentMethod::LINKAGE_PAYMENT_METHOD[PaymentSetting::PAYMENT_SERVICE_SB][$paymentMethodType];

            return $payment->isPaymentCompleted($this->getPaymentData(false, $this->get('payment_tracking_id')), [
                'pay_method' => $payMethod,
            ]);
        } else {
            throw new CakeException();
        }
    }

    /**
     * 決済データを取得
     *
     * @param bool $validationFlg バリデーションフラグ
     * @param string|null $paymentTrackingId 決済トラッキングID
     * @return array
     */
    public function getPaymentData($validationFlg = false, $paymentTrackingId = null)
    {
        if (!isset($this->paymentData)) {
            /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
            $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');
            /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
            $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

            $paymentSetting = $paymentSettingsTable->getDataOrFail();
            if ((string)$paymentSetting->get('payment_service') !== (string)$this->get('payment_service')) {
                throw new CakeException();
            }

            $payment = $paymentSetting->getPaymentModule();
            $payment->setOrderId($this->get('payment_order_id'));

            if ($paymentSetting->isPaymentServiceGmo()) {
                $paymentData = $payment->getPaymentData();
                if (!isset($paymentData)) {
                    $paymentData = [];
                    $this->paymentError = $payment->getErrors();
                }
            } elseif ($paymentSetting->isPaymentServiceSb()) {
                $paymentMethodType = $paymentMethodsTable->getPaymentMethodType($this->get('payment_method_id'));
                $payMethod
                    = PaymentMethod::LINKAGE_PAYMENT_METHOD[PaymentSetting::PAYMENT_SERVICE_SB][$paymentMethodType];

                $options = [
                    'pay_method' => $payMethod,
                ];
                if (!is_null($paymentTrackingId)) {
                    $options['tracking_id'] = $paymentTrackingId;
                }

                $paymentData = $payment->getPaymentData($options);
                if (!is_array($paymentData)) {
                    if ($validationFlg) {
                        return [];
                    }
                    throw new CakeException();
                }
            } else {
                throw new CakeException();
            }

            $this->paymentData = $paymentData;
        }

        return $this->paymentData;
    }

    /**
     * API型決済のパラメータを生成
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return array
     */
    protected function createApiPaymentParameter(Reservation $reservation)
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentToken = $this->get('payment_token');
        if (!is_array($paymentToken)) {
            $paymentToken = [];
        }

        $paymentSetting = $paymentSettingsTable->getDataOrFail();
        if ($paymentSetting->isPaymentServiceGmo()) {
            $configKey = 'Setting.payment.' . PaymentSetting::PAYMENT_SERVICE_GMO;
            $parameter = [
                'jobCd' => Configure::readOrFail($configKey . '.jobCodeApi.' . $paymentSetting->get('job_code')),
                'amount' => $this->get('charge'),
                'method' => GmoPayment::API_METHOD_ONE_TIME,
                'token' => Hash::get($paymentToken, 'token', ''),
            ];
            if ($paymentSetting->is3DSecureFlgOn()) {
                $parameter['TdFlag'] = GmoPayment::API_TD_FLAG_ON;
                $parameter['RetUrl'] = Router::url([
                    'prefix' => 'User',
                    'controller' => 'Payment',
                    'action' => 'finish',
                    'id' => $this->get('reservation_id'),
                ], true);
                $parameter['CallbackType'] = GmoPayment::API_CALLBACK_TYPE_POST;

                if ($this->has('kyc_values')) {
                    $kycValues = $this->get('kyc_values');
                    if (!empty($kycValues['emailAddress'])) {
                        $parameter['Tds2Email'] = $kycValues['emailAddress'];
                    }
                    if (!empty($kycValues['phoneType']) && !empty($kycValues['phoneNumber'])) {
                        $phoneType = $kycValues['phoneType'];
                        $phoneCountryCodeParams = GmoPayment::PHONE_COUNTRY_CODE_PARAMS;
                        $phoneNumberParams = GmoPayment::PHONE_NUMBER_PARAMS;

                        // フォーマットされた国コードと電話番号を取得
                        $formatValues = StringUtility::formatCountryCodeAndPhoneNumber($kycValues['phoneNumber']);
                        $countryCode = Hash::get($formatValues, 'countryCode');
                        $number = Hash::get($formatValues, 'number');
                        $parameter[$phoneCountryCodeParams[$phoneType]] = (string)$countryCode;
                        $parameter[$phoneNumberParams[$phoneType]] = (string)$number;
                    }
                }
            } else {
                $parameter['TdFlag'] = GmoPayment::API_TD_FLAG_OFF;
                $parameter['TdRequired'] = GmoPayment::API_TD_REQUIRED_OFF;
            }

            return $parameter;
        } elseif ($paymentSetting->isPaymentServiceSb()) {
            $user = $reservation->getUserEntity();
            $event = $reservation->getEventEntity();
            if (!isset($user) || !isset($event)) {
                throw new CakeException();
            }

            $parameter = [
                'cust_code' => $paymentSetting->get('cust_code_prefix') . $user->get('id'),
                'item_id' => $event->get('id'),
                'item_name' => $event->get('name'),
                'amount' => $this->get('charge'),
                'free1' => $reservation->get('id'),
                'token' => Hash::get($paymentToken, 'token', ''),
                'token_key' => Hash::get($paymentToken, 'token_key', ''),
            ];
            if ($paymentSetting->is3DSecureFlgOn()) {
                $parameter['isUse3DSecure'] = true;
                $parameter['ok_redirectUrl'] = Router::url([
                    'prefix' => 'User',
                    'controller' => 'Payment',
                    'action' => 'finish',
                    'id' => $this->get('reservation_id'),
                ], true);
                $parameter['ng_redirectUrl'] = Router::url([
                    'prefix' => 'User',
                    'controller' => 'Payment',
                    'action' => 'error-sb3ds',
                    'id' => $this->get('reservation_id'),
                ], true);
                $parameter['tds_info_token'] = Hash::get($paymentToken, 'tds2info_token', '');
                $parameter['tds_info_token_key'] = Hash::get($paymentToken, 'tds2info_token_key', '');
            }

            return $parameter;
        } else {
            throw new CakeException();
        }
    }

    /**
     * 決済代行会社 = SB の判定
     *
     * @return bool
     */
    public function isReservationPaymentServiceSb()
    {
        return (string)$this->get('payment_service') === (string)PaymentSetting::PAYMENT_SERVICE_SB;
    }

    /**
     * 決済トラッキングIDの更新が可能か判定
     *
     * @return bool
     */
    public function canUpdatePaymentTrackingId()
    {
        /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
        $reservationPaymentsTable = $this->getTableLocator()->get('ReservationPayments');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        $paymentSetting = $paymentSettingsTable->getData();
        if (!isset($paymentSetting)) {
            return false;
        }

        $status = $reservationPaymentsTable->getReservationPaymentStatus(
            $this->get('status'),
            $this->get('payment_limit')
        );

        $methodType = $paymentMethodsTable->getPaymentMethodType(
            $this->get('payment_method_id')
        );

        if (
            $paymentSetting->isPaymentServiceGmo()
            || $status === static::DISPLAY_STATUS_UNSETTLED
            || $status === static::DISPLAY_STATUS_CANCEL
            || $this->get('receive_result_flg') === static::RECEIVE_RESULT_FLG_ON
            || $methodType === PaymentMethod::TYPE_CARD
        ) {
            return false;
        }

        return true;
    }

    /**
     * API型の決済取消要求を実行
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param \App\Model\Entity\PaymentSetting $paymentSetting 決済モジュール
     * @return array|null
     */
    public function cancelApiPayment(Reservation $reservation, PaymentSetting $paymentSetting)
    {
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

        /** @var \App\Utility\Payment\AbstractPayment $payment */
        $payment = clone $paymentSetting->getPaymentModule();

        $paymentMethodType =
            $paymentMethodsTable->getPaymentMethodType($this->get('payment_method_id'));

        $payMethod = PaymentMethod::LINKAGE_PAYMENT_METHOD[PaymentSetting::PAYMENT_SERVICE_SB][$paymentMethodType];

        $payment->setDataId((string)$reservation->get('id'));

        if ($payment->canGetPaymentData(['pay_method' => $payMethod])) {
            $payment->setOrderId($this->get('payment_order_id'));
            $paymentData = $payment->getPaymentData([
                'tracking_id' => $this->get('payment_tracking_id'),
                'pay_method' => $payMethod,
            ]);
        }

        if (
            $paymentMethodType === PaymentMethod::TYPE_AU_PAY ||
            (isset($paymentData) && $this->isPaymentCompleted())
        ) {
            $paymentStatus = $paymentMethodType === PaymentMethod::TYPE_APPLE_PAY && isset($paymentData) ?
                Hash::get($paymentData, 'payment_status') : null;
            $payment->cancelPayment([
                'tracking_id' => $this->get('payment_tracking_id'),
                'pay_method' => $payMethod,
                'payment_status' => $paymentStatus,
            ]);
        }

        return $payment->getErrors();
    }

    /**
     * 決済のエラーを取得
     *
     * @return array|null
     */
    public function getPaymentError()
    {
        if (!isset($this->paymentError)) {
            return null;
        }

        return $this->paymentError;
    }

    /**
     * API情報を暗号化
     *
     * @param string $apiInfo API情報
     * @return string
     */
    public function encryptApiInfo(string $apiInfo)
    {
        return StringUtility::encrypt($apiInfo, (string)$this->get('id'));
    }

    /**
     * API情報を復号化
     *
     * @param string $crypt 暗号化文字列
     * @return string
     */
    public function decryptApiInfo(string $crypt)
    {
        $value = StringUtility::decrypt($crypt, (string)$this->get('id'));
        if (!isset($value)) {
            throw new CakeException();
        }

        return $value;
    }

    /**
     * AccessPassのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setAccessPass($data)
    {
        if (!isset($data)) {
            return null;
        }

        return $this->encryptApiInfo($data);
    }

    /**
     * 決済処理続行中の判定
     *
     * @return bool
     */
    public function isPaymentProcessing()
    {
        return ArrayUtility::inArray($this->get('status'), static::PAYMENT_PROCESSING_STATUSES);
    }
}
