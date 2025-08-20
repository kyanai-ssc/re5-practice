<?php
declare(strict_types=1);

namespace App\Utility\Payment;

use App\Model\Entity\ReservationPayment;
use App\Utility\ArrayUtility;
use Cake\Core\Exception\CakeException;
use Cake\Http\Client;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
use Throwable;

/**
 * GMOペイメント
 */
class GmoPayment extends AbstractPayment
{
    public const API_JOB_CD_CHECK = 'CHECK';
    public const API_JOB_CD_CAPTURE = 'CAPTURE';
    public const API_JOB_CD_AUTH = 'AUTH';
    public const API_JOB_CD_SALES = 'SALES';
    public const API_JOB_CD_VOID = 'VOID';
    public const API_JOB_CD_RETURN = 'RETURN';
    public const API_JOB_CD_RETURNX = 'RETURNX';
    public const API_JOB_CD_CANCEL = 'CANCEL';
    public const API_JOB_CD_SAUTH = 'SAUTH';

    public const API_STATUS_AUTH = 'AUTH';
    public const API_STATUS_SALES = 'SALES';
    public const API_STATUS_CAPTURE = 'CAPTURE';
    public const API_STATUS_UNPROCESSED = 'UNPROCESSED';
    public const API_STATUS_AUTHENTICATED = 'AUTHENTICATED';
    public const API_STATUS_VOID = 'VOID';
    public const API_STATUS_RETURN = 'RETURN';
    public const API_STATUS_RETURNX = 'RETURNX';

    public const API_STATUS_PAYMENT_FINISH = [
        self::API_STATUS_AUTH,
        self::API_STATUS_SALES,
        self::API_STATUS_CAPTURE,
    ];
    public const API_STATUS_PAYMENT_CANCEL = [
        self::API_STATUS_UNPROCESSED,
        self::API_STATUS_AUTHENTICATED,
        self::API_STATUS_VOID,
        self::API_STATUS_RETURN,
        self::API_STATUS_RETURNX,
    ];

    public const API_METHOD_ONE_TIME = 1;
    public const API_METHOD_ON_TIME = 2;
    public const API_METHOD_BONUS_ONE_TIME = 3;
    public const API_METHOD_BONUS_ON_TIME = 4;
    public const API_METHOD_REVOLVING = 5;

    public const API_TD_FLAG_OFF = 0;
    public const API_TD_FLAG_ON = 2;

    public const API_TD_REQUIRED_OFF = 2;

    public const API_CALLBACK_TYPE_POST = 1;

    public const API_ACS_EMV_3D_SECURE = 2;

    public const API_URL_ENTRY_TRAN = '/payment/EntryTran.idPass';
    public const API_URL_EXEC_TRAN = '/payment/ExecTran.idPass';
    public const API_URL_CANCEL_TRAN = '/payment/AlterTran.idPass';
    public const API_URL_SECURE_TRAN = '/payment/SecureTran2.idPass';
    public const API_URL_SEARCH_TRAN = '/payment/SearchTrade.idPass';

    public const API_RESPONSE_ENTRY_TRAN = [
        'AccessID',
        'AccessPass',
    ];
    public const API_RESPONSE_CANCEL_TRAN = [
        'TranID',
        'TranDate',
    ];

    /**
     * タイプ別 国コードパラメータ
     */
    public const PHONE_COUNTRY_CODE_PARAMS = [
        ReservationPayment::PHONE_TYPE_WORK => 'Tds2WorkPhoneCC',
        ReservationPayment::PHONE_TYPE_HOME => 'Tds2HomePhoneCC',
        ReservationPayment::PHONE_TYPE_MOBILE => 'Tds2MobilePhoneCC',
    ];

    /**
     * タイプ別 電話番号パラメータ
     */
    public const PHONE_NUMBER_PARAMS = [
        ReservationPayment::PHONE_TYPE_WORK => 'Tds2WorkPhoneSubscriber',
        ReservationPayment::PHONE_TYPE_HOME => 'Tds2HomePhoneSubscriber',
        ReservationPayment::PHONE_TYPE_MOBILE => 'Tds2MobilePhoneSubscriber',
    ];

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
        $entryTranResult = $this->sendEntryTranApi($options);
        if (!isset($entryTranResult)) {
            return null;
        }

        $execTranResult = $this->sendExecTranApi([
            'AccessID' => Hash::get($entryTranResult, 'AccessID'),
            'AccessPass' => Hash::get($entryTranResult, 'AccessPass'),
        ] + $options);
        if (!isset($execTranResult)) {
            return null;
        }

        if (isset($execTranResult['TranID'])) {
            $this->lastPayment = [
                'AccessID' => $entryTranResult['AccessID'],
                'AccessPass' => $entryTranResult['AccessPass'],
                'ProcessDate' => Hash::get($execTranResult, 'TranDate'),
            ];
        }

        $processDate = Hash::get($execTranResult, 'TranDate');
        if (isset($processDate) && is_string($processDate) && $processDate !== '') {
            $processDate = FrozenTime::createFromFormat('YmdHis', $processDate);
        }

        $redirectUrl = Hash::get($execTranResult, 'RedirectUrl');
        if (is_string($redirectUrl) && isset($execTranResult['t']) && is_string($execTranResult['t'])) {
            $redirectUrl .= '&' . http_build_query([
                't' => $execTranResult['t'],
            ]);
        }

        return [
            'access_id' => Hash::get($entryTranResult, 'AccessID'),
            'access_pass' => Hash::get($entryTranResult, 'AccessPass'),
            'payment_tran_id' => Hash::get($execTranResult, 'TranID'),
            'payment_process_date' => $processDate,
            'redirect_url' => $redirectUrl,
        ] + $execTranResult;
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
                $cancelResult = $this->sendCancelTranApi(
                    $lastPayment['AccessID'],
                    $lastPayment['AccessPass']
                );
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
    public function shouldThreeDSecure(array $paymentData, array $options = []): bool
    {
        $acs = Hash::get($paymentData, 'ACS');
        if (is_scalar($acs)) {
            $acs = (string)$acs;
        }

        return $acs === (string)static::API_ACS_EMV_3D_SECURE;
    }

    /**
     * @inheritDoc
     */
    public function getThreeDSecurePaymentResult(array $parameter, array $options = []): ?array
    {
        $result = $this->sendSecureTranApi($parameter);
        if (!isset($result)) {
            return null;
        }

        $processDate = Hash::get($result, 'TranDate');
        if (isset($processDate)) {
            $processDate = FrozenTime::createFromFormat('YmdHis', $processDate);
        }

        return [
            'payment_tran_id' => Hash::get($result, 'TranID'),
            'payment_process_date' => $processDate,
        ];
    }

    /**
     * @inheritDoc
     */
    public function isPaymentCompleted(array $paymentData, array $options = []): bool
    {
        return ArrayUtility::inArray(Hash::get($paymentData, 'Status'), static::API_STATUS_PAYMENT_FINISH);
    }

    /**
     * @inheritDoc
     */
    public function canGetPaymentData(array $options = []): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentData(array $options = []): ?array
    {
        $result = $this->sendSearchTradeApi();
        if (!isset($result)) {
            return null;
        }

        if (
            !isset($result['Status'])
            || (
                !ArrayUtility::inArray($result['Status'], static::API_STATUS_PAYMENT_FINISH)
                && !ArrayUtility::inArray($result['Status'], static::API_STATUS_PAYMENT_CANCEL)
            )
        ) {
            $this->writeLog((string)json_encode($result));
            $this->setErrors([
                'Status' => Hash::get($result, 'Status'),
            ]);

            return null;
        }

        $processDate = Hash::get($result, 'ProcessDate');
        if (isset($processDate)) {
            $processDate = FrozenTime::createFromFormat('YmdHis', $processDate);
        }

        return [
            'payment_tran_id' => Hash::get($result, 'TranID'),
            'payment_process_date' => $processDate,
        ] + $result;
    }

    /**
     * @inheritDoc
     */
    public function cancelPayment(array $options = []): bool
    {
        throw new CakeException();
    }

    /**
     * 取引登録APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendEntryTranApi(array $options): ?array
    {
        $data = [
            'ShopID' => $this->getConfig('shopId'),
            'ShopPass' => $this->getConfig('shopPass'),
            'OrderID' => $this->getOrderId(),
            'JobCd' => Hash::get($options, 'jobCd'),
            'Amount' => Hash::get($options, 'amount'),
        ];
        if (isset($options['TdFlag'])) {
            $data['TdFlag'] = $options['TdFlag'];
        }
        if (isset($options['TdRequired'])) {
            $data['TdRequired'] = $options['TdRequired'];
        }
        $result = $this->sendApi(static::API_URL_ENTRY_TRAN, $data, static::API_RESPONSE_ENTRY_TRAN);

        return $result;
    }

    /**
     * 決済実行APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendExecTranApi(array $options): ?array
    {
        $data = [
            'AccessID' => Hash::get($options, 'AccessID'),
            'AccessPass' => Hash::get($options, 'AccessPass'),
            'OrderID' => $this->getOrderId(),
            'Method' => Hash::get($options, 'method'),
            'Token' => Hash::get($options, 'token'),
        ];
        if (isset($options['RetUrl'])) {
            $data['RetUrl'] = $options['RetUrl'];
        }
        if (isset($options['CallbackType'])) {
            $data['CallbackType'] = $options['CallbackType'];
        }

        if (isset($options['Tds2Email'])) {
            $data['Tds2Email'] = $options['Tds2Email'];
        }

        if (isset($options['Tds2WorkPhoneCC'])) {
            $data['Tds2WorkPhoneCC'] = $options['Tds2WorkPhoneCC'];
        }
        if (isset($options['Tds2WorkPhoneSubscriber'])) {
            $data['Tds2WorkPhoneSubscriber'] = $options['Tds2WorkPhoneSubscriber'];
        }

        if (isset($options['Tds2HomePhoneCC'])) {
            $data['Tds2HomePhoneCC'] = $options['Tds2HomePhoneCC'];
        }
        if (isset($options['Tds2HomePhoneSubscriber'])) {
            $data['Tds2HomePhoneSubscriber'] = $options['Tds2HomePhoneSubscriber'];
        }

        if (isset($options['Tds2MobilePhoneCC'])) {
            $data['Tds2MobilePhoneCC'] = $options['Tds2MobilePhoneCC'];
        }
        if (isset($options['Tds2MobilePhoneSubscriber'])) {
            $data['Tds2MobilePhoneSubscriber'] = $options['Tds2MobilePhoneSubscriber'];
        }

        $result = $this->sendApi(static::API_URL_EXEC_TRAN, $data);

        return $result;
    }

    /**
     * 決済取消APIの送信
     *
     * @param string $accessId 取引ID
     * @param string $accessPass 取引パスワード
     * @return array|null
     */
    protected function sendCancelTranApi($accessId, $accessPass): ?array
    {
        $data = [
            'ShopID' => $this->getConfig('shopId'),
            'ShopPass' => $this->getConfig('shopPass'),
            'AccessID' => $accessId,
            'AccessPass' => $accessPass,
            'JobCd' => static::API_JOB_CD_CANCEL,
        ];
        $result = $this->sendApi(static::API_URL_CANCEL_TRAN, $data, static::API_RESPONSE_CANCEL_TRAN);

        return $result;
    }

    /**
     * 3DS2.0認証後決済実行APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendSecureTranApi(array $options): ?array
    {
        return $this->sendApi(static::API_URL_SECURE_TRAN, [
            'AccessID' => Hash::get($options, 'AccessID'),
            'AccessPass' => Hash::get($options, 'AccessPass'),
        ]);
    }

    /**
     * 取引状態参照APIの送信
     *
     * @param array $options オプション
     * @return array|null
     */
    protected function sendSearchTradeApi(array $options = []): ?array
    {
        return $this->sendApi(static::API_URL_SEARCH_TRAN, [
            'ShopID' => $this->getConfig('shopId'),
            'ShopPass' => $this->getConfig('shopPass'),
            'OrderID' => $this->getOrderId(),
        ]);
    }

    /**
     * APIの送信
     *
     * @param string $url URL
     * @param array $data データ
     * @param array|null $checkKeys 存在チェックするキー
     * @return array|null
     */
    protected function sendApi($url, $data, $checkKeys = null): ?array
    {
        $client = new Client([
            'adapter' => 'Cake\Http\Client\Adapter\Curl',
            'host' => $this->getConfig('host'),
            'scheme' => $this->getConfig('scheme'),
            'ssl_verify_peer' => $this->getConfig('sslVerify'),
            'ssl_verify_peer_name' => $this->getConfig('sslVerify'),
        ]);

        $response = $client->post($url, $data);
        if (!$response->isOk()) {
            $this->writeReponseLog($response);
            throw new CakeException();
        }

        $result = [];
        parse_str($response->getStringBody(), $result);
        if (empty($result)) {
            $this->writeReponseLog($response);
            throw new CakeException();
        }

        if (!$this->checkErrors($result)) {
            $this->setErrors([
                'ErrCode' => Hash::get($result, 'ErrCode'),
                'ErrInfo' => Hash::get($result, 'ErrInfo'),
            ]);
            $this->writeLog((string)json_encode($this->getErrors()));

            return null;
        }

        foreach ((array)$checkKeys as $key) {
            if (!array_key_exists($key, $result)) {
                $this->writeReponseLog($response);
                throw new CakeException();
            }
        }

        return $result;
    }

    /**
     * APIのエラー判定
     *
     * @param array $result 応答結果
     * @return bool
     */
    protected function checkErrors($result): bool
    {
        $errorKeys = [
            'ErrCode',
            'ErrInfo',
        ];
        foreach ($errorKeys as $errorKey) {
            if (array_key_exists($errorKey, $result)) {
                return false;
            }
        }

        return true;
    }
}
