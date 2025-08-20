<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenDate;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Class TempAccessCommand
 *
 *  accesslog's file type: not read gz, Please txt file
 */
class TempAccessCommand extends Command
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
        $parser->addOption('date', [
            'name' => 'date',
            'help' => '集計を実施する日を設定可能です。フォーマット（yyyymmdd）',
            'default' => $this->commonData()->getNowDateTime()->subDays(1)->format('Ymd'),
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        /** @var \App\Model\Table\TempAccessSummariesTable $tempAccessSummariesTable */
        $tempAccessSummariesTable = $this->getTableLocator()->get('TempAccessSummaries');

        $accessDate = $args->getOption('date');
        try {
            $accessDate = FrozenDate::createFromFormat('Ymd', (string)$accessDate);
        } catch (InvalidArgumentException $e) {
            $this->errorExit('Invalid date.');
        }
        if (!($accessDate instanceof DateTimeInterface)) {
            throw new CakeException();
        }

        $tempAccessSummary = $tempAccessSummariesTable->createEntity($accessDate);
        if (isset($tempAccessSummary)) {
            $tempAccessSummariesTable->saveOrFail($tempAccessSummary);
        } else {
            $this->outputMessage('AccessLog can not read');
        }

        $this->outputEndMessage();
    }
}
