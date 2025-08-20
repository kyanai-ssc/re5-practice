<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Command\Setting\Traits\DatabaseTrait;
use App\Command\Setting\Traits\MigrateTrait;
use App\Utility\FileUtility;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use SplFileInfo;

/**
 * Class ExportMasterDataCommand
 */
class ExportMasterDataCommand extends Command
{
    use DatabaseTrait;
    use MigrateTrait;

    public const DIRECTORY_PERMISSION = 0777;
    public const FILE_PERMISSION = 0666;
    public const DUMP_OPTIONS = [
        '--serializable-deferrable',
        '--no-acl',
        '--no-owner',
        '--no-tablespaces',
        '--data-only',
        '--column-inserts',
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
        $parser->addOption('path', [
            'name' => 'path',
            'default' => $this->getDataDirectory(),
        ]);
        $parser->addOption('directory', [
            'name' => 'directory',
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
            $this->errorExit('ExportMasterData Error: invalid client.');
        }

        // パスのチェック
        $path = (string)$args->getOption('path');
        if ($path === '') {
            $this->errorExit('ExportMasterData Error: invalid path.');
        }
        $path = new SplFileInfo($path);
        if (!$path->isDir()) {
            $this->errorExit('ExportMasterData Error: path not found.');
        }

        // パーミッションチェック
        if (!is_writable($path->getPathname())) {
            $this->errorExit('ExportMasterData Error: permission error.');
        }

        // ディレクトリのチェック
        $directory = (string)$args->getOption('directory');
        if (preg_match('/^[\\-\\.0-9A-Z_a-z]{1,255}$/', $directory) !== 1) {
            $this->errorExit('ExportMasterData Error: invalid directory.');
        }
        $directory = new SplFileInfo($path->getPathname() . DS . $directory);
        if (file_exists($directory->getPathname())) {
            $this->errorExit('ExportMasterData Error: exists directory.');
        }

        if (!mkdir($directory->getPathname())) {
            throw new CakeException();
        }
        if (!chmod($directory->getPathname(), static::DIRECTORY_PERMISSION)) {
            throw new CakeException();
        }

        $this->exportFiles($directory);
        $this->exportData($directory);

        $this->outputEndMessage();
    }

    /**
     * ファイルをエクスポート
     *
     * @param \SplFileInfo $dataDirectory ディレクトリ
     * @return void
     */
    protected function exportFiles($dataDirectory)
    {
        $filesDirectory = new SplFileInfo(UPLOAD_FILES);
        FileUtility::copyDirectory(
            $filesDirectory->getPathname(),
            $dataDirectory->getPathname() . DS . $filesDirectory->getFilename(),
            static::DIRECTORY_PERMISSION,
            static::FILE_PERMISSION
        );

        $cssFile = new SplFileInfo(CUSTOM_CSS);
        FileUtility::copyDirectory(
            $cssFile->getPathInfo()->getPathname(),
            $dataDirectory->getPathname() . DS . $cssFile->getPathInfo()->getFilename(),
            static::DIRECTORY_PERMISSION,
            static::FILE_PERMISSION
        );
    }

    /**
     * データをエクスポート
     *
     * @param \SplFileInfo $dataDirectory ディレクトリ
     * @return void
     */
    protected function exportData($dataDirectory)
    {
        $dumpFile = new SplFileInfo($dataDirectory->getPathname() . DS . $this->getDumpFileName());
        $dumpOptions = static::DUMP_OPTIONS;
        foreach ($this->getMasterTables() as $table) {
            $dumpOptions[] = '--table=' . $table;
        }

        if (Configure::check('Env.pgDumpEnv')) {
            putenv(Configure::readOrFail('Env.pgDumpEnv'));
        }

        $result = $this->executePgDump($dumpFile, $this->connectionName, $dumpOptions, static::FILE_PERMISSION);
        if ($result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }
    }
}
