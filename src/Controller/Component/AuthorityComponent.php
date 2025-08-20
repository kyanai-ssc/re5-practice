<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Utility\ArrayUtility;
use Cake\Controller\Component;

/**
 * 権限コンポーネント
 */
class AuthorityComponent extends Component
{
    public const AUTHORITY_SEPARATOR = '_';
    public const CONTROLLER_AUTHORITY_ALL = 'All';
    public const ACTION_AUTHORITY_ALL = '_all';

    /**
     * @var array
     */
    protected $authority = [];

    /**
     * @var array
     */
    protected $rejectedAuthority = [];

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
    }

    /**
     * アクセス不可権限をセット
     *
     * @param array $authority アクセス不可
     * @return void
     */
    public function setRejectedAuthority($authority)
    {
        if (!is_array($authority)) {
            $authority = [];
        }

        $this->rejectedAuthority = $authority;
    }

    /**
     * アクセス不可権限を取得
     *
     * @return array
     */
    public function getRejectedAuthority()
    {
        return $this->rejectedAuthority;
    }

    /**
     * 権限を設定
     *
     * @param array|null $authority 権限
     * @return void
     */
    public function setAuthority($authority)
    {
        if (!is_array($authority)) {
            $authority = [];
        }

        $this->authority = $authority;
    }

    /**
     * 権限を取得
     *
     * @return array
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * 権限チェック
     *
     * @param string $controller コントローラ
     * @param string $action アクション
     * @param array $settingAuthority 設定可能な権限
     * @param array $replace 変換する権限
     * @param string|null $id ID
     * @param string|null $formType FormType
     * @return bool
     */
    public function checkAuthority(
        $controller,
        $action,
        $settingAuthority = [],
        $replace = [],
        $id = null,
        $formType = null
    ) {
        $request = $this->getController()->getRequest();
        $authority = $this->getAuthority();
        $rejectAuthority = $this->getRejectedAuthority();

        $action = preg_replace('/(Exec|Conf|Finish)$/', '', $action);

        //アクセス不可かどうかを確認
        $checkStr = $controller . self::AUTHORITY_SEPARATOR . self::ACTION_AUTHORITY_ALL;
        if (ArrayUtility::arraySearch($checkStr, $rejectAuthority) !== false) {
            return false;
        }

        // All_all（全ページ許可）
        $checkStr = self::CONTROLLER_AUTHORITY_ALL . self::AUTHORITY_SEPARATOR . self::ACTION_AUTHORITY_ALL;

        if (ArrayUtility::arraySearch($checkStr, $authority) !== false) {
            return true;
        }

        // [機能]_all（該当機能許可）
        if ($controller === 'FormPatterns' || $controller === 'FormGroups') {
            // 直接遷移時に値をセット
            if ($formType === null) {
                $formType = $request->getParam('formType');
            }
            if ($id === null) {
                $id = $request->getParam('id');
            }

            if ($controller === 'FormPatterns') {
                $checkStr = $controller . self::AUTHORITY_SEPARATOR .
                            $formType . self::AUTHORITY_SEPARATOR . self::ACTION_AUTHORITY_ALL;
            } else {
                $checkStr = $controller . self::AUTHORITY_SEPARATOR .
                            $id . self::AUTHORITY_SEPARATOR . self::ACTION_AUTHORITY_ALL;
            }
        } else {
            $checkStr = $controller . self::AUTHORITY_SEPARATOR . self::ACTION_AUTHORITY_ALL;
        }
        if (ArrayUtility::arraySearch($checkStr, $authority) !== false) {
            return true;
        }

        if ($controller === 'FormPatterns' || $controller === 'FormGroups') {
            if ($controller === 'FormPatterns') {
                $checkStr = $controller . self::AUTHORITY_SEPARATOR . $formType . self::AUTHORITY_SEPARATOR . $action;
            } else {
                $checkStr = $controller . self::AUTHORITY_SEPARATOR . $id . self::AUTHORITY_SEPARATOR . $action;
            }
        } else {
            // [機能]_[処理]（該当処理のみ許可）
            $checkStr = $controller . self::AUTHORITY_SEPARATOR . $action;
        }

        $replaceFlg = false;

        //判定する権限を置き換える
        $replaceAction = $controller . self::AUTHORITY_SEPARATOR . $action;
        if (!empty($replace)) {
            if (isset($replace[$replaceAction])) {
                $checkStr = $replace[$replaceAction];
                $replaceFlg = true;
            }
        }

        if (ArrayUtility::arraySearch($checkStr, $authority) !== false) {
            return true;
        }

        //設定可能な権限で無いかつ権限を置き換えて判定していない場合は画面は許可する
        if (!empty($settingAuthority) && !$replaceFlg) {
            if (array_key_exists($controller, $settingAuthority) === false) {
                return true;
            }
        }

        return false;
    }
}
