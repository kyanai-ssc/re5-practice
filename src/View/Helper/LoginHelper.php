<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * LoginHelper class.
 */
class LoginHelper extends Helper
{
    /**
     * @var bool|null
     */
    protected $backOnLogin = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->backOnLogin = Hash::get($config, 'backOnLogin', false);
    }

    /**
     * ログイン後の遷移先を取得
     *
     * @return string|null
     */
    public function getLoginRedirectBack()
    {
        $redirect = null;
        if ($this->backOnLogin) {
            $redirect = $this->getView()->getRequest()->getRequestTarget();
        }

        return $redirect;
    }
}
