<?php
declare(strict_types=1);

namespace App\Utility\Recaptcha;

use Cake\Core\Configure;

class RecaptchaFactory
{
    public const ERROR_LOG_SCOPE = 'recaptcha';

    /**
     * Recaptchaのモジュールを生成
     *
     * @param string $siteKey サイトキー
     * @param string $secretKey シークレットキー
     * @return \App\Utility\Recaptcha\Recaptcha
     */
    public static function createInstance(string $siteKey, string $secretKey): Recaptcha
    {
        return new Recaptcha([
            'errorLog' => static::ERROR_LOG_SCOPE,
            'siteKey' => $siteKey,
            'secretKey' => $secretKey,
        ] + Configure::readOrFail('Env.recaptcha.config'));
    }
}
