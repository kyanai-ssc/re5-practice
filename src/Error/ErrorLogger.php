<?php
declare(strict_types=1);

namespace App\Error;

use App\Http\ServerRequest;
use App\Mailer\ErrorMailer;
use Cake\Error\ErrorLogger as CakeErrorLogger;
use Cake\Error\PhpError;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * ErrorLogger class.
 */
class ErrorLogger extends CakeErrorLogger
{
    /**
     * @var bool
     */
    protected $sendMail = true;

    /**
     * @inheritDoc
     */
    public function logError(
        PhpError $error,
        ?ServerRequestInterface $request = null,
        bool $includeTrace = false
    ): void {
        try {
            parent::logError($error, $request, $includeTrace);
        } catch (Throwable $e) {
            // DO NOTHING
        }
        $this->sendErrorMail($error, $request, $includeTrace);
    }

    /**
     * @inheritDoc
     */
    public function logException(
        Throwable $exception,
        ?ServerRequestInterface $request = null,
        bool $includeTrace = false
    ): void {
        try {
            parent::logException($exception, $request, $includeTrace);
        } catch (Throwable $e) {
            // DO NOTHING
        }
        $this->sendExceptionMail($exception, $request, $includeTrace);
    }

    /**
     * @inheritDoc
     */
    public function getRequestContext(ServerRequestInterface $request): string
    {
        $message = "\nRequest URL: " . $request->getRequestTarget();

        $referer = $request->getHeaderLine('Referer');
        if ($referer) {
            $message .= "\nReferer URL: " . $referer;
        }

        /** @var \Cake\Http\ServerRequest $request */
        $serverRequest = new ServerRequest([
            'session' => $request->getSession(),
        ]);
        $clientIp = $serverRequest->clientIp();
        if ($clientIp !== '' && $clientIp !== '::1') {
            $message .= "\nClient IP: " . $clientIp;
        }

        return $message;
    }

    /**
     * メール送信の有無を設定
     *
     * @param bool $sendMail メール送信の有無
     * @return void
     */
    public function setSendMail(bool $sendMail)
    {
        $this->sendMail = $sendMail;
    }

    /**
     * エラーメールを送信
     *
     * @param \Cake\Error\PhpError $error The error to log
     * @param ?\Psr\Http\Message\ServerRequestInterface $request The request if in an HTTP context.
     * @param bool $includeTrace Should the log message include a stacktrace
     * @return void
     */
    public function sendErrorMail(
        PhpError $error,
        ?ServerRequestInterface $request = null,
        bool $includeTrace = true
    ): void {
        try {
            $mailer = new ErrorMailer();
            if ($this->sendMail && $mailer->shouldSendErrorMail($error)) {
                $message = $error->getMessage();
                if ($request) {
                    $message .= $this->getRequestContext($request);
                }
                if ($includeTrace) {
                    $message .= "\nTrace:\n" . $error->getTraceAsString() . "\n";
                }
                $mailer->send('notifyError', [$message]);
            }
        } catch (Throwable $e) {
            // DO NOTHING
        }
    }

    /**
     * 例外メールを送信
     *
     * @param \Throwable $exception The exception to log a message for.
     * @param \Psr\Http\Message\ServerRequestInterface|null $request The current request if available.
     * @param bool $includeTrace Whether or not a stack trace should be logged.
     * @return void
     */
    public function sendExceptionMail(
        Throwable $exception,
        ?ServerRequestInterface $request = null,
        bool $includeTrace = true
    ): void {
        try {
            $mailer = new ErrorMailer();
            if ($this->sendMail && $mailer->shouldSendErrorMail($exception)) {
                $message = $this->getMessage($exception, false, $includeTrace);
                if ($request !== null) {
                    $message .= $this->getRequestContext($request);
                }
                $mailer->send('notifyError', [$message, $exception]);
            }
        } catch (Throwable $e) {
            // DO NOTHING
        }
    }
}
