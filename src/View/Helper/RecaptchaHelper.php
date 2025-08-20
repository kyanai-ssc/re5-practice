<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\RecaptchaSetting;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\View\Helper;
use Exception;

/**
 * RecaptchaHelper class.
 */
class RecaptchaHelper extends Helper
{
    use LocatorAwareTrait;

    /**
     * 利用設定の判定
     *
     * @return bool
     */
    public function isUseFlgOn(): bool
    {
        /** @var \App\Model\Table\RecaptchaSettingsTable $recaptchaSettingsTable */
        $recaptchaSettingsTable = $this->getTableLocator()->get('RecaptchaSettings');

        $recaptchaSetting = $recaptchaSettingsTable->getData();
        if (!isset($recaptchaSetting)) {
            return false;
        }

        return $recaptchaSetting->isUseFlgOn();
    }

    /**
     * reCATPTCHA設定を取得
     *
     * @return \App\Model\Entity\RecaptchaSetting
     */
    public function getData(): RecaptchaSetting
    {
        /** @var \App\Model\Table\RecaptchaSettingsTable $recaptchaSettingsTable */
        $recaptchaSettingsTable = $this->getTableLocator()->get('RecaptchaSettings');

        $recaptchaSetting = $recaptchaSettingsTable->getData();
        if (!isset($recaptchaSetting)) {
            throw new Exception();
        }

        return $recaptchaSetting;
    }

    /**
     * フォームのクラスを取得
     *
     * @return array
     */
    public function getFormClass(): array
    {
        if (!$this->isUseFlgOn()) {
            return [];
        }

        return ['js_recaptcha_form'];
    }

    /**
     * フォームの属性を取得
     *
     * @param string $actionType アクション種別
     * @return array
     */
    public function getFormAttribute(string $actionType): array
    {
        if (!$this->isUseFlgOn()) {
            return [];
        }

        return [
            'data-recaptcha-site-key' => $this->getData()->get('site_key'),
            'data-recaptcha-action' => $this->getData()->getAction($actionType),
        ];
    }

    /**
     * トークンのタグを取得
     *
     * @return string
     */
    public function getTokenElement(): string
    {
        if (!$this->isUseFlgOn()) {
            return '';
        }

        return $this->getView()->element('User/Common/fieldset/recaptcha');
    }
}
