<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Command\Setting\Traits\DatabaseTrait;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use SplFileInfo;

/**
 * Class DatabaseBackupCommand
 */
class DatabaseBackupCommand extends Command
{
    use DatabaseTrait;

    public const FILE_PERMISSION = 0666;
    public const DUMP_OPTIONS = [
        '--serializable-deferrable',
        '--no-acl',
        '--no-owner',
        '--no-tablespaces',
        '--clean',
        '--quote-all-identifiers',
        '--schema=public',
    ];

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

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        if (!$this->checkClientOption()) {
            $this->errorExit('DatabaseBackup Error: invalid client.');
        }

        $file = (string)$args->getOption('file');
        if ($file === '') {
            $this->errorExit('DatabaseBackup Error: invalid file.');
        }

        $dumpFile = new SplFileInfo($file);
        if (file_exists($dumpFile->getPathname())) {
            $this->errorExit('DatabaseBackup Error: exists file.');
        }

        if (Configure::check('Env.pgDumpEnv')) {
            putenv(Configure::readOrFail('Env.pgDumpEnv'));
        }

        $result = $this->executePgDump($dumpFile, $this->connectionName, static::DUMP_OPTIONS, static::FILE_PERMISSION);
        if ($result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }

        $this->outputEndMessage();
    }
}
