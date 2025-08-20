<?php
declare(strict_types=1);

namespace App\Utility\OAuth;

use Cake\I18n\FrozenTime;

class Token
{
    /**
     * @var string|null
     */
    protected $accessToken;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $expiration;

    /**
     * @var string|null
     */
    protected $refreshToken;

    /**
     * Constructor.
     *
     * @param string $accessToken アクセストークン
     * @param \Cake\I18n\FrozenTime|null $expiration 有効期限
     * @param string|null $refreshToken リフレッシュトークン
     */
    public function __construct(string $accessToken, ?FrozenTime $expiration = null, ?string $refreshToken = null)
    {
        $this->setAccessToken($accessToken);
        $this->setExpiration($expiration);
        $this->setRefreshToken($refreshToken);
    }

    /**
     * アクセストークンを取得
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * アクセストークンを設定
     *
     * @param string $accessToken アクセストークン
     * @return void
     */
    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * 有効期限を取得
     *
     * @return \Cake\I18n\FrozenTime|null
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * 有効期限を設定
     *
     * @param \Cake\I18n\FrozenTime|null $expiration 有効期限
     * @return void
     */
    public function setExpiration(?FrozenTime $expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * リフレッシュトークンを取得
     *
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * リフレッシュトークンを設定
     *
     * @param string|null $refreshToken リフレッシュトークン
     * @return void
     */
    public function setRefreshToken(?string $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * 有効期限切れの判定
     *
     * @return bool
     */
    public function hasExpired()
    {
        return !is_null($this->getExpiration()) && FrozenTime::now() >= $this->getExpiration();
    }
}
