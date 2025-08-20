<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class BounceMailCommand
 */
class BounceMailCommand extends Command
{
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

        /** @var \App\Model\Table\BounceMailsTable $bounceMailsTable */
        $bounceMailsTable = $this->getTableLocator()->get('BounceMails');

        $mail = file_get_contents('php://stdin');
        if ($mail === false) {
            $this->errorExit('Failed to read stdin.');
        }

        $bounceMailToken = $bounceMailsTable->parseMail((string)$mail);
        if (isset($bounceMailToken)) {
            $bounceMailsTable->saveBounceMail($bounceMailToken, (string)$mail);
        }

        $this->outputEndMessage();
    }
}
