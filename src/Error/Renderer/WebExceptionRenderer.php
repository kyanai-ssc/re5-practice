<?php
declare(strict_types=1);

namespace App\Error\Renderer;

use App\Exception\PaymentRollbackException;
use App\Exception\SmartLockException;
use App\Exception\ThreeDSecurePaymentException;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Error\Renderer\WebExceptionRenderer as CakeWebExceptionRenderer;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\HttpException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\ServerRequest;
use Throwable;

/**
 * WebExceptionRenderer class.
 */
class WebExceptionRenderer extends CakeWebExceptionRenderer
{
    /**
     * @var array<string, int>
     * @psalm-var array<class-string<\Throwable>, int>
     */
    protected $additionalExceptionHttpCodes = [
        InvalidPrimaryKeyException::class => 404,
    ];

    /**
     * @inheritDoc
     */
    public function __construct(Throwable $exception, ?ServerRequest $request = null)
    {
        parent::__construct($exception, $request);

        $this->exceptionHttpCodes += $this->additionalExceptionHttpCodes;
    }

    /**
     * @inheritDoc
     */
    protected function _message(Throwable $exception, int $code): string
    {
        $message = __(Message::ERROR_SYSTEM_ERROR);
        if ($exception instanceof PaymentRollbackException) {
            $message = __(Message::ERROR_PAYMENT_ROLLBACK);
        } elseif ($exception instanceof ThreeDSecurePaymentException) {
            $message = __(Message::ERROR_THREE_D_SECURE_PAYMENT);
        } elseif ($exception instanceof SmartLockException) {
            $exceptionMessage = $exception->getMessage();
            if ($exceptionMessage !== '') {
                $message = __($exceptionMessage);
            } else {
                $message = __(Message::ERROR_REGISTRATION_SMART_LOCK);
            }
        } elseif ($code < 500) {
            $exceptionMessage = $exception->getMessage();
            $attributes = [];
            if ($exception instanceof CakeException) {
                $attributes = $exception->getAttributes();
            }

            if ($exception instanceof HttpException && $exceptionMessage !== '') {
                $message = __($exceptionMessage);
            } elseif (isset($attributes['message']) && $attributes['message'] !== '') {
                $message = __($attributes['message']);
            } elseif ($code === 404) {
                $message = __(Message::ERROR_NOT_FOUND);
            } else {
                $message = __(Message::ERROR_USER_ERROR);
            }
        }

        return $message;
    }

    /**
     * @inheritDoc
     */
    protected function _template(Throwable $exception, string $method, int $code): string
    {
        $this->template = 'error500';
        if ($exception instanceof UnauthorizedException && $code === 401) {
            $this->controller->viewBuilder()->setLayout('error403_401');
            $this->template = 'error401';
        } elseif ($exception instanceof ForbiddenException && $code === 403) {
            $this->controller->viewBuilder()->setLayout('error403_401');
            $this->template = 'error403';
        } elseif ($code < 500) {
            $this->template = 'error400';
        }

        if (Configure::read('debug') && $this->template === 'error500') {
            $this->template = 'debug';
        }

        return $this->template;
    }
}
