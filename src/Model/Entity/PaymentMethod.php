<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\Payment\SbPayment;

/**
 * PaymentMethod Entity
 *
 * @property int $id
 * @property int $type
 * @property string $name
 * @property int $display_flg
 * @property int $sort_no
 * @property int $default_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Reservation[] $reservations
 */
class PaymentMethod extends AppEntity
{
    public const DISPLAY_FLG_ON = 1;
    public const DISPLAY_FLG_OFF = 0;

    /**
     * タイプ：カード
     */
    public const TYPE_CARD = 1;

    /**
     * タイプ：現金支払い
     */
    public const TYPE_CASH = 2;

    /**
     * タイプ：銀行振込
     */
    public const TYPE_BANK = 3;

    /**
     * タイプ：PayPay
     */
    public const TYPE_PAYPAY = 4;

    /**
     * タイプ：ApplePay
     */
    public const TYPE_APPLE_PAY = 5;

    /**
     * タイプ：auPAY
     */
    public const TYPE_AU_PAY = 6;

    /**
     * 決済連携が必要なタイプ
     */
    public const REQUIRES_PAYMENT_LINKAGE_TYPE = [
        self::TYPE_CARD,
        self::TYPE_PAYPAY,
        self::TYPE_APPLE_PAY,
        self::TYPE_AU_PAY,
    ];

    /**
     * API型決済のタイプ
     */
    public const API_PAYMENT_TYPE = [
        self::TYPE_CARD,
    ];

    /**
     * リンク型決済のタイプ
     */
    public const LINK_PAYMENT_TYPE = [
        self::TYPE_PAYPAY,
        self::TYPE_APPLE_PAY,
        self::TYPE_AU_PAY,
    ];

    /**
     * GMO決済のタイプ
     */
    public const GMO_PAYMENT_TYPE = [
        self::TYPE_CARD,
        self::TYPE_CASH,
        self::TYPE_BANK,
    ];

    /**
     * SBペイメント決済のタイプ
     */
    public const SB_PAYMENT_TYPE = [
        self::TYPE_CARD,
        self::TYPE_CASH,
        self::TYPE_BANK,
        self::TYPE_PAYPAY,
        self::TYPE_APPLE_PAY,
        self::TYPE_AU_PAY,
    ];

    /**
     * 決済連携時の決済方法
     */
    public const LINKAGE_PAYMENT_METHOD = [
        PaymentSetting::PAYMENT_SERVICE_SB => [
            self::TYPE_CARD => SbPayment::PAY_METHOD_CREDIT,
            self::TYPE_PAYPAY => SbPayment::PAY_METHOD_PAYPAY,
            self::TYPE_APPLE_PAY => SbPayment::PAY_METHOD_APPLE_PAY,
            self::TYPE_AU_PAY => SbPayment::PAY_METHOD_AU_PAY,
        ],
    ];

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'type' => false,
        'name' => true,
        'display_flg' => false,
        'sort_no' => false,
        'default_flg' => false,
        'created' => false,
        'modified' => false,
        'reservations' => false,
    ];

    /**
     * 決済タイプがクレジットカードか
     *
     * @return bool
     */
    public function isCard(): bool
    {
        return (string)$this->get('type') === (string)static::TYPE_CARD;
    }

    /**
     * 設定されている決済代行会社の決済方法に含まれているか判定
     *
     * @return bool
     */
    public function isPaymentServiceMethod()
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getData();

        if (is_null($paymentSetting)) {
            return false;
        } elseif ($paymentSetting->isPaymentServiceGmo()) {
            return in_array((int)$this->get('type'), static::GMO_PAYMENT_TYPE, true);
        } elseif ($paymentSetting->isPaymentServiceSb()) {
            return in_array((int)$this->get('type'), static::SB_PAYMENT_TYPE, true);
        }

        return false;
    }
}
