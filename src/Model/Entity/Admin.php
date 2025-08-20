<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\ArrayUtility;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;

/**
 * Admin Entity
 *
 * @property int $id
 * @property int|null $label_id
 * @property int $authority
 * @property string $login_id
 * @property \Cake\I18n\FrozenTime $password_modify_timestamp
 * @property int $password_reset_flg
 * @property int $initial_admin_flg
 * @property int $system_admin_flg
 * @property string $password
 * @property string|null $initial_password
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Label $label
 * @property \App\Model\Entity\AdminLoginHistory $admin_login_history
 * @property \App\Model\Entity\AdminListItem[] $admin_list_items
 * @property \App\Model\Entity\AdminMail[] $admin_mails
 * @property \App\Model\Entity\AdminOperationalLog[] $admin_operational_logs
 * @property \App\Model\Entity\AdminPassResetToken[] $admin_pass_reset_tokens
 * @property \App\Model\Entity\AdminSearchItem[] $admin_search_items
 * @property \App\Model\Entity\AutoReplyMailHistory[] $auto_reply_mail_histories
 */
class Admin extends AppEntity
{
    public const AUTHORITY_MASTER = 1;
    public const AUTHORITY_REGULAR = 2;
    public const AUTHORITY_OPERATOR = 3;

    public const PASSWORD_RESET_FLG_OFF = 0;
    public const PASSWORD_RESET_FLG_ON = 1;

    public const INITIAL_ADMIN_FLG_OFF = 0;
    public const INITIAL_ADMIN_FLG_ON = 1;

    public const SYSTEM_ADMIN_FLG_OFF = 0;
    public const SYSTEM_ADMIN_FLG_ON = 1;

    /**
     * 初回リセット
     */
    public const FIRST_TIME_RESET = 1;

    /**
     * 初回以降のリセット
     */
    public const LIMIT_TIME_RESET = 2;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'label_id' => true,
        'authority' => true,
        'login_id' => true,
        'password_modify_timestamp' => false,
        'password_reset_flg' => false,
        'initial_admin_flg' => false,
        'system_admin_flg' => false,
        'password' => true,
        'initial_password' => false,
        'created' => false,
        'modified' => false,
        'label' => false,
        'admin_login_history' => false,
        'admin_list_items' => false,
        'admin_mails' => true,
        'admin_operational_logs' => false,
        'admin_pass_reset_tokens' => false,
        'admin_search_items' => false,
        'auto_reply_mail_histories' => false,
        'raw_password' => false,
        'admin_authority_id' => true,
    ];

    protected $_virtual = [
        'raw_password',
    ];

    /**
     * マスター管理者の判定
     *
     * @return bool 判定結果
     */
    public function isMasterAdmin()
    {
        if ((string)$this->get('authority') !== ((string)static::AUTHORITY_MASTER)) {
            return false;
        }

        return true;
    }

    /**
     * オペレーター管理者の判定
     *
     * @return bool 判定結果
     */
    public function isOperatorAdmin()
    {
        if ((string)$this->get('authority') !== ((string)static::AUTHORITY_OPERATOR)) {
            return false;
        }

        return true;
    }

    /**
     * 初期管理者の判定
     *
     * @return bool 判定結果
     */
    public function isInitialAdmin()
    {
        if ((string)$this->get('initial_admin_flg') !== ((string)static::INITIAL_ADMIN_FLG_ON)) {
            return false;
        }

        return true;
    }

    /**
     * システム管理者の判定
     *
     * @return bool 判定結果
     */
    public function isSystemAdmin()
    {
        if ((string)$this->get('system_admin_flg') !== ((string)static::SYSTEM_ADMIN_FLG_ON)) {
            return false;
        }

        return true;
    }

    /**
     * 編集可能チェック
     *
     * @return bool 判定結果
     */
    public function canEdit()
    {
        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();

            if ((string)$this->get('id') !== (string)$loginData->get('id')) {
                if ($loginData->isSystemAdmin() || !$loginData->isMasterAdmin() || $this->isSystemAdmin()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * ラベルID編集可能チェック
     *
     * @return bool 判定結果
     */
    public function canEditLabelId()
    {
        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();

            if (!$loginData->isMasterAdmin()) {
                return false;
            }
        }

        return true;
    }

    /**
     * 権限編集可能チェック
     *
     * @return bool 判定結果
     */
    public function canEditAuthority()
    {
        if (!$this->isSystemAdmin()) {
            /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
            $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
            $systemSetting = $systemSettingsTable->getData();
            $canEditAdminAuthorityContractPlan = Configure::readOrFail(
                'Master.systemSetting.canEditAdminAuthorityContractPlan'
            );
            if (!ArrayUtility::inArray($systemSetting->get('contract_plan'), $canEditAdminAuthorityContractPlan)) {
                return false;
            }
        }

        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();

            if (!$loginData->isMasterAdmin()) {
                return false;
            }
        }

        return true;
    }

    /**
     * ログインID編集可能チェック
     *
     * @return bool 判定結果
     */
    public function canEditLoginId()
    {
        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();

            if (!$loginData->isMasterAdmin()) {
                return false;
            }
        }

        return true;
    }

    /**
     * 削除可能チェック
     *
     * @return bool 判定結果
     */
    public function canDelete()
    {
        if ($this->isInitialAdmin()) {
            return false;
        }

        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();

            if ((string)$this->get('id') === (string)$loginData->get('id')) {
                return false;
            }

            if (!$loginData->isSystemAdmin()) {
                if (!$this->canEdit()) {
                    return false;
                }

                /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
                $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
                $systemSetting = $systemSettingsTable->getData();
                $canAddAdminContractPlan = Configure::readOrFail('Master.systemSetting.canAddAdminContractPlan');
                if (!ArrayUtility::inArray($systemSetting->get('contract_plan'), $canAddAdminContractPlan)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * パスワードをハッシュ化
     *
     * @param string $password パスワード
     * @return string|null ハッシュ化文字列
     */
    public function passwordHash(string $password)
    {
        $passwordHasher = new DefaultPasswordHasher();
        $hash = $passwordHasher->hash($password);
        if (!is_string($hash)) {
            return null;
        }

        return $hash;
    }

    /**
     * パスワードをチェック
     *
     * @param string $password パスワード
     * @return bool
     */
    public function passwordCheck(string $password)
    {
        $passwordHasher = new DefaultPasswordHasher();

        return $passwordHasher->check($password, $this->get('password'));
    }

    /**
     * パスワードのミューテーター
     *
     * @param string|null $password パスワード
     * @return string|null ハッシュ化文字列
     */
    protected function _setPassword($password)
    {
        if (((string)$password) === '' || is_null($password)) {
            return null;
        }

        return $this->passwordHash($password);
    }

    /**
     * パスワードリセット判定
     *
     * @return int|bool
     */
    public function isPassReset()
    {
        if ($this->get('password_reset_flg') === static::PASSWORD_RESET_FLG_OFF) {
            return static::FIRST_TIME_RESET;
        }

        $now = $this->commonData()->getNowDateTime();

        /** @var  \App\Model\Table\SystemSettingsTable $systemSetting */
        $systemSetting = $this->getTableLocator()->get('SystemSettings');
        $system = $systemSetting->getData();

        if (
            !empty($system['admin_password_reset_day'])
            && $now->diffInDays($this->get('password_modify_timestamp')) >= $system['admin_password_reset_day']
        ) {
            return static::LIMIT_TIME_RESET;
        }

        return false;
    }
}
