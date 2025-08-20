<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\SiteSetting;
use App\Utility\CommonData\CommonDataTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\View\Helper;

/**
 * CommonDataHelper class.
 */
class CommonDataHelper extends Helper
{
    use CommonDataTrait;
    use LocatorAwareTrait;

    /**
     * 現在日時を取得
     *
     * @return \Cake\I18n\FrozenTime 現在日時
     */
    public function getNowDateTime()
    {
        return $this->commonData()->getNowDateTime();
    }

    /**
     * 管理者側ログイン情報の有無を判定
     *
     * @return bool 判定結果
     */
    public function existsAdminLoginData()
    {
        return $this->commonData()->existsAdminLoginData();
    }

    /**
     * 管理者側ログイン情報を取得
     *
     * @return \Cake\Datasource\EntityInterface ログイン情報
     */
    public function getAdminLoginData()
    {
        return $this->commonData()->getAdminLoginData();
    }

    /**
     * 管理者側ラベルIDを取得
     *
     * @return int|null ラベルID
     */
    public function getAdminLabelId()
    {
        return $this->commonData()->getAdminLoginLabel();
    }

    /**
     * 利用者側ログイン情報の有無を判定
     *
     * @return bool 判定結果
     */
    public function existsUserLoginData()
    {
        return $this->commonData()->existsUserLoginData();
    }

    /**
     * 利用者側ログイン情報を取得
     *
     * @return \Cake\Datasource\EntityInterface ログイン情報
     */
    public function getUserLoginData()
    {
        return $this->commonData()->getUserLoginData();
    }

    /**
     * ラベルIDを取得
     *
     * @return int|null ラベルID
     */
    public function getUserLabelId()
    {
        return $this->commonData()->getUserLabelId();
    }

    /**
     * 続けて予約が利用中かどうか
     *
     * @return bool
     */
    public function duringContinueReservation()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
        $siteSetting = $siteSettingsTable->getData();

        //管理側の場合は基本設定をみない
        if (
            $this->existsAdminLoginData()
            && $this->getView()->getRequest()->getSession()->read('reservations.add.continuousParameter')
        ) {
            return true;
        }

        if (
            $siteSetting->get('reservation_continuous_flg') === SiteSetting::COMMON_USE_FLG_ON
            && $this->getView()->getRequest()->getSession()->read('reservations.add.continuousParameter')
        ) {
            return true;
        }

        return false;
    }

    /**
     * メール送信時のデフォルトFromアドレスを取得
     *
     * @param bool $domainOnly ドメイン（@付き）のみ返すかどうか
     * @return string
     */
    public function getDefaultFromAddress(bool $domainOnly = false): string
    {
        return $this->commonData()->getDefaultFromAddress($domainOnly);
    }

    /**
     * メール送信時のデフォルトFromアドレスが環境別設定ファイルに設定されているか判定
     *
     * @return bool
     */
    public function existsDefaultFromAddressOnEnv(): bool
    {
        return $this->commonData()->existsDefaultFromAddressOnEnv();
    }

    /**
     * 決済期限切れリンク表示対象かどうか判定
     *
     * @return bool
     */
    public function isDisplaySearchPaymentExpiredLink()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $paymentSetting = $paymentSettingsTable->getData();
        if (
            $systemSettingsTable->getData()->usePayment()
            && isset($paymentSetting)
            && $paymentSetting->isPaymentServiceSb()
            && $this->existsAdminLoginData()
            && $reservationsTable->existsPaymentExpiredReservation()
        ) {
            return true;
        }

        return false;
    }

    /**
     * 未連携リンク表示対象かどうか判定
     *
     * @return bool
     */
    public function isDisplaySearchReservationUnlinkedSmartLockLink()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        if (
            $systemSettingsTable->getData()->useSmartLock()
            && $this->existsAdminLoginData()
            && $reservationsTable->existsSmartLockUnlinkedReservation()
        ) {
            return true;
        }

        return false;
    }
}
