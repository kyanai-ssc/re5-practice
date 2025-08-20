<?php
declare(strict_types=1);

namespace App\Exception;

use App\Locale\Message;
use Cake\Http\Exception\HttpException;

class PageNotFoundException extends HttpException
{
    /**
     * @inheritDoc
     */
    protected $_defaultCode = 404;

    /**
     * Constructor
     *
     * @param string|array|null $message If no message is given 'Not Found' will be the message
     * @param int $code Status code, defaults to 404
     * @param \Exception|null $previous The previous exception.
     */
    public function __construct($message = null, $code = null, $previous = null)
    {
        if (empty($message)) {
            $message = [
                'message' => __(Message::ERROR_PAGE_NOT_FOUND),
            ];
        }

        parent::__construct($message, $code, $previous);
    }
}
