<?php
declare(strict_types=1);

namespace App\Utility\Recaptcha;

use App\Exception\RecaptchaFailedException;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Log\Log;
use Exception;
use Psr\Log\LogLevel;

class Recaptcha
{
    use InstanceConfigTrait;

    public const API_URL_VERIFY_TOKEN = '/siteverify';

    /**
     * @var array
     */
    protected $_defaultConfig = [
        'errorLog' => null,
        'scheme' => null,
        'host' => null,
        'baseUrl' => null,
        'sslVerify' => true,
        'siteKey' => null,
        'secretKey' => null,
    ];

    /**
     * Constructor.
     *
     * @param array $options オプション
     */
    public function __construct(array $options = [])
    {
        $this->setConfig($options);
    }

    /**
     * トークンを検証
     *
     * @param string $token トークン
     * @return array|null
     */
    public function verifyToken(string $token): ?array
    {
        $client = $this->createApiClient();
        try {
            $response = $client->post($this->getConfig('baseUrl') . static::API_URL_VERIFY_TOKEN, [
                'secret' => $this->getConfig('secretKey'),
                'response' => $token,
            ]);
        } catch (Exception $e) {
            throw new RecaptchaFailedException($e->getMessage(), null, $e);
        }

        if (!$response->isOk()) {
            $this->writeReponseLog($response);

            return null;
        }

        $result = $response->getJson();
        if (!is_array($result) || !isset($result['success']) || !isset($result['action'])) {
            $this->writeReponseLog($response);

            return null;
        }

        if ($result['success'] === false) {
            $this->writeReponseLog($response);
        }

        return $result;
    }

    /**
     * ログへメッセージを記録
     *
     * @param string $message メッセージ
     * @param int|string|null $level レベル
     * @return void
     */
    protected function writeLog(string $message, $level = null): void
    {
        $scope = $this->getConfig('errorLog');
        if ((string)$scope === '') {
            return;
        }

        if (!isset($level)) {
            $level = LogLevel::ERROR;
        }
        Log::write($level, $message . "\n\n", ['scope' => $scope]);
    }

    /**
     * レスポンスをログへ記録
     *
     * @param \Cake\Http\Client\Response $response レスポンス
     * @return void
     */
    protected function writeReponseLog(Response $response): void
    {
        $lines = [];
        foreach ($response->getHeaders() as $key => $values) {
            $lines[] = $key . ': ' . implode(',', $values);
        }
        $lines[] = $response->getStringBody();

        $this->writeLog(implode("\n", $lines));
    }

    /**
     * API送信用のクライアントを生成
     *
     * @return \Cake\Http\Client
     */
    protected function createApiClient(): Client
    {
        return new Client([
            'adapter' => 'Cake\Http\Client\Adapter\Curl',
            'host' => $this->getConfig('host'),
            'scheme' => $this->getConfig('scheme'),
            'ssl_verify_peer' => $this->getConfig('sslVerify'),
            'ssl_verify_peer_name' => $this->getConfig('sslVerify'),
        ]);
    }
}
