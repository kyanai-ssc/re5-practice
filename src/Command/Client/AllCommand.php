<?php
declare(strict_types=1);

namespace App\Command\Client;

use App\Command\Command;
use App\Command\Traits\CommandTrait;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Exception\CakeException;
use FilesystemIterator;
use SplFileInfo;

/**
 * AllCommand class.
 */
class AllCommand extends Command
{
    use CommandTrait;

    protected const DEFAULT_LOG_SCOPE = 'allClientCommand';

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->addOption('command', [
            'name' => 'command',
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        $command = (string)$args->getOption('command');
        if ($command === '') {
            $this->errorExit('ClientAll Error: invlid command.');
        }

        foreach ($this->getClients() as $client) {
            $this->outputMessage('ClientAll Start: ' . $client);

            $result = $this->executeClientCommand($client, $command);
            if (isset($result)) {
                if (!empty($result)) {
                    $io->out($result);
                }
                $this->outputMessage('ClientAll End: ' . $client);
            } else {
                $this->outputMessage('ClientAll Error: ' . $client);
            }
        }

        $this->outputEndMessage();
    }

    /**
     * クライアントを取得
     *
     * @return array
     */
    protected function getClients()
    {
        $iterator = new FilesystemIterator(
            ROOT . DS . 'clients',
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
        );
        $clients = [];
        foreach ($iterator as $fileInfo) {
            if (!($fileInfo instanceof SplFileInfo)) {
                throw new CakeException();
            }
            if ($fileInfo->isDir()) {
                $clients[] = $fileInfo->getFilename();
            }
        }

        return $clients;
    }

    /**
     * 各クライアントのコマンドを実行
     *
     * @param string $client クライアント名
     * @param string $command コマンド
     * @return array|null
     */
    protected function executeClientCommand($client, $command)
    {
        $command = (string)preg_replace('/%CLIENT%/', $client, $command);
        $this->outputMessage('ClientAll Execute: ' . $command);

        $result = $this->execCommand($command, [], false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output']) && isset($this->consoleIo)) {
                $this->consoleIo->err($result['output']);
            }

            return null;
        }

        $output = [];
        if (!empty($result['output'])) {
            $output = $result['output'];
        }

        return $output;
    }
}
