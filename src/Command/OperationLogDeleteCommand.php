<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class OperationLogDelete
 */
class OperationLogDeleteCommand extends Command
{
    public const DELETE_DAY = 120;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->setTranslate();
    }

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

        $nowTime = $this->commonData()->getNowDateTime();

        $adminOperationalLogsTable = $this->getTableLocator()->get('AdminOperationalLogs');
        $deleteTime = $nowTime->subDays(static::DELETE_DAY);
        $adminOperationalLogsTable->deleteAll(['created <=' => $deleteTime]);

        $this->outputEndMessage();
    }
}
