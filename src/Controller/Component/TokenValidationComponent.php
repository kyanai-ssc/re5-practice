<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;
use Cake\Utility\Security;

/**
 * トークン検証コンポーネント
 */
class TokenValidationComponent extends Component
{
    /**
     * @var \Cake\Http\Session|null
     */
    protected $storage = null;

    /**
     * @var string|null
     */
    protected $storageKeyPrefix = null;

    /**
     * @var string
     */
    protected $parameterName = null;

    /**
     * @var int
     */
    protected $randomBytes = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->storage = $this->getController()->getRequest()->getSession();
        $this->storageKeyPrefix = Hash::get($config, 'storageKey', 'tokenValidation');
        $this->parameterName = Hash::get($config, 'parameterName', '_tokenValidation');
        $this->randomBytes = Hash::get($config, 'randomBytes', 16);
    }

    /**
     * パラメータ名を取得する
     *
     * @return string パラメータ名
     */
    public function getParameterName()
    {
        return $this->parameterName;
    }

    /**
     * トークンを取得する
     *
     * @param string $key キー
     * @return string トークン
     */
    public function getToken(string $key)
    {
        return $this->getStorage()->read($this->storageKey($key));
    }

    /**
     * トークンを生成する
     *
     * @param string $key キー
     * @return string トークン
     */
    public function generate(string $key)
    {
        $token = hash('sha512', Security::randomBytes($this->randomBytes));
        $this->getStorage()->write($this->storageKey($key), $token);

        $tokenData = [
            'token' => $token,
            'storageKey' => $key,
            'tokenName' => $this->getParameterName(),
        ];

        $this->setTokenParams($tokenData);

        return $token;
    }

    /**
     * トークンを検証し削除する
     *
     * @param string $key キー
     * @param bool $delete 削除有無
     * @return bool 検証結果
     */
    public function validate(string $key, bool $delete = true)
    {
        /** @var string|null $sessionToken */
        $sessionToken = $this->getStorage()->read($this->storageKey($key));
        /** @var string|null $parameterToken */
        $parameterToken = $this->getController()->getRequest()->getData($this->parameterName);

        if ($delete) {
            $this->getStorage()->delete($this->storageKey($key));
        }
        $this->getController()->setRequest($this->getController()->getRequest()->withoutData($this->parameterName));

        if (((string)$parameterToken) === '' || ((string)$sessionToken) === '' || $parameterToken !== $sessionToken) {
            $this->setTokenParams(true, 'tokenError');

            return false;
        }
        $this->setTokenParams(false, 'tokenError');

        return true;
    }

    /**
     * ストレージを取得
     *
     * @return \Cake\Http\Session
     */
    protected function getStorage()
    {
        if (!isset($this->storage)) {
            throw new CakeException();
        }

        return $this->storage;
    }

    /**
     * ストレージの格納キーを生成する
     *
     * @param string $key キー
     * @return string 格納キー
     */
    protected function storageKey($key)
    {
        if (!isset($this->storageKeyPrefix) || $this->storageKeyPrefix === '') {
            return $key;
        }

        return $this->storageKeyPrefix . '.' . $key;
    }

    /**
     * Set token params to request instance.
     *
     * @param mixed $tokenData トークン
     * @param string $name paramのname
     * @return void
     */
    protected function setTokenParams($tokenData, $name = 'token')
    {
        $controller = $this->getController();
        $request = $controller->getRequest();

        $controller->setRequest($request->withParam($name, $tokenData));
    }
}
