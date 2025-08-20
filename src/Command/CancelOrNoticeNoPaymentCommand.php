<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;
use InvalidArgumentException;

/**
 * CancelOrNoticeNoPaymentCommand
 */
class CancelOrNoticeNoPaymentCommand extends Command
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

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $dateTime = (string)$args->getOption('dateTime');
        try {
            $dateTime = FrozenTime::createFromFormat('Y/m/d H:i', $dateTime);
        } catch (InvalidArgumentException $e) {
            $this->errorExit('Invalid date time.');
        }
        if (!($dateTime instanceof FrozenTime)) {
            throw new CakeException();
        }

        $reservationsTable->cancelNoPayment($dateTime);
        $reservationsTable->noticeNoPayment($dateTime);
        $reservationsTable->cancelNotFinishThreeDSecure($dateTime);
        $reservationsTable->cancelNotFinishThreeDSecureOnSb($dateTime);

        usleep(static::SLEEP_MICRO_SECONDS);

        $this->outputEndMessage();
    }
}
