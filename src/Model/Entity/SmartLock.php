<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Security;

/**
 * SmartLock Entity
 *
 * @property int $id
 * @property int $type
 * @property string|null $client_id
 * @property string|null $client_secret
 * @property int|null $buffer
 * @property string|null $organizations_id
 * @property string|null $token
 * @property \Cake\I18n\FrozenTime|null $expires
 * @property string|null $refresh_token
 * @property int|null $form_item_id
 * @property int $app_use_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\FormItem $form_item
 */
class SmartLock extends AppEntity
{
    /**
     * 種別：リモートロック
     */
    public const TYPE_REMOTE_LOCK = 1;

    /**
     * 種別：アケルン
     */
    public const TYPE_AKERUN = 2;

    /**
     * バッファーの最大値（intの最大値）
     */
    public const BUFFER_MAX = 2147483647;

    /**
     * Client Secret ソルト文字数
     */
    public const CLIENT_SECRET_CRYPT_SALT_LENGTH = 32;

    /**
     * アプリ利用フラグ：利用する
     */
    public const APP_USE_FLG_ON = 1;

    /**
     * アプリ利用フラグ：利用しない
     */
    public const APP_USE_FLG_OFF = 0;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'type' => true,
        'client_id' => true,
        'client_secret' => true,
        'buffer' => true,
        'organizations_id' => true,
        'token' => true,
        'expires' => true,
        'refresh_token' => true,
        'form_item_id' => true,
        'app_use_flg' => true,
        'created' => false,
        'modified' => false,
        'form_item' => false,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var string[]
     */
    protected $_hidden = [
        'token',
    ];

    /**
     * リモートロック利用か
     *
     * @return bool true: 利用する
     */
    public function typeIsRemoteLock(): bool
    {
        return (string)$this->get('type') === ((string)static::TYPE_REMOTE_LOCK);
    }

    /**
     * アケルン利用か
     *
     * @return bool true: 利用する
     */
    public function typeIsAkerun(): bool
    {
        return (string)$this->get('type') === ((string)static::TYPE_AKERUN);
    }

    /**
     * 暗号化キーを生成
     *
     * @param string $salt ソルト
     * @return string 暗号化キー
     */
    protected function generateCryptKey(string $salt): string
    {
        return substr(Security::hash($this->get('client_id'), 'sha1', $salt), 0, 32);
    }

    /**
     * Client Secret を暗号化
     *
     * @param string $clientSecret Client Secret
     * @return string
     */
    public function encryptClientSecret(string $clientSecret): string
    {
        $salt = Security::randomString(static::CLIENT_SECRET_CRYPT_SALT_LENGTH);

        return $salt . base64_encode(Security::encrypt($clientSecret, $this->generateCryptKey($salt)));
    }

    /**
     * Client Secret を復号化
     *
     * @param string $crypt 暗号化文字列
     * @return string
     */
    public function decryptClientSecret(string $crypt): string
    {
        $salt = substr($crypt, 0, static::CLIENT_SECRET_CRYPT_SALT_LENGTH);
        $crypt = base64_decode(substr($crypt, static::CLIENT_SECRET_CRYPT_SALT_LENGTH));
        if (!is_string($crypt)) {
            throw new CakeException();
        }
        $clientSecret = Security::decrypt($crypt, $this->generateCryptKey($salt));
        if (!is_string($clientSecret)) {
            throw new CakeException();
        }

        return $clientSecret;
    }
}
