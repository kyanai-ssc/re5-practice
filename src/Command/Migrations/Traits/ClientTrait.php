<?php
declare(strict_types=1);

namespace App\Command\Migrations\Traits;

use Symfony\Component\Console\Input\InputOption;

/**
 * Client trait.
 */
trait ClientTrait
{
    /**
     * クライアントのオプションを追加
     *
     * @return void
     */
    protected function addClientOption()
    {
        $this->addOption('--client', null, InputOption::VALUE_REQUIRED, 'クライントのサブドメインを指定');
    }
}
