<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Command\Traits\CommandTrait;
use App\Utility\FileUtility;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Exception\CakeException;
use SplFileInfo;

/**
 * Class DeleteClientCommand
 */
class DeleteClientCommand extends Command
{
    use CommandTrait;

    public const DIRECTORY_PERMISSION = 0777;
    public const FILE_PERMISSION = 0666;

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->addOption('name', [
            'name' => 'name',
        ]);
        $parser->addOption('no-client-check', [
            'name' => 'no-client-check',
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

        if (!is_writable(ROOT)) {
            $this->errorExit('DeleteClient Error: permission error.');
        }

        $name = (string)$args->getOption('name');
        if (preg_match('/^[\\-\\.0-9a-z_]{1,255}$/', $name) !== 1) {
            $this->errorExit('DeleteClient Error: invlid name.');
        }

        if (!$args->getOption('no-client-check')) {
            $nameConfirm = (string)$io->ask('client name:');
            if ($name !== $nameConfirm) {
                $this->errorExit('DeleteClient Error: client name is incorrect.');
            }
        }

        if (!$this->checkFile($name)) {
            $this->errorExit('DeleteClient Error: not exists client.');
        }

        $backupDirectory = new SplFileInfo(TMP . 'delete' . DS . $name);
        if (file_exists($backupDirectory->getPathname())) {
            $this->errorExit('DeleteClient Error: exists backup directory.');
        }

        $this->moveClientFile($name, $backupDirectory);

        $this->outputEndMessage();
    }

    /**
     * クライアント用ファイル存在チェック
     *
     * @param string $client クライアント名
     * @return bool
     */
    protected function checkFile($client)
    {
        if (!file_exists(ROOT . DS . 'clients' . DS . $client)) {
            return false;
        }

        return true;
    }

    /**
     * クライアント用ファイル移動
     *
     * @param string $client クライアント名
     * @param \SplFileInfo $backupDirectory バックアップディレクトリ
     * @return void
     */
    protected function moveClientFile($client, $backupDirectory)
    {
        $clientDirectory = new SplFileInfo(ROOT . DS . 'clients' . DS . $client);
        $config = (include $clientDirectory->getPathname() . DS . 'config' . DS . 'env.php');
        if (!isset($config['Client']['host'])) {
            throw new CakeException();
        }

        $domain = (string)$config['Client']['host'];
        if (preg_match('/^[\\-\\.0-9a-z]{1,255}$/', $domain) !== 1) {
            throw new CakeException();
        }
        $hostFile = new SplFileInfo(ROOT . DS . 'hosts' . DS . $domain . '.php');

        if (!mkdir($backupDirectory->getPathname())) {
            throw new CakeException();
        }
        if (!chmod($backupDirectory->getPathname(), static::DIRECTORY_PERMISSION)) {
            throw new CakeException();
        }

        FileUtility::copyDirectory(
            $clientDirectory->getPathname(),
            $backupDirectory->getPathname() . DS . $clientDirectory->getBasename(),
            static::DIRECTORY_PERMISSION,
            static::FILE_PERMISSION
        );
        if (!copy($hostFile->getPathname(), $backupDirectory->getPathname() . DS . $hostFile->getFilename())) {
            throw new CakeException();
        }
        if (!chmod($backupDirectory->getPathname() . DS . $hostFile->getFilename(), static::FILE_PERMISSION)) {
            throw new CakeException();
        }

        FileUtility::deleteDirectory($clientDirectory->getPathname());
        if (!unlink($hostFile->getPathname())) {
            throw new CakeException();
        }
    }
}
