<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;

/**
 * SystemSetting Entity
 *
 * @property int $id
 * @property int $contract_plan
 * @property int $footer_logo_display_flg
 * @property int $payment_use_flg
 * @property int|null $admin_password_reset_day
 * @property int $smart_lock_use_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class SystemSetting extends AppEntity
{
    public const CONTRACT_PLAN_LITE = 1;
    public const CONTRACT_PLAN_BASIC = 2;
    public const CONTRACT_PLAN_CUSTOMIZE = 3;
    public const CONTRACT_PLAN_EXPAND_BASIC = 4;
    public const CONTRACT_PLAN_EXPAND_CUSTOMIZE = 5;
    public const CONTRACT_PLAN_PACKAGE = 6;
    public const CONTRACT_PLAN_CUSTOMIZE_BASIC = 7;

    public const FOOTER_LOGO_DISPLAY_FLG_OFF = 0;
    public const FOOTER_LOGO_DISPLAY_FLG_ON = 1;

    /**
     * 決済利用フラグ：オン
     */
    public const PAYMENT_USE_FLG_ON = 1;

    /**
     * 決済利用フラグ：オフ
     */
    public const PAYMENT_USE_FLG_OFF = 0;

    /**
     * スマートロック利用フラグ：オン
     */
    public const SMART_LOCK_USE_FLG_ON = 1;

    /**
     * スマートロック利用フラグ：オフ
     */
    public const SMART_LOCK_USE_FLG_OFF = 0;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'contract_plan' => true,
        'footer_logo_display_flg' => true,
        'payment_use_flg' => true,
        'admin_password_reset_day' => true,
        'smart_lock_use_flg' => true,
        'created' => false,
        'modified' => false,
    ];

    /**
     * 決済利用の判定
     *
     * @return bool
     */
    public function usePayment()
    {
        if ((string)$this->get('payment_use_flg') === ((string)static::PAYMENT_USE_FLG_ON)) {
            return true;
        }

        return false;
    }

    /**
     * ビデオ会議API連携が利用できるかどうか
     *
     * @return bool
     */
    public function canCoordinateVideoMeeting()
    {
        $canCoordinateVideoMeetingPlan = Configure::readOrFail('Master.systemSetting.canCoordinateVideoMeeting');
        if (!ArrayUtility::inArray($this->get('contract_plan'), $canCoordinateVideoMeetingPlan)) {
            return false;
        }

        return true;
    }

    /**
     * スマートロック利用かどうか
     *
     * @return bool true: 利用する
     */
    public function useSmartLock()
    {
        return (string)$this->get('smart_lock_use_flg') === ((string)static::SMART_LOCK_USE_FLG_ON);
    }
}
