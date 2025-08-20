<?php
declare(strict_types=1);

namespace App\Command;

use App\Command\Traits\ClientTrait;
use Cake\Console\ConsoleOptionParser;
use Migrations\Command\MigrationsCommand as BaseMigrations;
use Migrations\MigrationsDispatcher;

/**
 * MigrationsCommand class.
 */
class MigrationsCommand extends BaseMigrations
{
    use ClientTrait;

    /**
     * MigrationsCommand constructor.
     */
    public function __construct()
    {
        $command = [
            'Create' => \App\Command\Migrations\Create::class,
            'Dump' => \App\Command\Migrations\Dump::class,
            'MarkMigrated' => \App\Command\Migrations\MarkMigrated::class,
            'Migrate' => \App\Command\Migrations\Migrate::class,
            'Rollback' => \App\Command\Migrations\Rollback::class,
            'Seed' => \App\Command\Migrations\Seed::class,
            'Status' => \App\Command\Migrations\Status::class,
            'CacheBuild' => \App\Command\Migrations\CacheBuild::class,
            'CacheClear' => \App\Command\Migrations\CacheClear::class,
        ];

        MigrationsDispatcher::$phinxCommands = $command;

        parent::__construct();
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);

        return $parser;
    }
}
