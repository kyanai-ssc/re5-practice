<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Locale\Message;
use Cake\View\Helper;

/**
 * TokenHelper class.
 *
 * @property \Cake\View\Helper\FormHelper $Form
 */
class TokenHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    public $helpers = ['Form'];

    /**
     * トークンタグを取得する
     *
     * @return string トークンタグ
     */
    public function getTokenTag()
    {
        $token = $this->getView()->getRequest()->getParam('token');

        if (!empty($token)) {
            return $this->Form->hidden($token['tokenName'], ['value' => $token['token']]);
        }

        return '';
    }

    /**
     * トークンエラーメッセージの出力
     *
     * @return string エラーメッセージ
     */
    public function getTokenError()
    {
        $tokenError = $this->getView()->getRequest()->getParam('tokenError');

        if ($tokenError) {
            return $this->Form->formatTemplate('error', ['content' => __(Message::ERROR_ILLEGAL_TRANSITION)]);
        }

        return '';
    }
}
