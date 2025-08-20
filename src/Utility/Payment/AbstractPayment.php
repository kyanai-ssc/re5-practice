<?php
declare(strict_types=1);

namespace App\Utility\Payment;

use Cake\Core\Exception\CakeException;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\Client\Response;
use Cake\Log\Log;
use Psr\Log\LogLevel;

/**
 * AbstractPayment
 */
abstract class AbstractPayment implements PaymentInterface
{
    use InstanceConfigTrait;

    protected ?string $orderId;
    protected ?string $dataId;
    protected ?array $errors;

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
     * @inheritDoc
     */
    public function getTokenJsUrl(): string
    {
        return $this->getConfig('tokenJsUrl');
    }

    /**
     * @inheritDoc
     */
    public function getOrderId(): string
    {
        if (!isset($this->orderId)) {
            throw new CakeException();
        }

        return $this->orderId;
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @inheritDoc
     */
    public function getDataId(): string
    {
        if (!isset($this->dataId)) {
            throw new CakeException();
        }

        return $this->dataId;
    }

    /**
     * @inheritDoc
     */
    public function setDataId(string $dataId): void
    {
        $this->dataId = $dataId;
    }

    /**
     * @inheritDoc
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): ?array
    {
        if (!isset($this->errors)) {
            return null;
        }

        return $this->errors;
    }

    /**
     * @inheritDoc
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * ログへメッセージを記録
     *
     * @param string $message メッセージ
     * @param int|string|null $level レベル
     * @return void
     */
    public function writeLog(string $message, $level = null): void
    {
        $scope = $this->getConfig('errorLog');
        if ((string)$scope === '') {
            return;
        }

        if (!isset($level)) {
            $level = LogLevel::ERROR;
        }
        if (isset($this->orderId)) {
            $message = 'OrderId: ' . $this->getOrderId() . "\n" . $message;
        }
        if (isset($this->dataId)) {
            $message = 'DataId: ' . $this->getDataId() . "\n" . $message;
        }
        Log::write($level, $message, ['scope' => $scope]);
    }

    /**
     * レスポンスをログへ記録
     *
     * @param \Cake\Http\Client\Response $response レスポンス
     * @return void
     */
    protected function writeReponseLog(Response $response): void
    {
        $lines = [
            'HttpStatus: ' . $response->getStatusCode(),
        ];
        foreach ($response->getHeaders() as $key => $values) {
            $lines[] = $key . ': ' . implode(',', $values);
        }
        $lines[] = $response->getStringBody();

        $this->writeLog(implode("\n", $lines));
    }
}
