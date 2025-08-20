<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Command\Setting\Traits\MigrateTrait;
use App\Command\Traits\CommandTrait;
use App\Utility\ArrayUtility;
use App\Utility\FileUtility;
use Cake\Cache\Cache;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;
use SplFileInfo;

/**
 * Class ImportMasterDataCommand
 */
class ImportMasterDataCommand extends Command
{
    use CommandTrait;
    use MigrateTrait;

    public const BACKUP_DIRECTORY = 'backup';
    public const DIRECTORY_PERMISSION = 0777;
    public const FILE_PERMISSION = 0666;

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
        $parser->addOption('no-backup', [
            'name' => 'no-backup',
            'boolean' => true,
        ]);
        $parser->addOption('no-database-check', [
            'name' => 'no-database-check',
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
            $this->errorExit('ImportMasterData Error: invalid client.');
        }

        // パーミッションチェック
        if (!is_writable(ROOT)) {
            $this->errorExit('ImportMasterData Error: permission error.');
        }

        // パスのチェック
        $path = (string)$args->getOption('path');
        if ($path === '') {
            $this->errorExit('ImportMasterData Error: invalid path.');
        }
        $path = new SplFileInfo($path);
        if (!$path->isDir()) {
            $this->errorExit('ImportMasterData Error: path not found.');
        }

        // ディレクトリのチェック
        $directory = (string)$args->getOption('directory');
        if ($directory === '') {
            $this->errorExit('ImportMasterData Error: invalid directory.');
        }
        $directory = new SplFileInfo($path->getPathname() . DS . $directory);
        if (!$directory->isDir()) {
            $this->errorExit('ImportMasterData Error: directory not found.');
        }

        // インポートファイルの存在チェック
        $filesDirectory = new SplFileInfo(UPLOAD_FILES);
        if (!is_dir($directory->getPathname() . DS . $filesDirectory->getFilename())) {
            $this->errorExit('ImportMasterData Error: files directory not found.');
        }
        $cssFile = new SplFileInfo(CUSTOM_CSS);
        $cssDirectory = $cssFile->getPathInfo();
        if (!is_dir($directory->getPathname() . DS . $cssDirectory->getFilename())) {
            $this->errorExit('ImportMasterData Error: css directory not found.');
        }
        if (!is_readable($directory->getPathname() . DS . $this->getDumpFileName())) {
            $this->errorExit('ImportMasterData Error: dump file not found.');
        }

        // 実行環境確認
        if (!$args->getOption('no-database-check')) {
            $databaseName = $io->ask('database name:');
            $databasePassword = $io->ask('database password:');
            if (!$this->checkDatabase($databaseName, $databasePassword)) {
                $this->errorExit('ImportMasterData Error: database name or password is incorrect.');
            }
        }

        // バックアップ
        if (!$args->getOption('no-backup')) {
            if (file_exists($directory->getPathname() . DS . static::BACKUP_DIRECTORY)) {
                $this->errorExit('ImportMasterData Error: exists backup directory.');
            }
            $this->backup($directory);
        }

        $this->importFiles($directory);
        $importResult = $this->importData($directory);
        if (!empty($importResult)) {
            $io->verbose($importResult);
        }

        $this->outputEndMessage();
    }

    /**
     * データベースの接続情報をチェック
     *
     * @param string $databaseName DB名
     * @param string $databasePassword DBパスワード
     * @return bool
     */
    protected function checkDatabase($databaseName, $databasePassword)
    {
        $connectionConfig = ConnectionManager::getConfig($this->connectionName);
        if (!isset($connectionConfig)) {
            throw new CakeException();
        }

        if (
            ((string)$databaseName) !== Hash::get($connectionConfig, 'database')
            || ((string)$databasePassword) !== Hash::get($connectionConfig, 'password')
        ) {
            return false;
        }

        return true;
    }

    /**
     * バックアップ
     *
     * @param \SplFileInfo $dataDirectory ディレクトリ
     * @return void
     */
    protected function backup($dataDirectory)
    {
        $backupDirectory = new SplFileInfo($dataDirectory->getPathname() . DS . static::BACKUP_DIRECTORY);
        if (!mkdir($backupDirectory->getPathname())) {
            throw new CakeException();
        }
        if (!chmod($backupDirectory->getPathname(), static::DIRECTORY_PERMISSION)) {
            throw new CakeException();
        }

        $filesDirectory = new SplFileInfo(UPLOAD_FILES);
        if (file_exists($filesDirectory->getPathname())) {
            FileUtility::copyDirectory(
                $filesDirectory->getPathname(),
                $backupDirectory->getPathname() . DS . $filesDirectory->getFileName(),
                static::DIRECTORY_PERMISSION,
                static::FILE_PERMISSION
            );
        }

        $cssFile = new SplFileInfo(CUSTOM_CSS);
        $cssDirectory = $cssFile->getPathInfo();
        if (file_exists($cssDirectory->getPathname())) {
            FileUtility::copyDirectory(
                $cssDirectory->getPathname(),
                $backupDirectory->getPathname() . DS . $cssDirectory->getFileName(),
                static::DIRECTORY_PERMISSION,
                static::FILE_PERMISSION
            );
        }

        $dumpFile = new SplFileInfo($backupDirectory->getPathname() . DS . $this->getDumpFileName());
        $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'setting',
            'database-backup',
            '--quiet',
            '--client=' . Configure::readOrFail('Client.name'),
            '--file=' . $dumpFile->getPathname(),
        ], false);
        if (isset($result['status']) && $result['status'] !== 0) {
            if (!empty($result['output'])) {
                $this->outputErrorMessage($result['output']);
            }
            throw new CakeException();
        }
    }

    /**
     * ファイルをインポート
     *
     * @param \SplFileInfo $dataDirectory ディレクトリ
     * @return void
     */
    protected function importFiles($dataDirectory)
    {
        $filesDirectory = new SplFileInfo(UPLOAD_FILES);
        if (file_exists($filesDirectory->getPathname())) {
            FileUtility::deleteDirectory($filesDirectory->getPathname());
        }

        $cssFile = new SplFileInfo(CUSTOM_CSS);
        $cssDirectory = $cssFile->getPathInfo();
        if (file_exists($cssDirectory->getPathname())) {
            FileUtility::deleteDirectory($cssDirectory->getPathname());
        }

        FileUtility::copyDirectory(
            $dataDirectory->getPathname() . DS . $filesDirectory->getFilename(),
            $filesDirectory->getPathname(),
            static::DIRECTORY_PERMISSION,
            static::FILE_PERMISSION
        );

        FileUtility::copyDirectory(
            $dataDirectory->getPathname() . DS . $cssDirectory->getFilename(),
            $cssDirectory->getPathname(),
            static::DIRECTORY_PERMISSION,
            static::FILE_PERMISSION
        );
    }

    /**
     * データをインポート
     *
     * @param \SplFileInfo $dataDirectory ディレクトリ
     * @return array
     */
    protected function importData($dataDirectory)
    {
        /** @var \App\Model\Table\WordsTable $wordsTable */
        $wordsTable = $this->getTableLocator()->get('Words');

        $siteName = $wordsTable->find('updateSiteName')->first();
        if (!($siteName instanceof EntityInterface)) {
            throw new CakeException();
        }

        // テーブル初期化
        $deleteResult = ConnectionManager::get($this->connectionName)->transactional(function () {
            $tableNames = array_merge($this->getMasterTables(), $this->getTransactionTables());
            foreach (array_reverse($tableNames) as $tableName) {
                $split = preg_split('/_/', $tableName);
                if (!is_array($split)) {
                    throw new CakeException();
                }
                $tableAlias = implode('', array_map(function ($value) {
                    return ucfirst($value);
                }, $split));

                /** @var \App\Model\AppTable $table */
                $table = $this->getTableLocator()->get($tableAlias);

                $table->deleteAll([]);
                if (!ArrayUtility::inArray($tableName, $this->getNoSequenceTables())) {
                    $table->initializeSequence();
                }
            }

            return true;
        });
        if (!$deleteResult) {
            throw new CakeException();
        }

        // データリストア
        $dumpFile = new SplFileInfo($dataDirectory->getPathname() . DS . $this->getDumpFileName());
        $importResult = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'setting',
            'execute-sql',
            '--quiet',
            '--client=' . Configure::readOrFail('Client.name'),
            '--file=' . $dumpFile->getPathname(),
            '--transaction',
        ], false);
        if (isset($importResult['status']) && $importResult['status'] !== 0) {
            if (!empty($importResult['output'])) {
                $this->outputErrorMessage($importResult['output']);
            }
            throw new CakeException();
        }

        // サイト名設定
        $wordsTable->updateSiteName($siteName->get('word'));

        // メール設定
        $mailResult = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'setting',
            'default-mail-setting',
            '--quiet',
            '--client=' . Configure::readOrFail('Client.name'),
        ], false);
        if (isset($mailResult['status']) && $mailResult['status'] !== 0) {
            if (!empty($mailResult['output'])) {
                $this->outputErrorMessage($mailResult['output']);
            }
            throw new CakeException();
        }

        // 統計情報更新
        $analyzeResult = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
            'setting',
            'analyze-table',
            '--quiet',
            '--client=' . Configure::readOrFail('Client.name'),
        ], false);
        if (isset($analyzeResult['status']) && $analyzeResult['status'] !== 0) {
            if (!empty($analyzeResult['output'])) {
                $this->outputErrorMessage($analyzeResult['output']);
            }
            throw new CakeException();
        }

        Cache::clearAll();

        $output = null;
        if (!empty($importResult['output'])) {
            $output = $importResult['output'];
        }

        return $output;
    }
}
