<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Command\Setting\Traits\DatabaseTrait;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use SplFileInfo;

/**
 * Class ExecuteSqlCommand
 */
class ExecuteSqlCommand extends Command
{
    use DatabaseTrait;

    /**
     * @var string
     */
    protected $connectionName = 'default';

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);
        $parser->addOption('file', [
            'name' => 'file',
        ]);
        $parser->addOption('transaction', [
            'name' => 'transaction',
            'boolean' => true,
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        if (!$this->checkClientOption()) {
            $this->errorExit('ExecuteSql Error: invalid client.');
        }

        $file = (string)$args->getOption('file');
        if ($file === '') {
            $this->errorExit('ExecuteSql Error: invalid file.');
        }
        $transaction = (bool)$args->getOption('transaction');

        $fileInfo = new SplFileInfo($file);
        if (!$fileInfo->isFile()) {
            $this->errorExit('ExecuteSql Error: file not found.');
        }

        $result = $this->executeSql($fileInfo, $this->connectionName, $transaction);
        if ($result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            $this->errorExit('ExecuteSql Error: sql error.');
        }
        if (!empty($result['output'])) {
            $io->quiet($result['output']);
        }

        $this->outputEndMessage();
    }
}
