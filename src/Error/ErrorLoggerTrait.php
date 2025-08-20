<?php
declare(strict_types=1);

namespace App\Error;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Throwable;

trait ErrorLoggerTrait
{
    /**
     * @var \App\Error\ErrorLogger|null
     */
    protected $errorLogger = null;

    /**
     * Get exception logger.
     *
     * @return \App\Error\ErrorLogger
     */
    protected function getErrorLogger()
    {
        if (!isset($this->errorLogger)) {
            $this->errorLogger = new ErrorLogger(Configure::read('Error', []));
        }

        return $this->errorLogger;
    }

    /**
     * 例外処理を行い実行
     *
     * @param callable $callable 処理内容
     * @param bool $sendErrorMail エラーメール送信
     * @return bool
     */
    protected function executeSafe($callable, $sendErrorMail = true)
    {
        try {
            call_user_func($callable);
        } catch (Throwable $e) {
            $logger = clone $this->getErrorLogger();
            $logger->setSendMail($sendErrorMail);
            $logger->logException($e, Router::getRequest(), true);

            return false;
        }

        return true;
    }
}
