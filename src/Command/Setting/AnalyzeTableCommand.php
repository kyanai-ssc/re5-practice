<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Command\Setting\Traits\MigrateTrait;
use App\Command\Traits\CommandTrait;
use App\Utility\Database\BuilderFactory;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Exception\CakeException;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;

/**
 * Class AnalyzeTableCommand
 */
class AnalyzeTableCommand extends Command
{
    use CommandTrait;
    use MigrateTrait;

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

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        if (!$this->checkClientOption()) {
            $this->errorExit('AnalyzeTable Error: invalid client.');
        }

        $this->analyzeTable();

        $this->outputEndMessage();
    }

    /**
     * 統計情報を更新
     *
     * @return void
     */
    protected function analyzeTable()
    {
        $connection = ConnectionManager::get($this->connectionName);
        if (!($connection instanceof Connection)) {
            throw new CakeException();
        }
        $driverClass = namespaceSplit(get_class($connection->getDriver()));
        $driverExpression = BuilderFactory::getInstance($driverClass[1]);

        $tableNames = array_merge($this->getMasterTables(), $this->getTransactionTables());
        foreach ($tableNames as $tableName) {
            $driverExpression->analyzeTable($tableName, $connection);
        }
    }
}
