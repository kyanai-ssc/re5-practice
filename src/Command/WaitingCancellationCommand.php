<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class WaitingCancellationCommand
 */
class WaitingCancellationCommand extends Command
{
    public const SLEEP_MICRO_SECONDS = 250000;

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

        /** @var \App\Model\Table\WaitingCancellationsTable $waitingCancellationsTable */
        $waitingCancellationsTable = $this->getTableLocator()->get('WaitingCancellations');

        $waitingCancellationsTable->notifyCancellation();
        $waitingCancellationsTable->deleteOldData();

        usleep(static::SLEEP_MICRO_SECONDS);

        $this->outputEndMessage();
    }
}
