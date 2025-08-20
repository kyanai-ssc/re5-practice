<?php
declare(strict_types=1);

namespace App\Command\Traits;

use Cake\Core\Configure;

/**
 * Client trait.
 */
trait ClientTrait
{
    /**
     * クライアントのオプションを追加
     *
     * @param \Cake\Console\ConsoleOptionParser $parser パーサ
     * @return void
     */
    protected function addClientOption($parser)
    {
        $parser->addOption('client', [
            'help' => 'クライントのサブドメインを指定',
        ]);
    }

    /**
     * クライアントのオプションをチェック
     *
     * @return bool
     */
    protected function checkClientOption()
    {
        $client = Configure::read('Client.name');
        if (((string)$client) === '') {
            return false;
        }
        if (!file_exists(ROOT . DS . 'clients' . DS . $client)) {
            return false;
        }

        return true;
    }
}
