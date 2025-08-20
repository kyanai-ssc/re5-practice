<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class DefaultMailSettingCommand
 */
class DefaultMailSettingCommand extends Command
{
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
            $this->errorExit('DefaultMailSetting Error: invalid client.');
        }

        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->getTableLocator()->get('AutoReplyMails');

        $autoReplyMailsTable->initializeAddress();

        $this->outputEndMessage();
    }
}
