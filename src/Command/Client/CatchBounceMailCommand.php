<?php
declare(strict_types=1);

namespace App\Command\Client;

use App\Command\Command;
use App\Utility\Console\CommandEscaper;
use App\Utility\Mail\MailParser;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;

/**
 * Class CatchBounceMailCommand
 */
class CatchBounceMailCommand extends Command
{
    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        $mail = file_get_contents('php://stdin');
        if ($mail === false) {
            $this->errorExit('Failed to read stdin.');
        }

        $mailParser = new MailParser((string)$mail);
        $matches = null;
        preg_match('/^[^@]+@([^\\.]+)\\..+$/', (string)$mailParser->getTo(), $matches);
        if (isset($matches[1])) {
            $this->executeBounceMailCommand($matches[1], (string)$mail);
        }

        $this->outputEndMessage();
    }

    /**
     * バウンスメールコマンドを実行
     *
     * @param string $client クライアント名
     * @param string $mail メール内容
     * @return void
     */
    protected function executeBounceMailCommand($client, $mail)
    {
        $command = CommandEscaper::escape(Configure::readOrFail('Setting.batch.shell'), [
            'bounce-mail',
            '--quiet',
            '--client=' . $client,
        ]);
        $handle = popen($command, 'w');
        if ($handle === false) {
            throw new CakeException();
        }
        if (fwrite($handle, $mail) === false) {
            throw new CakeException();
        }
        if (pclose($handle) !== 0) {
            throw new CakeException();
        }
    }
}
