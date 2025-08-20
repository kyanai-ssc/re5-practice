<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\SiteSetting;
use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * AuthorityHelper class.
 *
 * @property \App\View\Helper\CommonDataHelper $CommonData
 * @property \App\View\Helper\SettingHelper $Setting
 */
class AuthorityHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    public $helpers = ['Setting', 'CommonData'];

    /**
     * @var mixed
     */
    protected $callback = [];

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->callback = Hash::get($config, 'callback', []);
    }

    /**
     * 権限チェック
     *
     * @param string|bool $element HTML|bool
     * @param string $controller コントローラ
     * @param string $action アクション
     * @param string|null $id ID
     * @param string|null $formType FormType
     * @return bool|string|void
     */
    public function isAuthority($element, $controller, $action, $id = null, $formType = null)
    {
        if (empty($this->callback)) {
            return false;
        }

        if (call_user_func($this->callback, $controller, $action, null, null, $id, $formType)) {
            if (is_bool($element)) {
                return $element;
            } else {
                echo $element;
            }
        }
    }

    /**
     * ログイン設定によるメニューの表示設定
     *
     * @return bool
     */
    public function isMenuDisplayLogin()
    {
        if (
            $this->Setting->getSiteSetting()->get('login_required_flg') === SiteSetting::COMMON_USE_FLG_ON
            && $this->CommonData->existsUserLoginData()
        ) {
            return true;
        } elseif (
            $this->Setting->getSiteSetting()->get('login_required_flg') === SiteSetting::COMMON_USE_FLG_ON
            && !$this->CommonData->existsUserLoginData()
        ) {
            return false;
        } else {
            return true;
        }
    }
}
