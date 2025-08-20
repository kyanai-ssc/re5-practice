<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Locale\Message;
use Cake\Controller\Component;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * reCATPTCHAコンポーネント
 */
class RecaptchaComponent extends Component
{
    use LocatorAwareTrait;

    /**
     * reCATPTCHAの検証
     *
     * @param string $action アクション
     * @param bool $successOnException 通信時の例外発生時に成功とするか
     * @return void
     */
    public function verify(string $action, bool $successOnException = true): void
    {
        /** @var \App\Model\Table\RecaptchaSettingsTable $recaptchaSettingsTable */
        $recaptchaSettingsTable = $this->getTableLocator()->get('RecaptchaSettings');

        $recaptchaSetting = $recaptchaSettingsTable->getData();
        if (!isset($recaptchaSetting) || !$recaptchaSetting->isUseFlgOn()) {
            return;
        }

        $token = $this->getController()->getRequest()->getData('recaptcha_token');
        if (!is_string($token)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $result = $recaptchaSetting->verifyToken($action, $token);
        if ($result === false || is_null($result) && !$successOnException) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }
    }
}
