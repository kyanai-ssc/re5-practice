<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use Cake\Core\Configure;
use Cake\Utility\Security;

/**
 * AppSetting Entity
 *
 * @property int $id
 * @property string $api_secret
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class AppSetting extends AppEntity
{
    public const API_SECRET_CRYPT_SALT_LENGTH = 32;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'admin_id' => true,
        'api_secret' => true,
    ];

    /**
     * API Secretを暗号化
     *
     * @param string $apiSecret API Sercret
     * @return string
     */
    public function setEncryptApiSecret(string $apiSecret)
    {
        $salt = Security::randomString(static::API_SECRET_CRYPT_SALT_LENGTH);
        $crypt = $salt . base64_encode(
            Security::encrypt($apiSecret, $this->generateCryptKey($salt), Configure::readOrFail('Env.appSetting.salt'))
        );
        $this->set('api_secret', $crypt);

        return $crypt;
    }

    /**
     * API Secretを複合した値を取得
     *
     * @param string $crypt 暗号化されたAPI Secret
     * @return string|null
     */
    public function getDecryptApiSecret(string $crypt)
    {
        $salt = substr($crypt, 0, static::API_SECRET_CRYPT_SALT_LENGTH);
        $crypt = base64_decode(substr($crypt, static::API_SECRET_CRYPT_SALT_LENGTH));
        if (!is_string($crypt)) {
            return null;
        }
        $apiSecret = Security::decrypt(
            $crypt,
            $this->generateCryptKey($salt),
            Configure::readOrFail('Env.appSetting.salt')
        );
        if (!is_string($apiSecret)) {
            return null;
        }

        return $apiSecret;
    }

    /**
     * API Secretが一致するかチェック
     *
     * @param string $apiSecret API Secret
     * @return bool
     */
    public function checkApiSecret(string $apiSecret)
    {
        return $apiSecret === $this->getDecryptApiSecret($this->get('api_secret'));
    }

    /**
     * 暗号化キーを生成
     *
     * @param string $salt ソルト
     * @return string
     */
    protected function generateCryptKey($salt)
    {
        return substr(Security::hash(Configure::readOrFail('Client.name'), 'sha1', $salt), 0, 32);
    }

    /**
     * API シークレットに空文字をセット
     *
     * @return void
     */
    public function setEmptyApiSecret()
    {
        $this->set('api_secret', '');
    }
}
