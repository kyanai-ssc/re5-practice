<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\ArrayUtility;
use App\Utility\Payment\PaymentFactory;
use App\Utility\StringUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;

/**
 * PaymentSetting Entity
 *
 * @property int $id
 * @property int $payment_service
 * @property int $environment
 * @property string|null $shop_id
 * @property string|null $merchant_id
 * @property string|null $service_id
 * @property string|null $cust_code_prefix
 * @property string|null $order_id_prefix
 * @property int|null $job_code
 * @property int $three_d_secure_flg
 * @property string $card_brand
 * @property string|null $shop_password
 * @property string|null $hash_key
 * @property string|null $basic_auth_id
 * @property string|null $basic_auth_password
 * @property string|null $encrypt_key
 * @property string|null $encrypt_iv
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class PaymentSetting extends AppEntity
{
    /**
     * 決済代行会社：GMO
     */
    public const PAYMENT_SERVICE_GMO = 1;

    /**
     * 決済代行会社：SB
     */
    public const PAYMENT_SERVICE_SB = 2;

    /**
     * 環境：本番
     */
    public const ENVIRONMENT_PRODUCTION = 1;

    /**
     * 環境：テスト
     */
    public const ENVIRONMENT_STAGING = 2;

    /**
     * 処理区分：即時売上
     */
    public const JOB_CODE_IMMEDIATE = 1;

    /**
     * 処理区分：仮売上
     */
    public const JOB_CODE_PROVISIONAL = 2;

    /**
     * 3Dセキュア：利用しない
     */
    public const THREE_D_SECURE_FLG_OFF = 0;

    /**
     * 3Dセキュア：利用する
     */
    public const THREE_D_SECURE_FLG_ON = 1;

    /**
     * カード会社：JSB
     */
    public const CARD_BLAND_JCB = 1;

    /**
     * カード会社：VISA
     */
    public const CARD_BLAND_VISA = 2;

    /**
     * カード会社：MasterCard
     */
    public const CARD_BLAND_MASTERCARD = 3;

    /**
     * カード会社：American Express
     */
    public const CARD_BLAND_AMERICAN_EXPRESS = 4;

    /**
     * カード会社：DinersClub
     */
    public const CARD_BLAND_DINERS_CLUB = 5;

    /**
     * API情報の暗号化キー
     */
    public const API_INFO_CRYPT_KEY = [
        self::PAYMENT_SERVICE_GMO => 'shop_id',
        self::PAYMENT_SERVICE_SB => 'merchant_id',
    ];

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'payment_service' => true,
        'environment' => true,
        'shop_id' => true,
        'merchant_id' => true,
        'service_id' => true,
        'cust_code_prefix' => true,
        'order_id_prefix' => true,
        'job_code' => true,
        'three_d_secure_flg' => true,
        'card_brand' => true,
        'shop_password' => true,
        'hash_key' => true,
        'basic_auth_id' => true,
        'basic_auth_password' => true,
        'encrypt_key' => true,
        'encrypt_iv' => true,
        'created' => false,
        'modified' => false,
        'card_name' => false,
    ];

    protected $_virtual = [
        'card_name',
    ];

    /**
     * @var \App\Utility\Payment\PaymentInterface|null
     */
    protected $paymentModule;

    /**
     * 決済代行会社 = GMO の判定
     *
     * @return bool
     */
    public function isPaymentServiceGmo()
    {
        return (string)$this->get('payment_service') === (string)static::PAYMENT_SERVICE_GMO;
    }

    /**
     * 決済代行会社 = SB の判定
     *
     * @return bool
     */
    public function isPaymentServiceSb()
    {
        return (string)$this->get('payment_service') === (string)static::PAYMENT_SERVICE_SB;
    }

    /**
     * 3Dセキュア = 利用する の判定
     *
     * @return bool
     */
    public function is3DSecureFlgOn()
    {
        return (string)$this->get('three_d_secure_flg') === (string)static::THREE_D_SECURE_FLG_ON;
    }

    /**
     * 編集可能チェック
     *
     * @return bool
     */
    public function canEdit()
    {
        return $this->isPaymentServiceGmo() || $this->isPaymentServiceSb();
    }

    /**
     * 決済モジュールを生成
     *
     * @return \App\Utility\Payment\PaymentInterface
     */
    public function getPaymentModule()
    {
        if (!isset($this->paymentModule)) {
            $this->paymentModule = PaymentFactory::createPaymentModule($this);
        }

        return $this->paymentModule;
    }

    /**
     * 有効期限(月)の値リストを取得
     *
     * @return array
     */
    public function getExpireMonthValueOptions()
    {
        $valueOptions = [];
        for ($i = 1; $i <= 12; ++$i) {
            $value = sprintf('%02d', $i);
            $valueOptions[$value] = $value;
        }

        return $valueOptions;
    }

    /**
     * 有効期限(年)の値リストを取得
     *
     * @return array
     */
    public function getExpireYearValueOptions()
    {
        $valueOptions = [];
        for ($i = 0; $i <= 20; ++$i) {
            $value = sprintf('%02d', (((int)$this->commonData()->getNowDateTime()->format('Y')) + $i) % 100);
            $valueOptions[$value] = $value;
        }

        return $valueOptions;
    }

    /**
     * 決済ステータス初期値を取得
     *
     * @return int
     */
    public function getInitialPaymentStatus()
    {
        if ($this->isPaymentServiceGmo()) {
            if ($this->is3DSecureFlgOn()) {
                return PaymentStatus::TYPE_PAYMENT_YET;
            } else {
                return $this->getPaymentSuccessStatus();
            }
        } elseif ($this->isPaymentServiceSb()) {
            if ($this->is3DSecureFlgOn()) {
                return PaymentStatus::TYPE_PAYMENT_YET;
            } else {
                return PaymentStatus::TYPE_PAYMENT_COMPLETE;
            }
        } else {
            throw new CakeException();
        }
    }

    /**
     * 決済成功時のステータスを取得
     *
     * @return int
     */
    public function getPaymentSuccessStatus()
    {
        if ($this->isPaymentServiceGmo()) {
            return Configure::readOrFail('Master.payment.credit.jobCodePaymentStatusType.' . $this->get('job_code'));
        } elseif ($this->isPaymentServiceSb()) {
            return PaymentStatus::TYPE_PAYMENT_COMPLETE;
        } else {
            throw new CakeException();
        }
    }

    /**
     * API情報を暗号化
     *
     * @param string $apiInfo API情報
     * @return string
     */
    public function encryptApiInfo(string $apiInfo)
    {
        return StringUtility::encrypt($apiInfo, $this->get(static::API_INFO_CRYPT_KEY[$this->get('payment_service')]));
    }

    /**
     * API情報を復号化
     *
     * @param string $crypt 暗号化文字列
     * @return string
     */
    public function decryptApiInfo(string $crypt)
    {
        $value = StringUtility::decrypt($crypt, $this->get(static::API_INFO_CRYPT_KEY[$this->get('payment_service')]));
        if (!isset($value)) {
            throw new CakeException();
        }

        return $value;
    }

    /**
     * カードブランドのミューテーター
     *
     * @param array $data データ
     * @return array
     */
    protected function _setCardBrand($data)
    {
        $data = ArrayUtility::arrayMapRecursive(function ($value) {
            return (int)$value;
        }, array_unique($data));
        sort($data, SORT_NUMERIC);

        return $data;
    }

    /**
     * ショップパスワードのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setShopPassword($data)
    {
        if (!isset($data)) {
            return null;
        }

        return $this->encryptApiInfo($data);
    }

    /**
     * ハッシュキーのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setHashKey($data)
    {
        if (!isset($data)) {
            return null;
        }

        return $this->encryptApiInfo($data);
    }

    /**
     * ベーシック認証パスワードのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setBasicAuthPassword($data)
    {
        if (!isset($data)) {
            return null;
        }

        return $this->encryptApiInfo($data);
    }

    /**
     * 暗号化キーのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setEncryptKey($data)
    {
        if (!isset($data)) {
            return null;
        }

        return $this->encryptApiInfo($data);
    }

    /**
     * 初期化キーのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setEncryptIv($data)
    {
        if (!isset($data)) {
            return null;
        }

        return $this->encryptApiInfo($data);
    }

    /**
     * カードのブランド名をカンマ区切りで
     *
     * @return string|null
     */
    protected function _getCardName()
    {
        if (empty($this->get('card_brand'))) {
            return null;
        }

        $brandName = [];
        foreach ($this->get('card_brand') as $brand) {
            $brandName[] = Configure::readOrFail('Master.payment.credit.brand.' . $brand);
        }

        return implode(',', $brandName);
    }

    /**
     * GMO利用時 かつ 本人確認が必要かを判定
     *
     * @return bool
     */
    public function requiresKycGmo()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        return $systemSettingsTable->getData()->usePayment()
            && $this->isPaymentServiceGmo()
            && $this->is3DSecureFlgOn();
    }

    /**
     * SB利用時 かつ 本人確認が必要かを判定
     *
     * @return bool
     */
    public function requiresKycSb()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        return $systemSettingsTable->getData()->usePayment()
            && $this->isPaymentServiceSb()
            && $this->is3DSecureFlgOn();
    }
}
