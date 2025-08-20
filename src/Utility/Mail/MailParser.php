<?php
declare(strict_types=1);

namespace App\Utility\Mail;

use Cake\Core\Exception\CakeException;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\IMessage;
use ZBateson\MailMimeParser\MailMimeParser;

class MailParser
{
    /**
     * @var \ZBateson\MailMimeParser\IMessage|null
     */
    protected $message;

    /**
     * Constructor.
     *
     * @param string $contents メール内容
     */
    public function __construct(string $contents)
    {
        $message = $this->parseMail($contents);

        $this->message = $message;
    }

    /**
     * 送信先を取得
     *
     * @return string|null
     */
    public function getTo(): ?string
    {
        $to = $this->getMessage()->getHeaderValue(HeaderConsts::TO);
        if (!is_string($to) || $to === '') {
            return null;
        }

        return $to;
    }

    /**
     * メールを解析
     *
     * @param string $contents メール内容
     * @return \ZBateson\MailMimeParser\IMessage
     */
    protected function parseMail($contents): IMessage
    {
        $parser = new MailMimeParser();

        return $parser->parse($contents, false);
    }

    /**
     * メッセージを取得
     *
     * @return \ZBateson\MailMimeParser\IMessage
     */
    protected function getMessage(): IMessage
    {
        if (!isset($this->message)) {
            throw new CakeException();
        }

        return $this->message;
    }
}
