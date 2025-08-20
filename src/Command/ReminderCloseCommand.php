<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\FrozenTime;
use InvalidArgumentException;

/**
 * Class ReminderCloseCommand
 */
class ReminderCloseCommand extends Command
{
    public const REMINDER_INTERVAL = 30;
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
        $parser->addOption('dateTime', [
            'name' => 'dateTime',
            'default' => $this->commonData()->getNowDateTime()->format('Y/m/d H:i'),
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');

        $dateTime = (string)$args->getOption('dateTime');
        try {
            $dateTime = FrozenTime::createFromFormat('Y/m/d H:i', $dateTime);
        } catch (InvalidArgumentException $e) {
            $this->errorExit('Invalid date time.');
        }

        $autoReplyMailHistoriesTable->sendReservationCloseReminder(static::REMINDER_INTERVAL, $dateTime);

        usleep(static::SLEEP_MICRO_SECONDS);

        $this->outputEndMessage();
    }
}
