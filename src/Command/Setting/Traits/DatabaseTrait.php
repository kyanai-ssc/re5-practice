<?php
declare(strict_types=1);

namespace App\Command\Setting\Traits;

use App\Command\Traits\CommandTrait;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Hash;
use Throwable;

/**
 * Trait DatabaseTrait
 */
trait DatabaseTrait
{
    use CommandTrait;

    /**
     * SQLを実行
     *
     * @param \SplFileInfo $file SQLファイル
     * @param string $connectionName 接続名
     * @param bool $transaction トランザクション
     * @return array
     */
    protected function executeSql($file, $connectionName, $transaction = false)
    {
        $connectionConfig = ConnectionManager::getConfig($connectionName);
        if (!isset($connectionConfig)) {
            throw new CakeException();
        }

        $sqlOptions = [
            '--host=' . Hash::get($connectionConfig, 'host'),
            '--username=' . Hash::get($connectionConfig, 'username'),
            '--dbname=' . Hash::get($connectionConfig, 'database'),
            '--file=' . $file->getPathname(),
        ];
        if (isset($connectionConfig['port'])) {
            $sqlOptions[] = '--port=' . $connectionConfig['port'];
        }
        if ($transaction) {
            $sqlOptions[] = '--single-transaction';
        }

        if (!putenv('PGPASSWORD=' . Hash::get($connectionConfig, 'password'))) {
            throw new CakeException();
        }
        try {
            $result = $this->execCommand(Configure::readOrFail('Env.path.psql'), $sqlOptions, false);
        } catch (Throwable $e) {
            putenv('PGPASSWORD');
            throw $e;
        }
        putenv('PGPASSWORD');

        return (array)$result;
    }

    /**
     * pg_dumpコマンドを実行
     *
     * @param \SplFileInfo $dumpFile ダンプファイル
     * @param string $connectionName 接続名
     * @param array $dumpOptions ダンプオプション
     * @param int|null $filePermission パーミッション
     * @return array
     */
    protected function executePgDump($dumpFile, $connectionName, $dumpOptions, $filePermission = null)
    {
        $connectionConfig = ConnectionManager::getConfig($connectionName);
        if (!isset($connectionConfig)) {
            throw new CakeException();
        }

        $dumpOptions = array_merge($dumpOptions, [
            '--host=' . Hash::get($connectionConfig, 'host'),
            '--username=' . Hash::get($connectionConfig, 'username'),
            '--dbname=' . Hash::get($connectionConfig, 'database'),
            '--file=' . $dumpFile->getPathname(),
        ]);
        if (isset($connectionConfig['port'])) {
            $dumpOptions[] = '--port=' . $connectionConfig['port'];
        }

        if (!putenv('PGPASSWORD=' . Hash::get($connectionConfig, 'password'))) {
            throw new CakeException();
        }
        try {
            $result = $this->execCommand(Configure::readOrFail('Env.path.pgDump'), $dumpOptions, false);
        } catch (Throwable $e) {
            putenv('PGPASSWORD');
            throw $e;
        }
        putenv('PGPASSWORD');

        if (isset($filePermission)) {
            if (!chmod($dumpFile->getPathname(), $filePermission)) {
                throw new CakeException();
            }
        }

        return (array)$result;
    }
}
