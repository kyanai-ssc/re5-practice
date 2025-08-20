<?php
declare(strict_types=1);

namespace App\Mailer\Transport;

use Cake\Mailer\Message;
use Cake\Mailer\Transport\MailTransport as CakeMailTransport;

/**
 * MailTransport class.
 */
class MailTransport extends CakeMailTransport
{
    /**
     * @inheritDoc
     */
    public function send(Message $message): array
    {
        $returnPath = $message->getReturnPath();
        $additionalParameters = $this->getConfig('additionalParameters');
        if (!empty($returnPath) && (string)$additionalParameters === '') {
            $message->setReturnPath([]);
            $this->setConfig('additionalParameters', '-f ' . reset($returnPath));
        }

        $result = parent::send($message);

        $message->setReturnPath($returnPath);
        $this->setConfig('additionalParameters', $additionalParameters);

        return $result;
    }
}
