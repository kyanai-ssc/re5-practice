<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Error\ErrorLoggerTrait;
use App\Exception\RecaptchaFailedException;
use App\Model\AppEntity;
use App\Utility\Recaptcha\RecaptchaFactory;
use App\Utility\StringUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Routing\Router;
use Throwable;

/**
 * RecaptchaSetting Entity
 *
 * @property int $id
 * @property int $use_flg
 * @property string|null $site_key
 * @property string|null $secret_key
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class RecaptchaSetting extends AppEntity
{
    use ErrorLoggerTrait;

    /*
     * 利用フラグ：利用しない
     */
    public const USE_FLG_OFF = 0;

    /*
     * 利用フラグ：利用する
     */
    public const USE_FLG_ON = 1;

    /*
     * アクション：会員登録
     */
    public const ACTION_USER = 'user';

    /*
     * アクション：予約登録
     */
    public const ACTION_RESERVE = 'reserve';

    /*
     * アクション：お問い合わせ
     */
    public const ACTION_INQUIRY = 'inquiry';

    /*
     * アクション：テスト
     */
    public const ACTION_TEST = 'test';

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'use_flg' => true,
        'site_key' => true,
        'secret_key' => true,
        'created' => false,
        'modified' => false,
    ];

    /**
     * シークレットキーを暗号化
     *
     * @param string $secretKey シークレットキー
     * @return string
     */
    public function encryptSecretKey(string $secretKey)
    {
        return StringUtility::encrypt(
            $secretKey,
            (string)$this->get('site_key'),
            Configure::readOrFail('Env.recaptchaSetting.salt')
        );
    }

    /**
     * シークレットキーを復号化
     *
     * @param string $secretKey シークレットキー
     * @return string
     */
    public function decryptSecretKey(string $secretKey)
    {
        $value = StringUtility::decrypt(
            $secretKey,
            (string)$this->get('site_key'),
            Configure::readOrFail('Env.recaptchaSetting.salt')
        );
        if (!isset($value)) {
            throw new CakeException();
        }

        return $value;
    }

    /**
     * シークレットキーのミューテータ
     *
     * @param string|null $data データ
     * @return string|null
     */
    protected function _setSecretKey($data)
    {
        if (!is_string($data) || $data === '') {
            return null;
        }

        return $this->encryptSecretKey($data);
    }

    /**
     * 利用設定の判定
     *
     * @return bool
     */
    public function isUseFlgOn()
    {
        return (string)$this->get('use_flg') === (string)static::USE_FLG_ON;
    }

    /**
     * アクションを取得
     *
     * @param string $type 種別
     * @return string
     */
    public function getAction(string $type)
    {
        return Configure::readOrFail('Master.recaptcha.action.' . $type);
    }

    /**
     * トークンの検証
     *
     * @param string $actionType アクション種別
     * @param string $token トークン
     * @return bool|null
     */
    public function verifyToken(string $actionType, string $token)
    {
        $recaptcha = RecaptchaFactory::createInstance(
            $this->get('site_key'),
            $this->decryptSecretKey($this->get('secret_key'))
        );

        try {
            $result = $recaptcha->verifyToken($token);
        } catch (RecaptchaFailedException $e) {
            try {
                $this->getErrorLogger()->log($e, Router::getRequest());
            } catch (Throwable $e2) {
                // DO NOTHING
            }
            try {
                $this->getErrorLogger()->sendExceptionMail($e);
            } catch (Throwable $e2) {
                // DO NOTHING
            }

            return null;
        }

        return !is_null($result) && $result['success'] && $result['action'] === $this->getAction($actionType);
    }

    /**
     * テスト可能か判定
     *
     * @return bool
     */
    public function canTest()
    {
        return !empty($this->get('site_key')) && !empty($this->get('secret_key'));
    }
}
